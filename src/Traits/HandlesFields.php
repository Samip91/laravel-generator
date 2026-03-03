<?php

namespace Brikshya\LaravelGenerator\Traits;

use Brikshya\LaravelGenerator\Services\ModelAnalyzer;
use Illuminate\Support\Str;

trait HandlesFields
{
    protected ModelAnalyzer $modelAnalyzer;
    
    /**
     * Get field definitions with auto-detection fallback.
     */
    protected function getFieldDefinitions(string $modelName, ?string $fieldsOption = null): array
    {
        // Priority 1: Explicit --fields option
        if (!empty($fieldsOption)) {
            return $this->parseFieldsString($fieldsOption);
        }
        
        // Priority 2: Auto-detect from existing model
        $detectedFields = $this->autoDetectFields($modelName);
        if (!empty($detectedFields)) {
            return $detectedFields;
        }
        
        // Priority 3: Interactive prompts (if in interactive mode)
        if ($this->isInteractive()) {
            return $this->promptForFields();
        }
        
        // Priority 4: Error - no field information available
        throw new \InvalidArgumentException(
            "No field information available. Please provide --fields option or ensure the {$modelName} model exists."
        );
    }

    /**
     * Auto-detect fields from existing model.
     */
    protected function autoDetectFields(string $modelName): array
    {
        if (!isset($this->modelAnalyzer)) {
            $this->modelAnalyzer = new ModelAnalyzer(app('files'));
        }
        
        try {
            $analyzedFields = $this->modelAnalyzer->analyzeModel($modelName);
            
            if (empty($analyzedFields)) {
                return [];
            }
            
            // Convert analyzed fields to generator format
            return $this->convertAnalyzedFields($analyzedFields);
        } catch (\Exception $e) {
            // Model doesn't exist or can't be analyzed
            return [];
        }
    }

    /**
     * Convert analyzed fields to generator format.
     */
    protected function convertAnalyzedFields(array $analyzedFields): array
    {
        $fields = [];
        
        foreach ($analyzedFields as $field) {
            $generatorField = [
                'name' => $field['name'],
                'type' => $field['type'],
                'nullable' => $field['nullable'] ?? true,
                'unique' => false, // Default for auto-detected fields
                'index' => false, // Default for auto-detected fields
            ];
            
            // Add type-specific options
            switch ($field['type']) {
                case 'enum':
                    $generatorField['options'] = $this->detectEnumOptions($field['name']);
                    break;
                    
                case 'string':
                    if (isset($field['length'])) {
                        $generatorField['length'] = $field['length'];
                    }
                    break;
                    
                case 'decimal':
                    $generatorField['precision'] = 8;
                    $generatorField['scale'] = 2;
                    break;
            }
            
            $fields[] = $generatorField;
        }
        
        return $fields;
    }

    /**
     * Parse fields string into field definitions.
     */
    protected function parseFieldsString(string $fieldsString): array
    {
        $fields = [];
        $fieldDefinitions = explode(',', $fieldsString);
        
        foreach ($fieldDefinitions as $fieldDef) {
            $fieldDef = trim($fieldDef);
            if (empty($fieldDef)) continue;
            
            $parts = explode(':', $fieldDef);
            $name = trim($parts[0]);
            $type = trim($parts[1] ?? 'string');
            
            $field = [
                'name' => $name,
                'type' => $type,
                'nullable' => true, // Default
                'unique' => false, // Default
                'index' => false, // Default
            ];
            
            // Parse type-specific options
            if (isset($parts[2])) {
                switch ($type) {
                    case 'enum':
                        $field['options'] = explode(',', $parts[2]);
                        break;
                        
                    case 'string':
                        if (is_numeric($parts[2])) {
                            $field['length'] = (int) $parts[2];
                        }
                        break;
                        
                    case 'decimal':
                        $precisionScale = explode(',', $parts[2]);
                        $field['precision'] = (int) ($precisionScale[0] ?? 8);
                        $field['scale'] = (int) ($precisionScale[1] ?? 2);
                        break;
                }
            }
            
            // Parse nullable modifier
            if (Str::endsWith($fieldDef, '?')) {
                $field['nullable'] = true;
                $field['name'] = rtrim($field['name'], '?');
            } elseif (Str::endsWith($fieldDef, '!')) {
                $field['nullable'] = false;
                $field['name'] = rtrim($field['name'], '!');
            }
            
            $fields[] = $field;
        }
        
        return $fields;
    }

    /**
     * Detect enum options from existing enum class.
     */
    protected function detectEnumOptions(string $fieldName): array
    {
        // Try to find enum class based on field name
        $enumClassName = $this->guessEnumClassName($fieldName);
        
        if (class_exists($enumClassName) && enum_exists($enumClassName)) {
            return $this->extractEnumCases($enumClassName);
        }
        
        // Default enum options for common field names
        $defaultOptions = [
            'status' => ['draft', 'published', 'archived'],
            'type' => ['basic', 'premium', 'enterprise'],
            'priority' => ['low', 'medium', 'high'],
            'level' => ['beginner', 'intermediate', 'advanced'],
        ];
        
        return $defaultOptions[$fieldName] ?? ['active', 'inactive'];
    }

    /**
     * Guess enum class name from field name.
     */
    protected function guessEnumClassName(string $fieldName): string
    {
        $className = Str::studly($fieldName) . 'Enum';
        
        // Check common enum locations
        $possibleClasses = [
            "App\\Enums\\{$className}",
            "App\\Enum\\{$className}",
            "App\\Models\\Enums\\{$className}",
        ];
        
        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
        
        return "App\\Enums\\{$className}";
    }

    /**
     * Extract cases from enum class.
     */
    protected function extractEnumCases(string $enumClass): array
    {
        try {
            $reflection = new \ReflectionEnum($enumClass);
            $cases = $reflection->getCases();
            
            return array_map(function ($case) {
                return $case->getValue();
            }, $cases);
        } catch (\Exception $e) {
            return ['active', 'inactive'];
        }
    }

    /**
     * Check if running in interactive mode.
     */
    protected function isInteractive(): bool
    {
        return $this->input->isInteractive() && !$this->option('no-interaction');
    }

    /**
     * Prompt for field definitions interactively.
     */
    protected function promptForFields(): array
    {
        $this->info('No field definitions found. Let\'s define them interactively.');
        
        $fields = [];
        
        do {
            $name = $this->ask('Field name (or press Enter to finish)');
            
            if (empty($name)) {
                break;
            }
            
            $type = $this->choice('Field type', [
                'string', 'text', 'integer', 'decimal', 'boolean', 
                'date', 'datetime', 'time', 'json', 'enum', 'foreign'
            ], 'string');
            
            $field = [
                'name' => $name,
                'type' => $type,
                'nullable' => $this->confirm('Is this field nullable?', true),
                'unique' => $this->confirm('Should this field be unique?', false),
                'index' => $this->confirm('Should this field be indexed?', false),
            ];
            
            // Type-specific prompts
            switch ($type) {
                case 'enum':
                    $options = $this->ask('Enum options (comma-separated)', 'active,inactive');
                    $field['options'] = array_map('trim', explode(',', $options));
                    break;
                    
                case 'string':
                    $length = $this->ask('String length (optional)');
                    if (is_numeric($length)) {
                        $field['length'] = (int) $length;
                    }
                    break;
                    
                case 'decimal':
                    $precision = $this->ask('Decimal precision', '8');
                    $scale = $this->ask('Decimal scale', '2');
                    $field['precision'] = (int) $precision;
                    $field['scale'] = (int) $scale;
                    break;
            }
            
            $fields[] = $field;
            
        } while (true);
        
        return $fields;
    }

    /**
     * Display detected fields for confirmation.
     */
    protected function displayDetectedFields(array $fields, string $source = 'model'): void
    {
        if (empty($fields)) {
            return;
        }
        
        $this->info("Auto-detected fields from {$source}:");
        $this->table(
            ['Name', 'Type', 'Nullable', 'Options'],
            collect($fields)->map(function ($field) {
                return [
                    $field['name'],
                    $field['type'],
                    $field['nullable'] ? 'Yes' : 'No',
                    isset($field['options']) ? implode(', ', $field['options']) : '-'
                ];
            })->toArray()
        );
    }

    /**
     * Validate field definitions.
     */
    protected function validateFields(array $fields): void
    {
        foreach ($fields as $field) {
            if (empty($field['name'])) {
                throw new \InvalidArgumentException('Field name cannot be empty.');
            }
            
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $field['name'])) {
                throw new \InvalidArgumentException(
                    "Invalid field name '{$field['name']}'. Use snake_case format."
                );
            }
            
            $validTypes = [
                'string', 'text', 'integer', 'decimal', 'boolean',
                'date', 'datetime', 'time', 'json', 'enum', 'foreign'
            ];
            
            if (!in_array($field['type'], $validTypes)) {
                throw new \InvalidArgumentException(
                    "Invalid field type '{$field['type']}'. Valid types: " . implode(', ', $validTypes)
                );
            }
            
            if ($field['type'] === 'enum' && empty($field['options'])) {
                throw new \InvalidArgumentException(
                    "Enum field '{$field['name']}' must have options defined."
                );
            }
        }
    }

    /**
     * Format fields for display in command output.
     */
    protected function formatFieldsForDisplay(array $fields): string
    {
        return collect($fields)->map(function ($field) {
            $formatted = $field['name'] . ':' . $field['type'];
            
            if (isset($field['options'])) {
                $formatted .= ':' . implode(',', $field['options']);
            } elseif (isset($field['length'])) {
                $formatted .= ':' . $field['length'];
            } elseif (isset($field['precision']) && isset($field['scale'])) {
                $formatted .= ':' . $field['precision'] . ',' . $field['scale'];
            }
            
            if (!$field['nullable']) {
                $formatted .= '!';
            }
            
            return $formatted;
        })->implode(', ');
    }
}
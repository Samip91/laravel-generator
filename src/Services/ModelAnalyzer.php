<?php

namespace Brikshya\LaravelGenerator\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use Exception;

class ModelAnalyzer
{
    protected Filesystem $files;
    
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Analyze a model and extract field definitions.
     */
    public function analyzeModel(string $modelName): array
    {
        $fields = [];
        
        try {
            // Try to analyze from existing model class
            $fields = $this->analyzeModelClass($modelName);
        } catch (Exception $e) {
            // Fallback to file analysis if class can't be instantiated
            $fields = $this->analyzeModelFile($modelName);
        }
        
        // Enhance with migration data if available
        $migrationFields = $this->analyzeMigrationFile($modelName);
        $fields = $this->mergeFieldData($fields, $migrationFields);
        
        return $fields;
    }

    /**
     * Analyze model using reflection (if class exists).
     */
    protected function analyzeModelClass(string $modelName): array
    {
        $modelClass = "App\\Models\\{$modelName}";
        
        if (!class_exists($modelClass)) {
            throw new Exception("Model class {$modelClass} not found");
        }
        
        $reflection = new ReflectionClass($modelClass);
        $instance = $reflection->newInstanceWithoutConstructor();
        
        $fields = [];
        
        // Get fillable fields
        $fillable = $this->getPropertyValue($instance, 'fillable') ?? [];
        
        // Get casts for field types
        $casts = $this->getPropertyValue($instance, 'casts') ?? [];
        
        // Get dates fields
        $dates = $this->getPropertyValue($instance, 'dates') ?? [];
        
        // Process fillable fields
        foreach ($fillable as $field) {
            $type = $this->determineFieldType($field, $casts, $dates);
            $fields[] = [
                'name' => $field,
                'type' => $type,
                'nullable' => $this->isFieldNullable($field),
                'source' => 'model_fillable'
            ];
        }
        
        return $fields;
    }

    /**
     * Analyze model by parsing the file content.
     */
    protected function analyzeModelFile(string $modelName): array
    {
        $modelPath = app_path("Models/{$modelName}.php");
        
        if (!$this->files->exists($modelPath)) {
            return [];
        }
        
        $content = $this->files->get($modelPath);
        $fields = [];
        
        // Extract fillable array
        if (preg_match('/protected\s+\$fillable\s*=\s*\[(.*?)\]/s', $content, $matches)) {
            $fillableContent = $matches[1];
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $fillableContent, $fillableMatches);
            $fillable = $fillableMatches[1];
            
            foreach ($fillable as $field) {
                $fields[] = [
                    'name' => $field,
                    'type' => 'string', // Default type
                    'nullable' => true,
                    'source' => 'file_fillable'
                ];
            }
        }
        
        // Extract casts array for better type detection
        if (preg_match('/protected\s+\$casts\s*=\s*\[(.*?)\]/s', $content, $matches)) {
            $castsContent = $matches[1];
            preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]?([^\'"]+)[\'"]?/', $castsContent, $castMatches);
            
            if (isset($castMatches[1], $castMatches[2])) {
                foreach ($castMatches[1] as $index => $fieldName) {
                    $castType = $castMatches[2][$index];
                    
                    // Update existing field type or add new field
                    $fieldIndex = collect($fields)->search(function ($field) use ($fieldName) {
                        return $field['name'] === $fieldName;
                    });
                    
                    if ($fieldIndex !== false) {
                        $fields[$fieldIndex]['type'] = $this->mapCastToFieldType($castType);
                    }
                }
            }
        }
        
        return $fields;
    }

    /**
     * Analyze migration file for additional field information.
     */
    protected function analyzeMigrationFile(string $modelName): array
    {
        $tableName = Str::snake(Str::pluralStudly($modelName));
        $migrationPattern = database_path("migrations/*_create_{$tableName}_table.php");
        $migrationFiles = glob($migrationPattern);
        
        if (empty($migrationFiles)) {
            return [];
        }
        
        $migrationPath = $migrationFiles[0];
        $content = $this->files->get($migrationPath);
        $fields = [];
        
        // Extract table schema definition
        preg_match_all('/\$table->(\w+)\([\'"]([^\'"]+)[\'"](?:,\s*(\d+))?\)(?:->(\w+)\(\))?/m', $content, $matches);
        
        if (isset($matches[1], $matches[2])) {
            foreach ($matches[1] as $index => $type) {
                $fieldName = $matches[2][$index];
                $length = $matches[3][$index] ?? null;
                $modifier = $matches[4][$index] ?? null;
                
                // Skip Laravel default fields
                if (in_array($fieldName, ['id', 'created_at', 'updated_at'])) {
                    continue;
                }
                
                $fields[] = [
                    'name' => $fieldName,
                    'type' => $this->mapMigrationToFieldType($type),
                    'length' => $length,
                    'nullable' => $modifier === 'nullable',
                    'source' => 'migration'
                ];
            }
        }
        
        return $fields;
    }

    /**
     * Merge field data from different sources.
     */
    protected function mergeFieldData(array $modelFields, array $migrationFields): array
    {
        $mergedFields = [];
        $modelFieldNames = collect($modelFields)->pluck('name')->toArray();
        
        // Start with model fields as primary source
        foreach ($modelFields as $modelField) {
            $migrationField = collect($migrationFields)->firstWhere('name', $modelField['name']);
            
            if ($migrationField) {
                // Merge data, prioritizing migration for type and nullable info
                $mergedFields[] = [
                    'name' => $modelField['name'],
                    'type' => $migrationField['type'] ?? $modelField['type'],
                    'nullable' => $migrationField['nullable'] ?? $modelField['nullable'],
                    'length' => $migrationField['length'] ?? null,
                    'source' => 'merged'
                ];
            } else {
                $mergedFields[] = $modelField;
            }
        }
        
        // Add migration fields not present in model
        foreach ($migrationFields as $migrationField) {
            if (!in_array($migrationField['name'], $modelFieldNames)) {
                $mergedFields[] = $migrationField;
            }
        }
        
        return $mergedFields;
    }

    /**
     * Get property value using reflection.
     */
    protected function getPropertyValue($instance, string $property)
    {
        try {
            $reflection = new ReflectionClass($instance);
            
            if ($reflection->hasProperty($property)) {
                $prop = $reflection->getProperty($property);
                $prop->setAccessible(true);
                return $prop->getValue($instance);
            }
        } catch (Exception $e) {
            // Property doesn't exist or is not accessible
        }
        
        return null;
    }

    /**
     * Determine field type from various sources.
     */
    protected function determineFieldType(string $fieldName, array $casts, array $dates): string
    {
        // Check casts first
        if (isset($casts[$fieldName])) {
            return $this->mapCastToFieldType($casts[$fieldName]);
        }
        
        // Check if it's a date field
        if (in_array($fieldName, $dates) || Str::endsWith($fieldName, '_at')) {
            return 'datetime';
        }
        
        // Check if it's a foreign key
        if (Str::endsWith($fieldName, '_id')) {
            return 'foreign';
        }
        
        // Default type based on field name patterns
        return $this->guessTypeFromName($fieldName);
    }

    /**
     * Map Laravel cast types to generator field types.
     */
    protected function mapCastToFieldType(string $castType): string
    {
        // Handle enum classes
        if (class_exists($castType) && enum_exists($castType)) {
            return 'enum';
        }
        
        return match($castType) {
            'boolean', 'bool' => 'boolean',
            'integer', 'int' => 'integer',
            'float', 'double', 'real' => 'decimal',
            'string' => 'string',
            'array', 'json' => 'json',
            'datetime', 'date', 'timestamp' => 'datetime',
            'time' => 'time',
            default => 'string',
        };
    }

    /**
     * Map migration column types to field types.
     */
    protected function mapMigrationToFieldType(string $migrationtype): string
    {
        return match($migrationtype) {
            'string', 'char' => 'string',
            'text', 'mediumText', 'longText' => 'text',
            'integer', 'bigInteger', 'mediumInteger', 'smallInteger', 'tinyInteger' => 'integer',
            'decimal', 'double', 'float' => 'decimal',
            'boolean' => 'boolean',
            'date' => 'date',
            'datetime', 'timestamp' => 'datetime',
            'time' => 'time',
            'json' => 'json',
            'foreignId', 'unsignedBigInteger' => 'foreign',
            'enum' => 'enum',
            default => 'string',
        };
    }

    /**
     * Guess field type from field name patterns.
     */
    protected function guessTypeFromName(string $fieldName): string
    {
        $fieldName = strtolower($fieldName);
        
        if (Str::endsWith($fieldName, '_id')) {
            return 'foreign';
        }
        
        if (in_array($fieldName, ['content', 'description', 'body', 'notes', 'message'])) {
            return 'text';
        }
        
        if (in_array($fieldName, ['email', 'phone', 'url', 'slug'])) {
            return 'string';
        }
        
        if (in_array($fieldName, ['status', 'type', 'category'])) {
            return 'enum';
        }
        
        if (Str::contains($fieldName, ['is_', 'has_', 'can_', 'active', 'enabled'])) {
            return 'boolean';
        }
        
        if (Str::endsWith($fieldName, '_at')) {
            return 'datetime';
        }
        
        return 'string';
    }

    /**
     * Check if field is nullable based on various indicators.
     */
    protected function isFieldNullable(string $fieldName): bool
    {
        // Foreign keys are usually nullable
        if (Str::endsWith($fieldName, '_id')) {
            return true;
        }
        
        // These fields are usually required
        if (in_array($fieldName, ['name', 'title', 'email', 'username'])) {
            return false;
        }
        
        // Default to nullable
        return true;
    }

    /**
     * Detect relationships from model methods.
     */
    protected function detectRelationships(ReflectionClass $reflection): array
    {
        $relationships = [];
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            if ($method->getNumberOfParameters() === 0 && !$method->isStatic()) {
                $methodName = $method->getName();
                
                // Skip magic methods and common model methods
                if (Str::startsWith($methodName, '__') || 
                    in_array($methodName, ['getAttribute', 'setAttribute', 'save', 'delete', 'toArray', 'toJson'])) {
                    continue;
                }
                
                // Try to determine if it's a relationship method by name pattern
                if ($this->isLikelyRelationshipMethod($methodName)) {
                    $relationships[] = [
                        'name' => $methodName,
                        'type' => $this->guessRelationshipType($methodName),
                        'foreign_key' => Str::snake($methodName) . '_id'
                    ];
                }
            }
        }
        
        return $relationships;
    }

    /**
     * Check if method name is likely a relationship.
     */
    protected function isLikelyRelationshipMethod(string $methodName): bool
    {
        // Common relationship naming patterns
        $patterns = [
            '/^[a-z][a-zA-Z]*$/', // Simple camelCase names
        ];
        
        // Exclude common model methods
        $excludeMethods = [
            'fresh', 'refresh', 'replicate', 'exists', 'wasRecentlyCreated',
            'getKey', 'getKeyName', 'getTable', 'getConnectionName'
        ];
        
        if (in_array($methodName, $excludeMethods)) {
            return false;
        }
        
        return preg_match($patterns[0], $methodName);
    }

    /**
     * Guess relationship type from method name.
     */
    protected function guessRelationshipType(string $methodName): string
    {
        // Plural names are usually hasMany
        if (Str::plural($methodName) === $methodName) {
            return 'hasMany';
        }
        
        // Singular names are usually belongsTo
        return 'belongsTo';
    }
}
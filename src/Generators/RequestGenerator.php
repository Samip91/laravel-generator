<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;

class RequestGenerator extends BaseGenerator
{
    /**
     * Generate both Store and Update requests.
     */
    public function generate(): bool
    {
        $this->generateStoreRequest();
        $this->generateUpdateRequest();
        
        return true;
    }

    /**
     * Generate Store request.
     */
    protected function generateStoreRequest(): void
    {
        $path = app_path('Http/Requests/Store'.$this->getClassName().'Request.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getStoreStub());
        $content = $this->replaceVariables($stub, $this->getStoreVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate Update request.
     */
    protected function generateUpdateRequest(): void
    {
        $path = app_path('Http/Requests/Update'.$this->getClassName().'Request.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getUpdateStub());
        $content = $this->replaceVariables($stub, $this->getUpdateVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Get the stub file path for Store request.
     */
    public function getStub(): string
    {
        return $this->getStoreStub();
    }

    /**
     * Get the stub file path for Store request.
     */
    protected function getStoreStub(): string
    {
        return __DIR__.'/../Stubs/request.store.stub';
    }

    /**
     * Get the stub file path for Update request.
     */
    protected function getUpdateStub(): string
    {
        return __DIR__.'/../Stubs/request.update.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Http/Requests/Store'.$this->getClassName().'Request.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Http\\Requests';
    }

    /**
     * Get the variables for Store request.
     */
    protected function getStoreVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ requestClass }}'] = 'Store'.$this->getClassName().'Request';
        $variables['{{ rules }}'] = $this->buildStoreRules();
        
        return $variables;
    }

    /**
     * Get the variables for Update request.
     */
    protected function getUpdateVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ requestClass }}'] = 'Update'.$this->getClassName().'Request';
        $variables['{{ rules }}'] = $this->buildUpdateRules();
        
        return $variables;
    }

    /**
     * Build validation rules for Store request.
     */
    protected function buildStoreRules(): string
    {
        $fields = $this->parseFields();
        $rules = [];

        foreach ($fields as $field) {
            if (in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $fieldRules = $this->getFieldValidationRules($field, 'store');
            if (!empty($fieldRules)) {
                $rules[] = "'{$field['name']}' => '".implode('|', $fieldRules)."'";
            }
        }

        if (empty($rules)) {
            return '[]';
        }

        return "[\n            ".implode(",\n            ", $rules)."\n        ]";
    }

    /**
     * Build validation rules for Update request.
     */
    protected function buildUpdateRules(): string
    {
        $fields = $this->parseFields();
        $rules = [];

        foreach ($fields as $field) {
            if (in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $fieldRules = $this->getFieldValidationRules($field, 'update');
            if (!empty($fieldRules)) {
                $rules[] = "'{$field['name']}' => '".implode('|', $fieldRules)."'";
            }
        }

        if (empty($rules)) {
            return '[]';
        }

        return "[\n            ".implode(",\n            ", $rules)."\n        ]";
    }

    /**
     * Get validation rules for a specific field.
     */
    protected function getFieldValidationRules(array $field, string $context = 'store'): array
    {
        $rules = [];

        // Required rule
        if (!$field['nullable']) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Type-specific rules
        switch ($field['type']) {
            case 'string':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'text':
                $rules[] = 'string';
                break;
            case 'integer':
            case 'bigInteger':
                $rules[] = 'integer';
                break;
            case 'decimal':
            case 'float':
            case 'double':
                $rules[] = 'numeric';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'date':
                $rules[] = 'date';
                break;
            case 'datetime':
            case 'timestamp':
                $rules[] = 'date_format:Y-m-d H:i:s';
                break;
            case 'email':
                $rules[] = 'email';
                break;
            case 'json':
                $rules[] = 'array';
                break;
            case 'enum':
                $values = implode(',', $field['options']);
                $rules[] = "in:{$values}";
                break;
            case 'foreign':
                if (Str::endsWith($field['name'], '_id')) {
                    $table = Str::plural(Str::beforeLast($field['name'], '_id'));
                    $rules[] = "exists:{$table},id";
                }
                break;
        }

        // Unique rule
        if ($field['unique']) {
            $table = $this->getSnakeName();
            if ($context === 'update') {
                $rules[] = "unique:{$table},{$field['name']},{\$this->route('".Str::singular($table)."')}";
            } else {
                $rules[] = "unique:{$table},{$field['name']}";
            }
        }

        // Handle foreign key fields
        if (Str::endsWith($field['name'], '_id') && $field['type'] !== 'foreign') {
            $table = Str::plural(Str::beforeLast($field['name'], '_id'));
            $rules[] = "exists:{$table},id";
        }

        return $rules;
    }
}
<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;

class MigrationGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/migration.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        $name = 'create_'.$this->getSnakeName().'_table';
        $timestamp = date('Y_m_d_His');
        
        return database_path("migrations/{$timestamp}_{$name}.php");
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return '';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ migrationClass }}'] = 'Create'.$this->getPluralClassName().'Table';
        $variables['{{ migrationFields }}'] = $this->buildMigrationFields();
        
        return $variables;
    }

    /**
     * Build migration fields.
     */
    protected function buildMigrationFields(): string
    {
        $fields = $this->parseFields();
        $migrationFields = [];

        // Add default fields
        $migrationFields[] = '$table->id();';

        // Add custom fields
        foreach ($fields as $field) {
            $migrationFields[] = $this->buildFieldDefinition($field);
        }

        // Add timestamps
        $migrationFields[] = '$table->timestamps();';

        // Add indexes
        foreach ($fields as $field) {
            if ($field['unique']) {
                $migrationFields[] = '$table->unique(\''.$field['name'].'\');';
            }
            if ($field['index']) {
                $migrationFields[] = '$table->index(\''.$field['name'].'\');';
            }
        }

        // Add foreign key constraints
        foreach ($this->getRelationships() as $relation) {
            if ($relation['type'] === 'belongsTo') {
                $table = Str::snake(Str::plural($relation['model']));
                $migrationFields[] = '$table->foreign(\''.$relation['foreign_key'].'\')->references(\'id\')->on(\''.$table.'\')->onDelete(\'cascade\');';
            }
        }

        return implode("\n            ", $migrationFields);
    }

    /**
     * Build field definition for migration.
     */
    protected function buildFieldDefinition(array $field): string
    {
        $definition = '$table->';

        switch ($field['type']) {
            case 'string':
                $definition .= 'string(\''.$field['name'].'\')';
                break;
            case 'text':
                $definition .= 'text(\''.$field['name'].'\')';
                break;
            case 'integer':
                $definition .= 'integer(\''.$field['name'].'\')';
                break;
            case 'bigInteger':
                $definition .= 'bigInteger(\''.$field['name'].'\')';
                break;
            case 'decimal':
                $definition .= 'decimal(\''.$field['name'].'\', 8, 2)';
                break;
            case 'float':
                $definition .= 'float(\''.$field['name'].'\')';
                break;
            case 'double':
                $definition .= 'double(\''.$field['name'].'\')';
                break;
            case 'boolean':
                $definition .= 'boolean(\''.$field['name'].'\')->default(false)';
                break;
            case 'date':
                $definition .= 'date(\''.$field['name'].'\')';
                break;
            case 'datetime':
                $definition .= 'dateTime(\''.$field['name'].'\')';
                break;
            case 'timestamp':
                $definition .= 'timestamp(\''.$field['name'].'\')';
                break;
            case 'time':
                $definition .= 'time(\''.$field['name'].'\')';
                break;
            case 'json':
                $definition .= 'json(\''.$field['name'].'\')';
                break;
            case 'uuid':
                $definition .= 'uuid(\''.$field['name'].'\')';
                break;
            case 'enum':
                $values = array_map(fn($v) => "'{$v}'", $field['options']);
                $definition .= 'enum(\''.$field['name'].'\', ['.implode(', ', $values).'])';
                break;
            case 'foreign':
                $definition .= 'foreignId(\''.$field['name'].'\')';
                break;
            default:
                if (Str::endsWith($field['name'], '_id')) {
                    $definition .= 'foreignId(\''.$field['name'].'\')';
                } else {
                    $definition .= 'string(\''.$field['name'].'\')';
                }
        }

        if ($field['nullable']) {
            $definition .= '->nullable()';
        }

        $definition .= ';';

        return $definition;
    }
}
<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/model.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Models/'.$this->getClassName().'.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Models';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ uses }}'] = $this->buildUses();
        $variables['{{ fillable }}'] = $this->buildFillable();
        $variables['{{ casts }}'] = $this->buildCasts();
        $variables['{{ relationships }}'] = $this->buildRelationships();
        $variables['{{ scopes }}'] = $this->buildScopes();
        
        return $variables;
    }

    /**
     * Build use statements.
     */
    protected function buildUses(): string
    {
        $uses = [
            'use Illuminate\Database\Eloquent\Factories\HasFactory;',
            'use Illuminate\Database\Eloquent\Model;',
        ];

        // Add enum uses
        foreach ($this->getEnumFields() as $enum) {
            $enumClass = Str::studly($enum['name']).'Enum';
            $uses[] = "use App\\Enums\\{$enumClass};";
        }

        // Add relationship uses
        foreach ($this->getRelationships() as $relation) {
            if (!in_array("use App\\Models\\{$relation['model']};", $uses)) {
                $uses[] = "use App\\Models\\{$relation['model']};";
            }
        }

        return implode("\n", $uses);
    }

    /**
     * Build fillable array.
     */
    protected function buildFillable(): string
    {
        $fields = $this->parseFields();
        $fillable = [];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $fillable[] = "'{$field['name']}'";
            }
        }

        if (empty($fillable)) {
            return '[]';
        }

        return "[\n        ".implode(",\n        ", $fillable)."\n    ]";
    }

    /**
     * Build casts array.
     */
    protected function buildCasts(): string
    {
        $fields = $this->parseFields();
        $casts = [];

        foreach ($fields as $field) {
            switch ($field['type']) {
                case 'boolean':
                    $casts[] = "'{$field['name']}' => 'boolean'";
                    break;
                case 'integer':
                case 'bigInteger':
                    $casts[] = "'{$field['name']}' => 'integer'";
                    break;
                case 'decimal':
                case 'float':
                case 'double':
                    $casts[] = "'{$field['name']}' => 'float'";
                    break;
                case 'date':
                    $casts[] = "'{$field['name']}' => 'date'";
                    break;
                case 'datetime':
                case 'timestamp':
                    $casts[] = "'{$field['name']}' => 'datetime'";
                    break;
                case 'json':
                    $casts[] = "'{$field['name']}' => 'array'";
                    break;
                case 'enum':
                    $enumClass = Str::studly($field['name']).'Enum';
                    $casts[] = "'{$field['name']}' => {$enumClass}::class";
                    break;
            }
        }

        if (empty($casts)) {
            return '[]';
        }

        return "[\n        ".implode(",\n        ", $casts)."\n    ]";
    }

    /**
     * Build relationship methods.
     */
    protected function buildRelationships(): string
    {
        $relationships = $this->getRelationships();
        $methods = [];

        foreach ($relationships as $name => $relation) {
            $methods[] = $this->buildRelationshipMethod($name, $relation);
        }

        // Add reverse relationships (hasMany)
        $methods[] = $this->buildHasManyRelationships();

        return implode("\n\n    ", array_filter($methods));
    }

    /**
     * Build a single relationship method.
     */
    protected function buildRelationshipMethod(string $name, array $relation): string
    {
        $method = "public function {$name}()\n    {\n";
        
        switch ($relation['type']) {
            case 'belongsTo':
                $method .= "        return \$this->belongsTo({$relation['model']}::class";
                if ($relation['foreign_key'] !== $name.'_id') {
                    $method .= ", '{$relation['foreign_key']}'";
                }
                $method .= ");\n";
                break;
        }
        
        $method .= "    }";
        
        return $method;
    }

    /**
     * Build hasMany relationships.
     */
    protected function buildHasManyRelationships(): string
    {
        return '';
    }

    /**
     * Build scope methods.
     */
    protected function buildScopes(): string
    {
        $scopes = [];
        $generatedScopes = []; // Track generated scope names to avoid duplicates
        $fields = $this->parseFields();

        // Add common scopes based on field types
        foreach ($fields as $field) {
            if ($field['type'] === 'enum') {
                foreach ($field['options'] as $option) {
                    $scopeName = 'scope'.Str::studly($option);
                    // Only add if not already generated
                    if (!in_array($scopeName, $generatedScopes)) {
                        $scopes[] = "public function {$scopeName}(\$query)\n    {\n        return \$query->where('{$field['name']}', '{$option}');\n    }";
                        $generatedScopes[] = $scopeName;
                    }
                }
            }

            if ($field['name'] === 'status') {
                $scopeName = 'scopeActive';
                // Only add if not already generated
                if (!in_array($scopeName, $generatedScopes)) {
                    $scopes[] = "public function {$scopeName}(\$query)\n    {\n        return \$query->where('status', 'active');\n    }";
                    $generatedScopes[] = $scopeName;
                }
            }
        }

        return implode("\n\n    ", $scopes);
    }
}
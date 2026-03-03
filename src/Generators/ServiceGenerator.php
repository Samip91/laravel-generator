<?php

namespace Brikshya\LaravelGenerator\Generators;

class ServiceGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/service.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Services/'.$this->getClassName().'Service.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Services';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ serviceClass }}'] = $this->getClassName().'Service';
        $variables['{{ uses }}'] = $this->buildUses();
        $variables['{{ withRelations }}'] = $this->buildWithRelations();
        
        return $variables;
    }

    /**
     * Build use statements.
     */
    protected function buildUses(): string
    {
        $uses = [
            'use App\\Models\\'.$this->getClassName().';',
            'use Illuminate\\Pagination\\LengthAwarePaginator;',
            'use Illuminate\\Database\\Eloquent\\Collection;',
        ];

        return implode("\n", $uses);
    }

    /**
     * Build with relations clause for queries.
     */
    protected function buildWithRelations(): string
    {
        $fields = $this->parseFields();
        $relations = [];

        foreach ($fields as $field) {
            if ($field['type'] === 'foreign' || str_ends_with($field['name'], '_id')) {
                $relationName = str_replace('_id', '', $field['name']);
                $relations[] = "'".$relationName."'";
            }
        }

        if (empty($relations)) {
            return '';
        }

        return 'with(['.implode(', ', $relations).'])->';
    }
}
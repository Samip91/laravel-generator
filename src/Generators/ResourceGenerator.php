<?php

namespace Brikshya\LaravelGenerator\Generators;

class ResourceGenerator extends BaseGenerator
{
    /**
     * Generate both Resource and ResourceCollection.
     */
    public function generate(): bool
    {
        $this->generateResource();
        $this->generateResourceCollection();
        
        return true;
    }

    /**
     * Generate Resource class.
     */
    protected function generateResource(): void
    {
        $path = app_path('Http/Resources/'.$this->getClassName().'Resource.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getStub());
        $content = $this->replaceVariables($stub, $this->getVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate ResourceCollection class.
     */
    protected function generateResourceCollection(): void
    {
        $path = app_path('Http/Resources/'.$this->getClassName().'ResourceCollection.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getCollectionStub());
        $content = $this->replaceVariables($stub, $this->getCollectionVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/resource.stub';
    }

    /**
     * Get the collection stub file path.
     */
    protected function getCollectionStub(): string
    {
        return __DIR__.'/../Stubs/resource.collection.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Http/Resources/'.$this->getClassName().'Resource.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Http\\Resources';
    }

    /**
     * Get the variables for ResourceCollection.
     */
    protected function getCollectionVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ resourceClass }}'] = $this->getClassName().'ResourceCollection';
        
        return $variables;
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ resourceClass }}'] = $this->getClassName().'Resource';
        $variables['{{ resourceFields }}'] = $this->buildResourceFields();
        
        return $variables;
    }

    /**
     * Build resource fields array.
     */
    protected function buildResourceFields(): string
    {
        $fields = $this->parseFields();
        $resourceFields = ['"id" => $this->id'];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $resourceFields[] = '"'.$field['name'].'" => $this->'.$field['name'];
            }
        }

        $resourceFields[] = '"created_at" => $this->created_at';
        $resourceFields[] = '"updated_at" => $this->updated_at';

        return implode(",\n            ", $resourceFields);
    }
}
<?php

namespace Brikshya\LaravelGenerator\Generators;

class SeederGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/seeder.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return database_path('seeders/'.$this->getClassName().'Seeder.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'Database\\Seeders';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ seederClass }}'] = $this->getClassName().'Seeder';
        
        return $variables;
    }
}
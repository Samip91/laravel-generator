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
}
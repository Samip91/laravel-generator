<?php

namespace Brikshya\LaravelGenerator\Generators;

class PolicyGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/policy.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Policies/'.$this->getClassName().'Policy.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Policies';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ policyClass }}'] = $this->getClassName().'Policy';
        
        return $variables;
    }
}
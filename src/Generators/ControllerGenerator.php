<?php

namespace Brikshya\LaravelGenerator\Generators;

use Brikshya\LaravelGenerator\Services\UIFrameworkDetector;

class ControllerGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        if ($this->options['api_only'] ?? false) {
            return __DIR__.'/../Stubs/controller.api.stub';
        }

        // Detect UI framework
        $detector = new UIFrameworkDetector($this->files);
        $framework = $detector->detect();
        
        if ($framework['name'] === 'breeze' && $framework['has_authentication']) {
            return __DIR__.'/../Stubs/controller.breeze.stub';
        }
        
        return __DIR__.'/../Stubs/controller.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return app_path('Http/Controllers/'.$this->getClassName().'Controller.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Http\\Controllers';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ uses }}'] = $this->buildUses();
        $variables['{{ controllerClass }}'] = $this->getClassName().'Controller';
        
        return $variables;
    }

    /**
     * Build use statements.
     */
    protected function buildUses(): string
    {
        $uses = [
            'use App\\Http\\Controllers\\Controller;',
            'use App\\Models\\'.$this->getClassName().';',
            'use App\\Services\\'.$this->getClassName().'Service;',
            'use App\\Http\\Requests\\Store'.$this->getClassName().'Request;',
            'use App\\Http\\Requests\\Update'.$this->getClassName().'Request;',
        ];

        if ($this->options['api_only'] ?? false) {
            $uses[] = 'use App\\Http\\Resources\\'.$this->getClassName().'Resource;';
            $uses[] = 'use Illuminate\\Http\\JsonResponse;';
        } else {
            $uses[] = 'use Illuminate\\Http\\RedirectResponse;';
            $uses[] = 'use Illuminate\\View\\View;';
        }

        return implode("\n", $uses);
    }
}
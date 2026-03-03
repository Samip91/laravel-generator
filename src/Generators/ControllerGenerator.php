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

        // Check if service exists or should be used
        $useService = $this->shouldUseService();

        // Detect UI framework
        $detector = new UIFrameworkDetector($this->files);
        $framework = $detector->detect();
        
        if ($framework['name'] === 'breeze' && $framework['has_authentication']) {
            return $useService 
                ? __DIR__.'/../Stubs/controller.breeze.stub'
                : __DIR__.'/../Stubs/controller.breeze.no-service.stub';
        }
        
        return $useService 
            ? __DIR__.'/../Stubs/controller.stub'
            : __DIR__.'/../Stubs/controller.no-service.stub';
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
     * Check if service should be used in controller.
     */
    protected function shouldUseService(): bool
    {
        // If service option is explicitly set, use it
        if (isset($this->options['service'])) {
            return $this->options['service'];
        }

        // Check if service file exists
        $servicePath = app_path('Services/' . $this->getClassName() . 'Service.php');
        return $this->files->exists($servicePath);
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ uses }}'] = $this->buildUses();
        $variables['{{ controllerClass }}'] = $this->getClassName().'Controller';
        $variables['{{ createViewData }}'] = $this->buildCreateViewData();
        $variables['{{ editViewData }}'] = $this->buildEditViewData();
        $variables['{{ createCompact }}'] = $this->buildCreateCompact();
        $variables['{{ editCompact }}'] = $this->buildEditCompact();
        
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

    /**
     * Build view data for create method.
     */
    protected function buildCreateViewData(): string
    {
        $fields = $this->parseFields();
        $viewData = [];

        foreach ($fields as $field) {
            if ($field['type'] === 'foreign' || str_ends_with($field['name'], '_id')) {
                $relationName = str_replace('_id', '', $field['name']);
                $modelName = ucfirst($relationName);
                $pluralName = \Illuminate\Support\Str::camel(\Illuminate\Support\Str::plural($relationName));
                $viewData[] = '$'.$pluralName.' = \\App\\Models\\'.$modelName.'::all();';
            }
        }

        return empty($viewData) ? '' : implode("\n        ", $viewData);
    }

    /**
     * Build view data for edit method.
     */
    protected function buildEditViewData(): string
    {
        return $this->buildCreateViewData();
    }

    /**
     * Build compact array for create method.
     */
    protected function buildCreateCompact(): string
    {
        $fields = $this->parseFields();
        $compactVars = [];

        foreach ($fields as $field) {
            if ($field['type'] === 'foreign' || str_ends_with($field['name'], '_id')) {
                $relationName = str_replace('_id', '', $field['name']);
                $pluralName = \Illuminate\Support\Str::camel(\Illuminate\Support\Str::plural($relationName));
                $compactVars[] = "'".$pluralName."'";
            }
        }

        if (empty($compactVars)) {
            return '';
        }

        return ', compact('.implode(', ', $compactVars).')';
    }

    /**
     * Build compact array for edit method.
     */
    protected function buildEditCompact(): string
    {
        $fields = $this->parseFields();
        $compactVars = [];

        foreach ($fields as $field) {
            if ($field['type'] === 'foreign' || str_ends_with($field['name'], '_id')) {
                $relationName = str_replace('_id', '', $field['name']);
                $pluralName = \Illuminate\Support\Str::camel(\Illuminate\Support\Str::plural($relationName));
                $compactVars[] = "'".$pluralName."'";
            }
        }

        if (empty($compactVars)) {
            return '';
        }

        return ', '.implode(', ', $compactVars);
    }
}
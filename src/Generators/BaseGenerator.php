<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Contracts\GeneratorInterface;

abstract class BaseGenerator implements GeneratorInterface
{
    protected Filesystem $files;
    protected string $name;
    protected array $fields;
    protected array $options;

    public function __construct(Filesystem $files, string $name, array $fields = [], array $options = [])
    {
        $this->files = $files;
        $this->name = $name;
        $this->fields = $fields;
        $this->options = $options;
    }

    /**
     * Generate the component.
     */
    public function generate(): bool
    {
        $path = $this->getPath();

        // Create directory if it doesn't exist
        $this->makeDirectory($path);

        // Generate the file content
        $content = $this->buildClass();

        // Write the file
        $this->files->put($path, $content);

        return true;
    }

    /**
     * Build the class with the given name.
     */
    public function buildClass(): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceVariables($stub, $this->getVariables());
    }

    /**
     * Replace variables in the stub.
     */
    protected function replaceVariables(string $stub, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $stub = str_replace($key, $value, $stub);
        }

        return $stub;
    }

    /**
     * Build the directory for the class if necessary.
     */
    protected function makeDirectory(string $path): string
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the class name.
     */
    protected function getClassName(): string
    {
        return Str::studly(Str::singular($this->name));
    }

    /**
     * Get the plural class name.
     */
    protected function getPluralClassName(): string
    {
        return Str::studly($this->name);
    }

    /**
     * Get the snake case name.
     */
    protected function getSnakeName(): string
    {
        return Str::snake($this->name);
    }

    /**
     * Get the kebab case name.
     */
    protected function getKebabName(): string
    {
        return Str::kebab($this->name);
    }

    /**
     * Get the camel case name.
     */
    protected function getCamelName(): string
    {
        return Str::camel($this->name);
    }

    /**
     * Get the singular name.
     */
    protected function getSingularName(): string
    {
        return Str::singular($this->name);
    }

    /**
     * Get the plural name.
     */
    protected function getPluralName(): string
    {
        return Str::plural($this->getSingularName());
    }

    /**
     * Parse field definition.
     */
    protected function parseFields(): array
    {
        $parsed = [];

        foreach ($this->fields as $field) {
            if (is_string($field)) {
                $parts = explode(':', $field);
                $name = $parts[0];
                $type = $parts[1] ?? 'string';
                $options = array_slice($parts, 2);

                $parsed[$name] = [
                    'name' => $name,
                    'type' => $type,
                    'options' => $options,
                    'nullable' => in_array('nullable', $options),
                    'unique' => in_array('unique', $options),
                    'index' => in_array('index', $options),
                ];
            } else {
                $parsed[$field['name']] = $field;
            }
        }

        return $parsed;
    }

    /**
     * Get relationships from fields.
     */
    protected function getRelationships(): array
    {
        $relationships = [];
        $fields = $this->parseFields();

        foreach ($fields as $field) {
            if ($field['type'] === 'foreign' || Str::endsWith($field['name'], '_id')) {
                $relationName = Str::beforeLast($field['name'], '_id');
                $relationships[$relationName] = [
                    'type' => 'belongsTo',
                    'model' => Str::studly($relationName),
                    'foreign_key' => $field['name'],
                ];
            }
        }

        return $relationships;
    }

    /**
     * Get enum fields.
     */
    protected function getEnumFields(): array
    {
        $enums = [];
        $fields = $this->parseFields();

        foreach ($fields as $field) {
            if ($field['type'] === 'enum') {
                $enums[$field['name']] = [
                    'name' => $field['name'],
                    'values' => $field['options'] ?? ['active', 'inactive'],
                ];
            }
        }

        return $enums;
    }

    /**
     * Get the base variables for replacement.
     */
    public function getVariables(): array
    {
        return [
            '{{ namespace }}' => $this->getNamespace(),
            '{{ class }}' => $this->getClassName(),
            '{{ pluralClass }}' => $this->getPluralClassName(),
            '{{ variable }}' => $this->getCamelName(),
            '{{ pluralVariable }}' => Str::camel($this->getPluralName()),
            '{{ model }}' => $this->getClassName(),
            '{{ modelVariable }}' => $this->getCamelName(),
            '{{ table }}' => $this->getSnakeName(),
            '{{ singularName }}' => $this->getSingularName(),
            '{{ pluralName }}' => $this->getPluralName(),
            '{{ kebabName }}' => $this->getKebabName(),
            '{{ snakeName }}' => $this->getSnakeName(),
        ];
    }

    /**
     * Get the default namespace for the class.
     */
    abstract protected function getNamespace(): string;
}
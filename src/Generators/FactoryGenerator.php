<?php

namespace Brikshya\LaravelGenerator\Generators;

class FactoryGenerator extends BaseGenerator
{
    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/factory.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return database_path('factories/'.$this->getClassName().'Factory.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'Database\\Factories';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ factoryClass }}'] = $this->getClassName().'Factory';
        $variables['{{ factoryFields }}'] = $this->buildFactoryFields();
        
        return $variables;
    }

    /**
     * Build factory field definitions.
     */
    protected function buildFactoryFields(): string
    {
        $fields = $this->parseFields();
        $factoryFields = [];

        foreach ($fields as $field) {
            if (in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $factoryFields[] = $this->getFactoryFieldDefinition($field);
        }

        return implode(",\n            ", $factoryFields);
    }

    /**
     * Get factory field definition.
     */
    protected function getFactoryFieldDefinition(array $field): string
    {
        return match($field['type']) {
            'string' => "'{$field['name']}' => fake()->words(3, true)",
            'text' => "'{$field['name']}' => fake()->paragraph()",
            'integer' => "'{$field['name']}' => fake()->numberBetween(1, 100)",
            'decimal', 'float' => "'{$field['name']}' => fake()->randomFloat(2, 0, 1000)",
            'boolean' => "'{$field['name']}' => fake()->boolean()",
            'date' => "'{$field['name']}' => fake()->date()",
            'datetime' => "'{$field['name']}' => fake()->dateTime()",
            'email' => "'{$field['name']}' => fake()->email()",
            'enum' => "'{$field['name']}' => fake()->randomElement(['".implode("', '", $field['options'])."'])",
            default => "'{$field['name']}' => fake()->word()",
        };
    }
}
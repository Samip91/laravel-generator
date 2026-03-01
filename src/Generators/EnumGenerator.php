<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;

class EnumGenerator extends BaseGenerator
{
    /**
     * Check if there are enums to generate.
     */
    public function hasEnums(): bool
    {
        return !empty($this->getEnumFields());
    }

    /**
     * Generate all enum classes.
     */
    public function generate(): bool
    {
        $enumFields = $this->getEnumFields();

        foreach ($enumFields as $enum) {
            $this->generateEnum($enum);
        }

        return true;
    }

    /**
     * Generate a single enum class.
     */
    protected function generateEnum(array $enum): void
    {
        $enumName = Str::studly($enum['name']) . 'Enum';
        $path = app_path('Enums/' . $enumName . '.php');
        
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getStub());
        $content = $this->replaceVariables($stub, $this->getEnumVariables($enum, $enumName));
        
        $this->files->put($path, $content);
    }

    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/enum.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        $enumName = Str::studly($this->name) . 'Enum';
        return app_path('Enums/' . $enumName . '.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'App\\Enums';
    }

    /**
     * Get the variables for enum generation.
     */
    protected function getEnumVariables(array $enum, string $enumName): array
    {
        return [
            '{{ namespace }}' => $this->getNamespace(),
            '{{ enumClass }}' => $enumName,
            '{{ cases }}' => $this->buildEnumCases($enum['values']),
            '{{ methods }}' => $this->buildEnumMethods($enum['values']),
        ];
    }

    /**
     * Build enum cases.
     */
    protected function buildEnumCases(array $values): string
    {
        $cases = [];
        
        foreach ($values as $value) {
            $caseName = strtoupper(Str::snake($value));
            $cases[] = "case {$caseName} = '{$value}';";
        }

        return implode("\n    ", $cases);
    }

    /**
     * Build enum helper methods.
     */
    protected function buildEnumMethods(array $values): string
    {
        $methods = [];

        // Add label method
        $labelCases = [];
        foreach ($values as $value) {
            $caseName = strtoupper(Str::snake($value));
            $label = Str::title(str_replace(['_', '-'], ' ', $value));
            $labelCases[] = "self::{$caseName} => '{$label}'";
        }

        $methods[] = "public function label(): string\n    {\n        return match(\$this) {\n            ".implode(",\n            ", $labelCases).",\n        };\n    }";

        // Add is methods for each value
        foreach ($values as $value) {
            $methodName = 'is'.Str::studly($value);
            $caseName = strtoupper(Str::snake($value));
            $methods[] = "public function {$methodName}(): bool\n    {\n        return \$this === self::{$caseName};\n    }";
        }

        // Add static values method
        $methods[] = "public static function values(): array\n    {\n        return array_column(self::cases(), 'value');\n    }";

        return implode("\n\n    ", $methods);
    }

    /**
     * Get color for status values.
     */
    protected function getStatusColor(string $status): string
    {
        return match($status) {
            'active', 'approved', 'published', 'completed' => 'green',
            'inactive', 'rejected', 'cancelled' => 'red',
            'pending', 'processing', 'draft' => 'yellow',
            'new' => 'blue',
            default => 'gray',
        };
    }
}
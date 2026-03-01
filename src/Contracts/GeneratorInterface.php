<?php

namespace Brikshya\LaravelGenerator\Contracts;

interface GeneratorInterface
{
    /**
     * Generate the component.
     */
    public function generate(): bool;

    /**
     * Get the stub file path.
     */
    public function getStub(): string;

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string;

    /**
     * Replace placeholders in the stub with actual values.
     */
    public function buildClass(): string;

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array;
}
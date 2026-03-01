<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;

class TestGenerator extends BaseGenerator
{
    /**
     * Generate both Feature and Unit tests.
     */
    public function generate(): bool
    {
        $this->generateFeatureTest();
        // Could also generate unit tests here if needed
        
        return true;
    }

    /**
     * Generate Feature test.
     */
    protected function generateFeatureTest(): void
    {
        $path = base_path('tests/Feature/'.$this->getClassName().'Test.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getStub());
        $content = $this->replaceVariables($stub, $this->getVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Get the stub file path.
     */
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/test.feature.stub';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return base_path('tests/Feature/'.$this->getClassName().'Test.php');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return 'Tests\\Feature';
    }

    /**
     * Get the variables to replace in the stub.
     */
    public function getVariables(): array
    {
        $variables = parent::getVariables();
        
        $variables['{{ testClass }}'] = $this->getClassName().'Test';
        $variables['{{ uses }}'] = $this->buildUses();
        $variables['{{ testMethods }}'] = $this->buildTestMethods();
        
        return $variables;
    }

    /**
     * Build use statements.
     */
    protected function buildUses(): string
    {
        $uses = [
            'use Illuminate\Foundation\Testing\RefreshDatabase;',
            'use Tests\TestCase;',
            'use App\Models\\'.$this->getClassName().';',
        ];

        return implode("\n", $uses);
    }

    /**
     * Build test methods.
     */
    protected function buildTestMethods(): string
    {
        $methods = [];
        $modelVariable = $this->getCamelName();
        $pluralVariable = Str::camel($this->name);
        $routeName = $this->getKebabName();

        // Test index
        $methods[] = $this->buildIndexTest($pluralVariable, $routeName);

        // Test create
        $methods[] = $this->buildCreateTest($routeName);

        // Test store
        $methods[] = $this->buildStoreTest($modelVariable, $routeName);

        // Test show
        $methods[] = $this->buildShowTest($modelVariable, $routeName);

        // Test edit
        $methods[] = $this->buildEditTest($modelVariable, $routeName);

        // Test update
        $methods[] = $this->buildUpdateTest($modelVariable, $routeName);

        // Test destroy
        $methods[] = $this->buildDestroyTest($modelVariable, $routeName);

        // Test validation
        $methods[] = $this->buildValidationTest($routeName);

        return implode("\n\n    ", $methods);
    }

    /**
     * Build index test method.
     */
    protected function buildIndexTest(string $pluralVariable, string $routeName): string
    {
        return "public function test_can_view_{$pluralVariable}_index(): void\n    {\n        \${$pluralVariable} = {$this->getClassName()}::factory()->count(3)->create();\n\n        \$response = \$this->get(route('{$routeName}.index'));\n\n        \$response->assertStatus(200);\n        \$response->assertViewIs('{$routeName}.index');\n        \$response->assertViewHas('{$pluralVariable}');\n    }";
    }

    /**
     * Build create test method.
     */
    protected function buildCreateTest(string $routeName): string
    {
        return "public function test_can_view_create_form(): void\n    {\n        \$response = \$this->get(route('{$routeName}.create'));\n\n        \$response->assertStatus(200);\n        \$response->assertViewIs('{$routeName}.create');\n    }";
    }

    /**
     * Build store test method.
     */
    protected function buildStoreTest(string $modelVariable, string $routeName): string
    {
        return "public function test_can_create_{$modelVariable}(): void\n    {\n        \$data = {$this->getClassName()}::factory()->make()->toArray();\n\n        \$response = \$this->post(route('{$routeName}.store'), \$data);\n\n        \$response->assertRedirect();\n        \$this->assertDatabaseHas('{$this->getSnakeName()}', \$data);\n    }";
    }

    /**
     * Build show test method.
     */
    protected function buildShowTest(string $modelVariable, string $routeName): string
    {
        return "public function test_can_view_{$modelVariable}(): void\n    {\n        \${$modelVariable} = {$this->getClassName()}::factory()->create();\n\n        \$response = \$this->get(route('{$routeName}.show', \${$modelVariable}));\n\n        \$response->assertStatus(200);\n        \$response->assertViewIs('{$routeName}.show');\n        \$response->assertViewHas('{$modelVariable}');\n    }";
    }

    /**
     * Build edit test method.
     */
    protected function buildEditTest(string $modelVariable, string $routeName): string
    {
        return "public function test_can_view_edit_form(): void\n    {\n        \${$modelVariable} = {$this->getClassName()}::factory()->create();\n\n        \$response = \$this->get(route('{$routeName}.edit', \${$modelVariable}));\n\n        \$response->assertStatus(200);\n        \$response->assertViewIs('{$routeName}.edit');\n        \$response->assertViewHas('{$modelVariable}');\n    }";
    }

    /**
     * Build update test method.
     */
    protected function buildUpdateTest(string $modelVariable, string $routeName): string
    {
        return "public function test_can_update_{$modelVariable}(): void\n    {\n        \${$modelVariable} = {$this->getClassName()}::factory()->create();\n        \$data = {$this->getClassName()}::factory()->make()->toArray();\n\n        \$response = \$this->put(route('{$routeName}.update', \${$modelVariable}), \$data);\n\n        \$response->assertRedirect();\n        \$this->assertDatabaseHas('{$this->getSnakeName()}', \$data);\n    }";
    }

    /**
     * Build destroy test method.
     */
    protected function buildDestroyTest(string $modelVariable, string $routeName): string
    {
        return "public function test_can_delete_{$modelVariable}(): void\n    {\n        \${$modelVariable} = {$this->getClassName()}::factory()->create();\n\n        \$response = \$this->delete(route('{$routeName}.destroy', \${$modelVariable}));\n\n        \$response->assertRedirect();\n        \$this->assertDatabaseMissing('{$this->getSnakeName()}', ['{$this->getSnakeName()}_id' => \${$modelVariable}->id]);\n    }";
    }

    /**
     * Build validation test method.
     */
    protected function buildValidationTest(string $routeName): string
    {
        $fields = $this->parseFields();
        $requiredFields = [];

        foreach ($fields as $field) {
            if (!$field['nullable'] && !in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $requiredFields[] = "'{$field['name']}'";
            }
        }

        $fieldsString = implode(', ', $requiredFields);

        return "public function test_validation_rules(): void\n    {\n        \$response = \$this->post(route('{$routeName}.store'), []);\n\n        \$response->assertSessionHasErrors([{$fieldsString}]);\n    }";
    }
}
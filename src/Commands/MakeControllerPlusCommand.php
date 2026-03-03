<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Traits\HandlesFields;
use Brikshya\LaravelGenerator\Generators\ControllerGenerator;
use Brikshya\LaravelGenerator\Generators\RequestGenerator;
use Brikshya\LaravelGenerator\Generators\ResourceGenerator;

class MakeControllerPlusCommand extends Command
{
    use HandlesFields;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:controller+ {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--m|migration : Also create migration}
                            {--model= : Model to use (defaults to controller name)}
                            {--r|resource : Generate resource controller}
                            {--api : Generate API controller}
                            {--requests : Generate form request classes}
                            {--resources : Generate API resource classes}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a controller with auto-detected fields from model, with optional migration and resources.';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $modelName = $this->option('model') ?: str_replace('Controller', '', $name);
        
        $fields = $this->parseFields($modelName);
        $options = $this->getOptions();

        $this->info("Generating controller: {$name}");

        if ($this->option('migration')) {
            $this->generateMigration($modelName, $fields, $options);
        }

        $this->generateController($name, $fields, $options);

        if ($this->option('requests')) {
            $this->generateRequests($modelName, $fields, $options);
        }

        if ($this->option('resources')) {
            $this->generateResources($modelName, $fields, $options);
        }

        $this->info('Controller generated successfully!');
        $this->displayGeneratedFiles($name, $modelName, $options);

        return self::SUCCESS;
    }

    /**
     * Parse fields from the command option or auto-detect from model.
     */
    protected function parseFields(string $modelName): array
    {
        $fieldsOption = $this->option('fields');
        
        // Check if model exists for auto-detection
        if (!$fieldsOption && !$this->modelExists($modelName)) {
            $this->error("Model '{$modelName}' not found for auto-detection.");
            $this->line("Either:");
            $this->line("  1. Create the model first: php artisan make:model+ {$modelName} --fields=\"name:string,email:string\"");
            $this->line("  2. Provide explicit fields: php artisan make:controller+ " . $this->argument('name') . " --fields=\"name:string,email:string\"");
            throw new \InvalidArgumentException("Model '{$modelName}' not found. Create the model first or use --fields option.");
        }
        
        $fields = $this->getFieldDefinitions($modelName, $fieldsOption);
        
        if (!empty($fields)) {
            $this->displayDetectedFields($fields, $fieldsOption ? 'fields option' : 'model analysis');
        } elseif (!$fieldsOption) {
            $this->warn("No fields detected from model '{$modelName}'. Controller will be created with basic structure.");
        }
        
        return $fields;
    }

    /**
     * Check if model class exists.
     */
    protected function modelExists(string $modelName): bool
    {
        $modelClass = "App\\Models\\" . Str::studly(Str::singular($modelName));
        return class_exists($modelClass) || $this->files->exists(app_path("Models/" . Str::studly(Str::singular($modelName)) . ".php"));
    }

    /**
     * Get command options.
     */
    protected function getOptions(): array
    {
        return [
            'api' => $this->option('api'),
            'resource' => $this->option('resource'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Generate migration.
     */
    protected function generateMigration(string $modelName, array $fields, array $options): void
    {
        $this->call('make:migration', [
            'name' => 'create_' . Str::snake(Str::plural($modelName)) . '_table',
            '--create' => Str::snake(Str::plural($modelName))
        ]);
        
        $this->line('✓ Migration created');
    }

    /**
     * Generate controller.
     */
    protected function generateController(string $name, array $fields, array $options): void
    {
        $generator = new ControllerGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Controller created');
    }

    /**
     * Generate form requests.
     */
    protected function generateRequests(string $modelName, array $fields, array $options): void
    {
        $generator = new RequestGenerator($this->files, $modelName, $fields, $options);
        $generator->generate();
        $this->line('✓ Form requests created');
    }

    /**
     * Generate API resources.
     */
    protected function generateResources(string $modelName, array $fields, array $options): void
    {
        $generator = new ResourceGenerator($this->files, $modelName, $fields, $options);
        $generator->generate();
        $this->line('✓ API resources created');
    }

    /**
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $controllerName, string $modelName, array $options): void
    {
        $className = Str::studly(Str::singular($modelName));
        
        $this->info('Generated files:');
        $this->line("  Controller: app/Http/Controllers/{$controllerName}.php");
        
        if ($this->option('migration')) {
            $tableName = Str::snake(Str::plural($modelName));
            $this->line("  Migration: database/migrations/create_{$tableName}_table.php");
        }
        
        if ($this->option('requests')) {
            $this->line("  Store Request: app/Http/Requests/Store{$className}Request.php");
            $this->line("  Update Request: app/Http/Requests/Update{$className}Request.php");
        }
        
        if ($this->option('resources')) {
            $this->line("  Resource: app/Http/Resources/{$className}Resource.php");
            $this->line("  Resource Collection: app/Http/Resources/{$className}Collection.php");
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line("1. Add routes to your routes file");
        $this->line("2. Update the generated files as needed");
    }
}
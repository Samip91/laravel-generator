<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Traits\HandlesFields;
use Brikshya\LaravelGenerator\Generators\ResourceGenerator;

class MakeResourcePlusCommand extends Command
{
    use HandlesFields;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:resource+ {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--model= : Model to use for auto-detection (defaults to name)}
                            {--collection : Also generate a resource collection}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate API resource classes with auto-detected fields from model for JSON API responses.';

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
        $modelName = $this->option('model') ?: $name;
        
        $fields = $this->parseFields($modelName);
        $options = $this->getOptions();

        $this->info("Generating API resource(s) for: {$name}");

        $this->generateResources($name, $fields, $options);

        $this->info('API resources generated successfully!');
        $this->displayGeneratedFiles($name, $options);

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
            $this->line("  2. Provide explicit fields: php artisan make:resource+ " . $this->argument('name') . " --fields=\"name:string,email:string\"");
            throw new \InvalidArgumentException("Model '{$modelName}' not found. Create the model first or use --fields option.");
        }
        
        $fields = $this->getFieldDefinitions($modelName, $fieldsOption);
        
        if (!empty($fields)) {
            $this->displayDetectedFields($fields, $fieldsOption ? 'fields option' : 'model analysis');
        } elseif (!$fieldsOption) {
            $this->warn("No fields detected from model '{$modelName}'. API resources will be created with basic structure.");
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
            'collection' => $this->option('collection'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Generate API resources.
     */
    protected function generateResources(string $name, array $fields, array $options): void
    {
        $generator = new ResourceGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        
        if ($options['collection']) {
            $this->line('✓ Resource and Collection created');
        } else {
            $this->line('✓ Resource created');
        }
    }

    /**
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $name, array $options): void
    {
        $className = Str::studly(Str::singular($name));
        
        $this->info('Generated files:');
        $this->line("  Resource: app/Http/Resources/{$className}Resource.php");
        
        if ($options['collection']) {
            $this->line("  Collection: app/Http/Resources/{$className}Collection.php");
        }

        $this->newLine();
        $this->info('Generated features:');
        $this->line('• Auto-mapped field transformations');
        $this->line('• Conditional field inclusion');
        $this->line('• Relationship loading support');
        $this->line('• Meta information helpers');
        
        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Customize field transformations as needed');
        $this->line('2. Add conditional field logic (e.g., hide sensitive data)');
        $this->line('3. Configure relationship loading in the resource');
        $this->line('4. Use the resources in your API controllers');
        $this->line('   Example: return new ' . $className . 'Resource($model);');
        
        if ($options['collection']) {
            $this->line('   Example: return new ' . $className . 'Collection($models);');
        }
    }
}
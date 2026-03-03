<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Traits\HandlesFields;
use Brikshya\LaravelGenerator\Generators\ModelGenerator;
use Brikshya\LaravelGenerator\Generators\MigrationGenerator;
use Brikshya\LaravelGenerator\Generators\ControllerGenerator;
use Brikshya\LaravelGenerator\Generators\FactoryGenerator;
use Brikshya\LaravelGenerator\Generators\SeederGenerator;
use Brikshya\LaravelGenerator\Generators\PolicyGenerator;
use Brikshya\LaravelGenerator\Generators\RequestGenerator;

class MakeModelPlusCommand extends Command
{
    use HandlesFields;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:model+ {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--m|migration : Also create migration}
                            {--c|controller : Also create controller}
                            {--r|resource : Create resource controller}
                            {--R|requests : Also create form request classes}
                            {--f|factory : Also create factory}
                            {--s|seed : Also create seeder}
                            {--p|policy : Also create policy}
                            {--a|all : Create migration, factory, seeder, policy, resource controller, and form requests}
                            {--api : Create API controller instead of web controller}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a model with auto-detected fields, with optional migration, controller, factory, seeder, and policy.';

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
        $fields = $this->parseFields($name);
        $options = $this->getOptions();

        $this->info("Generating model: {$name}");

        // Generate model
        $this->generateModel($name, $fields, $options);

        // Generate additional components based on options
        if ($this->option('migration') || $this->option('all')) {
            $this->generateMigration($name, $fields, $options);
        }

        if ($this->option('controller') || $this->option('resource') || $this->option('all')) {
            $this->generateController($name, $fields, $options);
        }

        if ($this->option('factory') || $this->option('all')) {
            $this->generateFactory($name, $fields, $options);
        }

        if ($this->option('seed') || $this->option('all')) {
            $this->generateSeeder($name, $fields, $options);
        }

        if ($this->option('policy') || $this->option('all')) {
            $this->generatePolicy($name, $fields, $options);
        }

        if ($this->option('requests') || $this->option('all')) {
            $this->generateRequests($name, $fields, $options);
        }

        $this->info('Model generated successfully!');
        $this->displayGeneratedFiles($name, $options);

        return self::SUCCESS;
    }

    /**
     * Parse fields from the command option or prompt for new models.
     */
    protected function parseFields(string $modelName): array
    {
        $fieldsOption = $this->option('fields');
        
        // For new models, we need explicit fields or interactive input
        if (!$fieldsOption) {
            if ($this->isInteractive()) {
                $this->info("No --fields option provided. Let's define the model fields interactively.");
                $fields = $this->promptForFields();
            } else {
                $this->error("No fields defined for new model '{$modelName}'.");
                $this->line("Please provide fields using --fields option:");
                $this->line("  php artisan make:model+ {$modelName} --fields=\"name:string,email:string\"");
                $this->line("");
                $this->line("Or run interactively (without --quiet flag):");
                $this->line("  php artisan make:model+ {$modelName}");
                throw new \InvalidArgumentException("Fields are required when creating a new model. Use --fields option or run interactively.");
            }
        } else {
            $fields = $this->parseFieldsString($fieldsOption);
        }
        
        $this->validateFields($fields);
        
        if (!empty($fields)) {
            $this->displayDetectedFields($fields, $fieldsOption ? 'fields option' : 'interactive input');
        } else {
            $this->warn("No fields defined. Model will be created with basic structure only.");
        }
        
        return $fields;
    }

    /**
     * Get command options.
     */
    protected function getOptions(): array
    {
        return [
            'api' => $this->option('api'),
            'resource' => $this->option('resource') || $this->option('all'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Generate model.
     */
    protected function generateModel(string $name, array $fields, array $options): void
    {
        $generator = new ModelGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Model created');
    }

    /**
     * Generate migration.
     */
    protected function generateMigration(string $name, array $fields, array $options): void
    {
        $generator = new MigrationGenerator($this->files, $name, $fields, $options);
        $generator->generate();
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
     * Generate factory.
     */
    protected function generateFactory(string $name, array $fields, array $options): void
    {
        $generator = new FactoryGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Factory created');
    }

    /**
     * Generate seeder.
     */
    protected function generateSeeder(string $name, array $fields, array $options): void
    {
        $generator = new SeederGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Seeder created');
    }

    /**
     * Generate policy.
     */
    protected function generatePolicy(string $name, array $fields, array $options): void
    {
        $generator = new PolicyGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Policy created');
    }

    /**
     * Generate form requests.
     */
    protected function generateRequests(string $name, array $fields, array $options): void
    {
        $generator = new RequestGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Form requests created');
    }

    /**
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $name, array $options): void
    {
        $className = Str::studly(Str::singular($name));
        $tableName = Str::snake(Str::plural($name));
        
        $this->info('Generated files:');
        $this->line("  Model: app/Models/{$className}.php");
        
        if ($this->option('migration') || $this->option('all')) {
            $this->line("  Migration: database/migrations/create_{$tableName}_table.php");
        }
        
        if ($this->option('controller') || $this->option('resource') || $this->option('all')) {
            $controllerType = $this->option('api') ? 'API' : 'Web';
            $this->line("  Controller: app/Http/Controllers/{$className}Controller.php ({$controllerType})");
        }
        
        if ($this->option('factory') || $this->option('all')) {
            $this->line("  Factory: database/factories/{$className}Factory.php");
        }
        
        if ($this->option('seed') || $this->option('all')) {
            $this->line("  Seeder: database/seeders/{$className}Seeder.php");
        }
        
        if ($this->option('policy') || $this->option('all')) {
            $this->line("  Policy: app/Policies/{$className}Policy.php");
        }
        
        if ($this->option('requests') || $this->option('all')) {
            $this->line("  Store Request: app/Http/Requests/Store{$className}Request.php");
            $this->line("  Update Request: app/Http/Requests/Update{$className}Request.php");
        }

        $this->newLine();
        $this->info('Next steps:');
        if ($this->option('migration') || $this->option('all')) {
            $this->line('1. Run: php artisan migrate');
        }
        if ($this->option('controller') || $this->option('resource') || $this->option('all')) {
            $this->line('2. Add routes to your routes file');
        }
        $this->line('3. Update the generated files as needed');
    }
}
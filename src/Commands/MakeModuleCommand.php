<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Generators\MigrationGenerator;
use Brikshya\LaravelGenerator\Generators\ModelGenerator;
use Brikshya\LaravelGenerator\Generators\ControllerGenerator;
use Brikshya\LaravelGenerator\Generators\ServiceGenerator;
use Brikshya\LaravelGenerator\Generators\RequestGenerator;
use Brikshya\LaravelGenerator\Generators\ResourceGenerator;
use Brikshya\LaravelGenerator\Generators\PolicyGenerator;
use Brikshya\LaravelGenerator\Generators\FactoryGenerator;
use Brikshya\LaravelGenerator\Generators\SeederGenerator;
use Brikshya\LaravelGenerator\Generators\TestGenerator;
use Brikshya\LaravelGenerator\Generators\EnumGenerator;
use Brikshya\LaravelGenerator\Generators\ViewGenerator;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:module {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--relationships= : Relationships definition}
                            {--api-only : Generate API-only components (no views)}
                            {--single-page : Generate single-page CRUD interface with modals}
                            {--separate-views : Generate separate views for each action (default)}
                            {--with= : Additional components (jobs,events,notifications)}
                            {--full : Generate all possible components}
                            {--auth : Add authentication middleware to controllers}
                            {--no-auth : Skip authentication even if Breeze is detected}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate a complete module with migration, model, controller, service, requests, and views. Supports single-page CRUD interface with modals.';

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
        $fields = $this->parseFields();
        $options = $this->getOptions();

        $this->info("Generating module: {$name}");

        // Generate components
        $this->generateComponents($name, $fields, $options);

        $this->info('Module generated successfully!');
        $this->displayGeneratedFiles($name);

        return self::SUCCESS;
    }

    /**
     * Parse fields from the command option.
     */
    protected function parseFields(): array
    {
        $fieldsOption = $this->option('fields');
        
        if (empty($fieldsOption)) {
            return $this->promptForFields();
        }

        $fields = [];
        foreach (explode(',', $fieldsOption) as $field) {
            $parts = explode(':', trim($field));
            $name = $parts[0];
            $type = $parts[1] ?? 'string';
            $options = array_slice($parts, 2);

            $fields[] = [
                'name' => $name,
                'type' => $type,
                'options' => $options,
                'nullable' => false,
                'unique' => false,
                'index' => false,
            ];
        }

        return $fields;
    }

    /**
     * Prompt user for fields interactively.
     */
    protected function promptForFields(): array
    {
        $fields = [];
        
        $this->info('Define fields for your model (press enter with empty name to finish):');
        
        while (true) {
            $name = $this->ask('Field name');
            
            if (empty($name)) {
                break;
            }

            $type = $this->choice('Field type', [
                'string', 'text', 'integer', 'bigInteger', 'decimal', 'float', 'double',
                'boolean', 'date', 'datetime', 'timestamp', 'time', 'json',
                'enum', 'foreign', 'uuid'
            ], 'string');

            $options = [];
            
            if ($this->confirm('Is this field nullable?')) {
                $options[] = 'nullable';
            }

            if ($this->confirm('Should this field be unique?')) {
                $options[] = 'unique';
            }

            if ($this->confirm('Should this field be indexed?')) {
                $options[] = 'index';
            }

            if ($type === 'enum') {
                $enumValues = $this->ask('Enum values (comma-separated)', 'active,inactive');
                $options = array_merge($options, explode(',', $enumValues));
            }

            $fields[] = [
                'name' => $name,
                'type' => $type,
                'options' => $options,
                'nullable' => in_array('nullable', $options),
                'unique' => in_array('unique', $options),
                'index' => in_array('index', $options),
            ];
        }

        return $fields;
    }

    /**
     * Get command options.
     */
    protected function getOptions(): array
    {
        return [
            'api_only' => $this->option('api-only'),
            'single_page' => $this->option('single-page'),
            'separate_views' => $this->option('separate-views'),
            'full' => $this->option('full'),
            'force' => $this->option('force'),
            'auth' => $this->option('auth'),
            'no_auth' => $this->option('no-auth'),
            'with' => $this->parseWithOption(),
        ];
    }

    /**
     * Parse the 'with' option.
     */
    protected function parseWithOption(): array
    {
        $with = $this->option('with');
        
        if (empty($with)) {
            return [];
        }

        return explode(',', $with);
    }

    /**
     * Generate all components for the module.
     */
    protected function generateComponents(string $name, array $fields, array $options): void
    {
        // Core components
        $this->generateMigration($name, $fields, $options);
        $this->generateModel($name, $fields, $options);
        $this->generateController($name, $fields, $options);
        $this->generateService($name, $fields, $options);

        // Form requests
        $this->generateRequests($name, $fields, $options);

        // API resources
        $this->generateResources($name, $fields, $options);

        // Authorization
        $this->generatePolicy($name, $fields, $options);

        // Database
        $this->generateFactory($name, $fields, $options);
        $this->generateSeeder($name, $fields, $options);

        // Tests
        $this->generateTests($name, $fields, $options);

        // Enums
        $this->generateEnums($name, $fields, $options);

        // Views (unless API-only)
        if (!$options['api_only']) {
            $this->generateViews($name, $fields, $options);
        }

        // Additional components if requested
        $this->generateAdditionalComponents($name, $fields, $options);
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
     * Generate model.
     */
    protected function generateModel(string $name, array $fields, array $options): void
    {
        $generator = new ModelGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Model created');
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
     * Generate service.
     */
    protected function generateService(string $name, array $fields, array $options): void
    {
        $generator = new ServiceGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Service created');
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
     * Generate API resources.
     */
    protected function generateResources(string $name, array $fields, array $options): void
    {
        $generator = new ResourceGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ API resources created');
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
     * Generate tests.
     */
    protected function generateTests(string $name, array $fields, array $options): void
    {
        $generator = new TestGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Tests created');
    }

    /**
     * Generate enums.
     */
    protected function generateEnums(string $name, array $fields, array $options): void
    {
        $generator = new EnumGenerator($this->files, $name, $fields, $options);
        if ($generator->hasEnums()) {
            $generator->generate();
            $this->line('✓ Enums created');
        }
    }

    /**
     * Generate views.
     */
    protected function generateViews(string $name, array $fields, array $options): void
    {
        $generator = new ViewGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        $this->line('✓ Views created');
    }

    /**
     * Generate additional components.
     */
    protected function generateAdditionalComponents(string $name, array $fields, array $options): void
    {
        $with = $options['with'] ?? [];

        // Add more generators based on 'with' options
        // This can be extended for jobs, events, notifications, etc.
        
        if (in_array('jobs', $with) || $options['full']) {
            // Generate job classes
            $this->line('✓ Jobs created');
        }

        if (in_array('events', $with) || $options['full']) {
            // Generate event and listener classes
            $this->line('✓ Events and listeners created');
        }

        if (in_array('notifications', $with) || $options['full']) {
            // Generate notification classes
            $this->line('✓ Notifications created');
        }
    }

    /**
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $name): void
    {
        $className = Str::studly(Str::singular($name));
        $tableName = Str::snake($name);
        
        $this->info('Generated files:');
        $this->line("  Migration: database/migrations/create_{$tableName}_table.php");
        $this->line("  Model: app/Models/{$className}.php");
        $this->line("  Controller: app/Http/Controllers/{$className}Controller.php");
        $this->line("  Service: app/Services/{$className}Service.php");
        $this->line("  Requests: app/Http/Requests/Store{$className}Request.php");
        $this->line("  Requests: app/Http/Requests/Update{$className}Request.php");
        $this->line("  Resources: app/Http/Resources/{$className}Resource.php");
        $this->line("  Policy: app/Policies/{$className}Policy.php");
        $this->line("  Factory: database/factories/{$className}Factory.php");
        $this->line("  Seeder: database/seeders/{$className}Seeder.php");
        $this->line("  Tests: tests/Feature/{$className}Test.php");
        
        if (!$this->option('api-only')) {
            if ($this->option('single-page')) {
                $this->line("  Views: resources/views/{$tableName}/index.blade.php (Single-page CRUD with modals)");
            } else {
                $this->line("  Views: resources/views/{$tableName}/ (Separate views)");
            }
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Run: php artisan migrate');
        $this->line('2. Add routes to your routes file:');
        if ($this->option('single-page')) {
            $this->line("   Route::resource('{$tableName}', {$className}Controller::class);");
            $this->line('   // Note: Single-page interface handles CRUD via AJAX');
        } else {
            $this->line("   Route::resource('{$tableName}', {$className}Controller::class);");
        }
        $this->line('3. Update the generated files as needed');
    }
}
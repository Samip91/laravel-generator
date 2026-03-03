<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Traits\HandlesFields;
use Brikshya\LaravelGenerator\Generators\RequestGenerator;

class MakeRequestPlusCommand extends Command
{
    use HandlesFields;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:request+ {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--model= : Model to use for auto-detection (defaults to name)}
                            {--store : Generate only store request}
                            {--update : Generate only update request}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate form request classes with auto-detected fields from model and intelligent validation rules.';

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

        $this->info("Generating form request(s) for: {$name}");

        $this->generateRequests($name, $fields, $options);

        $this->info('Form requests generated successfully!');
        $this->displayGeneratedFiles($name, $options);

        return self::SUCCESS;
    }

    /**
     * Parse fields from the command option or auto-detect from model.
     */
    protected function parseFields(string $modelName): array
    {
        $fieldsOption = $this->option('fields');
        
        $fields = $this->getFieldDefinitions($modelName, $fieldsOption);
        
        if (!empty($fields)) {
            $this->displayDetectedFields($fields, $fieldsOption ? 'fields option' : 'model analysis');
        }
        
        return $fields;
    }

    /**
     * Get command options.
     */
    protected function getOptions(): array
    {
        return [
            'store_only' => $this->option('store'),
            'update_only' => $this->option('update'),
            'force' => $this->option('force'),
        ];
    }

    /**
     * Generate form requests.
     */
    protected function generateRequests(string $name, array $fields, array $options): void
    {
        $generator = new RequestGenerator($this->files, $name, $fields, $options);
        $generator->generate();
        
        if ($options['store_only']) {
            $this->line('✓ Store request created');
        } elseif ($options['update_only']) {
            $this->line('✓ Update request created');
        } else {
            $this->line('✓ Store and Update requests created');
        }
    }

    /**
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $name, array $options): void
    {
        $className = Str::studly(Str::singular($name));
        
        $this->info('Generated files:');
        
        if ($options['store_only']) {
            $this->line("  Store Request: app/Http/Requests/Store{$className}Request.php");
        } elseif ($options['update_only']) {
            $this->line("  Update Request: app/Http/Requests/Update{$className}Request.php");
        } else {
            $this->line("  Store Request: app/Http/Requests/Store{$className}Request.php");
            $this->line("  Update Request: app/Http/Requests/Update{$className}Request.php");
        }

        $this->newLine();
        $this->info('Generated features:');
        $this->line('• Auto-generated validation rules based on field types');
        $this->line('• Unique validation handling for updates');
        $this->line('• Custom error messages for better UX');
        $this->line('• Authorization logic placeholder');
        
        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Review and customize validation rules');
        $this->line('2. Implement authorization logic if needed');
        $this->line('3. Add custom validation messages');
        $this->line('4. Use the requests in your controller methods');
    }
}
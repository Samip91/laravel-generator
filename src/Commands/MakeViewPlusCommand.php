<?php

namespace Brikshya\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Traits\HandlesFields;
use Brikshya\LaravelGenerator\Generators\ViewGenerator;

class MakeViewPlusCommand extends Command
{
    use HandlesFields;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:view+ {name} 
                            {--fields= : Fields definition (e.g., title:string,content:text,status:enum:draft,published)}
                            {--model= : Model to use for auto-detection (defaults to name)}
                            {--single-page : Generate single-page CRUD interface with modals}
                            {--separate-views : Generate separate views for each action (default)}
                            {--api-only : Skip view generation (contradiction check)}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     */
    protected $description = 'Generate views with auto-detected fields from model. Supports single-page CRUD interface with modals.';

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
        if ($this->option('api-only')) {
            $this->error('Cannot generate views with --api-only option. Views are for web interfaces.');
            return self::FAILURE;
        }

        $name = $this->argument('name');
        $modelName = $this->option('model') ?: $name;
        
        $fields = $this->parseFields($modelName);
        $options = $this->getOptions();

        $this->info("Generating views for: {$name}");

        $this->generateViews($name, $fields, $options);

        $this->info('Views generated successfully!');
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
            'single_page' => $this->option('single-page'),
            'separate_views' => $this->option('separate-views'),
            'force' => $this->option('force'),
        ];
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
     * Display the generated files.
     */
    protected function displayGeneratedFiles(string $name, array $options): void
    {
        $tableName = Str::snake($name);
        
        $this->info('Generated files:');
        
        if ($this->option('single-page')) {
            $this->line("  Views: resources/views/{$tableName}/index.blade.php (Single-page CRUD with modals)");
            $this->line("  Partials: resources/views/{$tableName}/_form.blade.php");
            $this->line("  Assets: public/js/modal-dialogs.js");
            $this->line("  Assets: public/css/modal-dialogs.css");
        } else {
            $this->line("  Views: resources/views/{$tableName}/ (Separate views)");
            $this->line("    ├── index.blade.php");
            $this->line("    ├── create.blade.php");
            $this->line("    ├── edit.blade.php");
            $this->line("    ├── show.blade.php");
            $this->line("    └── _form.blade.php");
        }

        $this->newLine();
        $this->info('Next steps:');
        $this->line('1. Add routes to match the generated views');
        if ($this->option('single-page')) {
            $this->line('2. Ensure AJAX endpoints are configured in your controller');
            $this->line('3. Include modal dialog assets in your layout');
        } else {
            $this->line('2. Update the views to match your design system');
        }
        $this->line('3. Customize the generated views as needed');
    }
}
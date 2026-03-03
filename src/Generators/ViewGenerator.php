<?php

namespace Brikshya\LaravelGenerator\Generators;

use Illuminate\Support\Str;
use Brikshya\LaravelGenerator\Services\UIFrameworkDetector;
use Brikshya\LaravelGenerator\Services\AlertService;
use Brikshya\LaravelGenerator\Services\CrudJavaScriptService;

class ViewGenerator extends BaseGenerator
{
    protected UIFrameworkDetector $detector;
    protected array $framework;

    public function __construct($files, $name, $fields = [], $options = [])
    {
        parent::__construct($files, $name, $fields, $options);
        
        $this->detector = new UIFrameworkDetector($files);
        $this->framework = $this->detector->detect();
    }

    /**
     * Generate all view files.
     */
    public function generate(): bool
    {
        // Check if single-page option is enabled
        if ($this->options['single_page'] ?? false) {
            $this->generateSinglePageView();
        } else {
            $this->generateIndexView();
            $this->generateCreateView();
            $this->generateEditView();
            $this->generateShowView();
            $this->generateFormView();
        }
        
        return true;
    }

    /**
     * Generate index view.
     */
    protected function generateIndexView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/index.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getIndexStub());
        $content = $this->replaceVariables($stub, $this->getIndexVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate create view.
     */
    protected function generateCreateView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/create.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getCreateStub());
        $content = $this->replaceVariables($stub, $this->getCreateVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate edit view.
     */
    protected function generateEditView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/edit.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getEditStub());
        $content = $this->replaceVariables($stub, $this->getEditVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate show view.
     */
    protected function generateShowView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/show.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getShowStub());
        $content = $this->replaceVariables($stub, $this->getShowVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate form partial view.
     */
    protected function generateFormView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/_form.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getFormStub());
        $content = $this->replaceVariables($stub, $this->getFormVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Generate single-page CRUD interface.
     */
    protected function generateSinglePageView(): void
    {
        $path = resource_path('views/'.$this->getKebabName().'/index.blade.php');
        $this->makeDirectory($path);
        
        $stub = $this->files->get($this->getSinglePageStub());
        $content = $this->replaceVariables($stub, $this->getSinglePageVariables());
        
        $this->files->put($path, $content);
    }

    /**
     * Get single-page stub based on framework.
     */
    protected function getSinglePageStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.single-page{$suffix}.stub";
    }

    /**
     * Get variables for single-page view.
     */
    protected function getSinglePageVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ tableHeaders }}'] = $this->buildTableHeaders();
        $variables['{{ tableRows }}'] = $this->buildTableRows();
        $variables['{{ formFields }}'] = $this->buildFormFields();
        $variables['{{ alerts }}'] = AlertService::generateAlert($this->framework['name']);
        $variables['{{ dialogCSS }}'] = AlertService::generateDialogCSS();
        $variables['{{ alertScript }}'] = AlertService::generateAlertJavaScript();
        $variables['{{ crudScript }}'] = CrudJavaScriptService::generateCrudScript($this->name, $this->parseFields());
        
        return $variables;
    }

    /**
     * Get stub file paths.
     */
    public function getStub(): string
    {
        return $this->getIndexStub();
    }

    protected function getIndexStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.index{$suffix}.stub";
    }

    protected function getCreateStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.create{$suffix}.stub";
    }

    protected function getEditStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.edit{$suffix}.stub";
    }

    protected function getShowStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.show{$suffix}.stub";
    }

    protected function getFormStub(): string
    {
        $suffix = $this->getFrameworkSuffix();
        return __DIR__."/../Stubs/view.form{$suffix}.stub";
    }

    /**
     * Get framework suffix for stub selection.
     */
    protected function getFrameworkSuffix(): string
    {
        return $this->framework['name'] === 'breeze' ? '.breeze' : '';
    }

    /**
     * Get the destination path for the generated file.
     */
    public function getPath(): string
    {
        return resource_path('views/'.$this->getKebabName().'/');
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getNamespace(): string
    {
        return '';
    }

    /**
     * Get variables for index view.
     */
    protected function getIndexVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ tableHeaders }}'] = $this->buildTableHeaders();
        $variables['{{ tableRows }}'] = $this->buildTableRows();
        
        return $variables;
    }

    /**
     * Get variables for create view.
     */
    protected function getCreateVariables(): array
    {
        return parent::getVariables();
    }

    /**
     * Get variables for edit view.
     */
    protected function getEditVariables(): array
    {
        return parent::getVariables();
    }

    /**
     * Get variables for show view.
     */
    protected function getShowVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ showFields }}'] = $this->buildShowFields();
        
        return $variables;
    }

    /**
     * Get variables for form view.
     */
    protected function getFormVariables(): array
    {
        $variables = parent::getVariables();
        $variables['{{ formFields }}'] = $this->buildFormFields();
        
        return $variables;
    }

    /**
     * Build table headers for index view.
     */
    protected function buildTableHeaders(): string
    {
        $fields = $this->parseFields();
        $headers = ['<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>'];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $label = Str::title(str_replace('_', ' ', $field['name']));
                $headers[] = "<th class=\"px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">{$label}</th>";
            }
        }

        $headers[] = '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>';
        $headers[] = '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';

        return implode("\n                ", $headers);
    }

    /**
     * Build table rows for index view.
     */
    protected function buildTableRows(): string
    {
        $fields = $this->parseFields();
        $rows = ['<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $'.$this->getCamelName().'->id }}</td>'];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                if ($field['type'] === 'enum') {
                    $rows[] = '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $'.$this->getCamelName().'->'.$field['name'].'?->label() ?? \'N/A\' }}</td>';
                } elseif ($field['type'] === 'foreign' || Str::endsWith($field['name'], '_id')) {
                    // Foreign key field - show related model name
                    $relationName = Str::beforeLast($field['name'], '_id');
                    $rows[] = '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $'.$this->getCamelName().'->'.$relationName.'->name ?? $'.$this->getCamelName().'->'.$relationName.'->title ?? $'.$this->getCamelName().'->'.$field['name'].' ?? \'N/A\' }}</td>';
                } else {
                    $rows[] = '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $'.$this->getCamelName().'->'.$field['name'].' }}</td>';
                }
            }
        }

        $rows[] = '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $'.$this->getCamelName().'->created_at->format(\'Y-m-d H:i\') }}</td>';

        return implode("\n                ", $rows);
    }

    /**
     * Build show fields for show view.
     */
    protected function buildShowFields(): string
    {
        $fields = $this->parseFields();
        $showFields = [];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['created_at', 'updated_at'])) {
                $label = Str::title(str_replace('_', ' ', $field['name']));
                
                if ($field['type'] === 'enum') {
                    $showFields[] = '<div class="mb-4"><label class="block text-sm font-medium text-gray-700">'.$label.'</label><p class="text-gray-900">{{ $'.$this->getCamelName().'->'.$field['name'].'?->label() ?? \'N/A\' }}</p></div>';
                } else {
                    $showFields[] = '<div class="mb-4"><label class="block text-sm font-medium text-gray-700">'.$label.'</label><p class="text-gray-900">{{ $'.$this->getCamelName().'->'.$field['name'].' }}</p></div>';
                }
            }
        }

        return implode("\n        ", $showFields);
    }

    /**
     * Build form fields for form view.
     */
    protected function buildFormFields(): string
    {
        $fields = $this->parseFields();
        $formFields = [];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['id', 'created_at', 'updated_at'])) {
                $formFields[] = $this->buildFormField($field);
            }
        }

        return implode("\n\n    ", $formFields);
    }

    /**
     * Build a single form field.
     */
    protected function buildFormField(array $field): string
    {
        $label = Str::title(str_replace('_', ' ', $field['name']));
        $name = $field['name'];
        $oldValue = "old('{$name}', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name} : '')";
        $required = $field['nullable'] ? '' : ' required';

        // Detect foreign key fields
        if ($field['type'] === 'foreign' || Str::endsWith($field['name'], '_id')) {
            $field['type'] = 'foreign';
        }

        if ($this->framework['name'] === 'breeze') {
            return $this->buildBreezeFormField($field, $label, $name, $oldValue, $required);
        }

        return match($field['type']) {
            'text' => '<div class="mb-4">
    <label for="'.$name.'" class="block text-sm font-medium text-gray-700">'.$label.'</label>
    <textarea id="'.$name.'" name="'.$name.'" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"'.$required.'>{{ '.$oldValue.' }}</textarea>
    @error(\''.$name.'\')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>',
            
            'boolean' => '<div class="mb-4">
    <div class="flex items-center">
        <input type="checkbox" id="'.$name.'" name="'.$name.'" value="1" class="rounded border-gray-300 shadow-sm" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name} : false) ? \'checked\' : \'\' }}>
        <label for="'.$name.'" class="ml-2 block text-sm text-gray-900">'.$label.'</label>
    </div>
    @error(\''.$name.'\')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>',

            'enum' => '<div class="mb-4">
    <label for="'.$name.'" class="block text-sm font-medium text-gray-700">'.$label.'</label>
    <select id="'.$name.'" name="'.$name.'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"'.$required.'>
        <option value="">Select '.$label.'</option>
        @foreach(\\App\\Enums\\'.Str::studly($name).'Enum::cases() as $case)
            <option value="{{ $case->value }}" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name}?->value : null) === $case->value ? \'selected\' : \'\' }}>
                {{ $case->label() }}
            </option>
        @endforeach
    </select>
    @error(\''.$name.'\')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>',

            'foreign' => '<div class="mb-4">
    <label for="'.$name.'" class="block text-sm font-medium text-gray-700">'.$label.'</label>
    <select id="'.$name.'" name="'.$name.'" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"'.$required.'>
        <option value="">Select '.$label.'</option>
        @foreach(${{ '.Str::camel(Str::plural(Str::beforeLast($name, '_id'))).' }} as $item)
            <option value="{{ $item->id }}" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name} : null) == $item->id ? \'selected\' : \'\' }}>
                {{ $item->name ?? $item->title ?? $item->id }}
            </option>
        @endforeach
    </select>
    @error(\''.$name.'\')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>',

            default => '<div class="mb-4">
    <label for="'.$name.'" class="block text-sm font-medium text-gray-700">'.$label.'</label>
    <input type="text" id="'.$name.'" name="'.$name.'" value="{{ '.$oldValue.' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"'.$required.'>
    @error(\''.$name.'\')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>'
        };
    }

    /**
     * Build Breeze-compatible form field.
     */
    protected function buildBreezeFormField(array $field, string $label, string $name, string $oldValue, string $required): string
    {
        return match($field['type']) {
            'text' => '<div>
    <x-input-label for="'.$name.'" :value="__(\''.addslashes($label).'\')" />
    <textarea id="'.$name.'" name="'.$name.'" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"'.$required.'>{{ '.$oldValue.' }}</textarea>
    <x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />
</div>',
            
            'boolean' => '<div>
    <label for="'.$name.'" class="inline-flex items-center">
        <input type="checkbox" id="'.$name.'" name="'.$name.'" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name} : false) ? \'checked\' : \'\' }}>
        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __(\''.addslashes($label).'\') }}</span>
    </label>
    <x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />
</div>',

            'enum' => '<div>
    <x-input-label for="'.$name.'" :value="__(\''.addslashes($label).'\')" />
    <select id="'.$name.'" name="'.$name.'" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"'.$required.'>
        <option value="">{{ __(\'Select '.addslashes($label).'\') }}</option>
        @foreach(\\App\\Enums\\'.Str::studly($name).'Enum::cases() as $case)
            <option value="{{ $case->value }}" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name}?->value : null) === $case->value ? \'selected\' : \'\' }}>
                {{ $case->label() }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />
</div>',

            'foreign' => '<div>
    <x-input-label for="'.$name.'" :value="__(\''.addslashes($label).'\')" />
    <select id="'.$name.'" name="'.$name.'" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"'.$required.'>
        <option value="">{{ __(\'Select '.addslashes($label).'\') }}</option>
        @foreach(${{ '.Str::camel(Str::plural(Str::beforeLast($name, '_id'))).' }} as $item)
            <option value="{{ $item->id }}" {{ old(\''.$name.'\', isset(\${{ modelVariable }}) ? \${{ modelVariable }}->{$name} : null) == $item->id ? \'selected\' : \'\' }}>
                {{ $item->name ?? $item->title ?? $item->id }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />
</div>',

            default => '<div>
    <x-input-label for="'.$name.'" :value="__(\''.addslashes($label).'\')" />
    <x-text-input id="'.$name.'" name="'.$name.'" type="text" class="mt-1 block w-full" :value="'.$oldValue.'"'.$required.' />
    <x-input-error :messages="$errors->get(\''.$name.'\')" class="mt-2" />
</div>'
        };
    }

    /**
     * Build show fields for Breeze show view.
     */
    protected function buildBreezeShowFields(): string
    {
        $fields = $this->parseFields();
        $showFields = [];

        foreach ($fields as $field) {
            if (!in_array($field['name'], ['created_at', 'updated_at'])) {
                $label = Str::title(str_replace('_', ' ', $field['name']));
                
                if ($field['type'] === 'enum') {
                    $showFields[] = '<div>
    <x-input-label :value="__(\''.addslashes($label).'\')" />
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $'.$this->getCamelName().'->'.$field['name'].'?->label() ?? __(\'N/A\') }}
    </p>
</div>';
                } else {
                    $showFields[] = '<div>
    <x-input-label :value="__(\''.addslashes($label).'\')" />
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ $'.$this->getCamelName().'->'.$field['name'].' ?? __(\'N/A\') }}
    </p>
</div>';
                }
            }
        }

        return implode("\n                        ", $showFields);
    }

}
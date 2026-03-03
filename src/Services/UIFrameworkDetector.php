<?php

namespace Brikshya\LaravelGenerator\Services;

use Illuminate\Filesystem\Filesystem;

class UIFrameworkDetector
{
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Detect the UI framework being used.
     */
    public function detect(): array
    {
        $framework = [
            'name' => 'tailwind',
            'layout' => 'app',
            'use_components' => false,
            'has_authentication' => false,
        ];

        // Check for Breeze
        if ($this->hasBreezeComponents()) {
            $framework = [
                'name' => 'breeze',
                'layout' => 'app',
                'use_components' => true,
                'has_authentication' => true,
                'components' => $this->getBreezeComponents(),
            ];
        }
        // Check for Jetstream
        elseif ($this->hasJetstreamComponents()) {
            $framework = [
                'name' => 'jetstream',
                'layout' => 'app',
                'use_components' => true,
                'has_authentication' => true,
            ];
        }
        // Check for Bootstrap
        elseif ($this->hasBootstrap()) {
            $framework = [
                'name' => 'bootstrap',
                'layout' => 'app',
                'use_components' => false,
                'has_authentication' => false,
            ];
        }

        return $framework;
    }

    /**
     * Check if Breeze components exist.
     */
    protected function hasBreezeComponents(): bool
    {
        $breezeComponents = [
            'layouts/app.blade.php',
            'components/primary-button.blade.php',
            'components/text-input.blade.php',
            'components/input-label.blade.php',
        ];

        foreach ($breezeComponents as $component) {
            if (!$this->files->exists(resource_path('views/' . $component))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if Jetstream components exist.
     */
    protected function hasJetstreamComponents(): bool
    {
        return $this->files->exists(resource_path('views/layouts/app.blade.php')) &&
               $this->files->exists(resource_path('views/navigation-menu.blade.php'));
    }

    /**
     * Check if Bootstrap is being used.
     */
    protected function hasBootstrap(): bool
    {
        $packageJson = base_path('package.json');
        
        if (!$this->files->exists($packageJson)) {
            return false;
        }

        $content = $this->files->get($packageJson);
        return str_contains($content, 'bootstrap');
    }

    /**
     * Get available Breeze components.
     */
    protected function getBreezeComponents(): array
    {
        $componentsPath = resource_path('views/components');
        
        if (!$this->files->exists($componentsPath)) {
            return [];
        }

        $components = [];
        $files = $this->files->files($componentsPath);

        foreach ($files as $file) {
            $name = str_replace('.blade.php', '', $file->getFilename());
            $components[$name] = 'components.' . $name;
        }

        return $components;
    }

    /**
     * Check if authentication is available.
     */
    public function hasAuthentication(): bool
    {
        return $this->files->exists(resource_path('views/auth')) ||
               $this->files->exists(app_path('Http/Controllers/Auth'));
    }

    /**
     * Get the appropriate layout name.
     */
    public function getLayout(): string
    {
        if ($this->files->exists(resource_path('views/layouts/app.blade.php'))) {
            return 'layouts.app';
        }

        if ($this->files->exists(resource_path('views/app.blade.php'))) {
            return 'app';
        }

        return 'layouts.app'; // Default assumption
    }

    /**
     * Get authentication middleware.
     */
    public function getAuthMiddleware(): ?string
    {
        return $this->hasAuthentication() ? 'auth' : null;
    }
}
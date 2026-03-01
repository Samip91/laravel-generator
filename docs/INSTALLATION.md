# Installation Guide

This guide will walk you through installing and setting up the Laravel Comprehensive Generator package.

## Requirements

- PHP 8.2 or higher
- Laravel 11.0 or higher
- Composer

## Installation Methods

### Method 1: Composer Install (Recommended)

```bash
composer require yourname/laravel-comprehensive-generator
```

### Method 2: Local Development

For local development or customization:

```bash
# Clone the repository
git clone https://github.com/yourname/laravel-comprehensive-generator.git packages/laravel-generator

# Add to composer.json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/laravel-generator"
        }
    ],
    "require": {
        "yourname/laravel-comprehensive-generator": "*"
    }
}

# Install
composer install
```

## Service Provider Registration

The package uses Laravel's auto-discovery feature, so the service provider is registered automatically. If you need to register it manually:

```php
// config/app.php
'providers' => [
    // Other Service Providers...
    YourName\LaravelGenerator\LaravelGeneratorServiceProvider::class,
],
```

## Configuration (Optional)

Publish the configuration file to customize default settings:

```bash
php artisan vendor:publish --tag=laravel-generator-config
```

This creates `config/laravel-generator.php` with customizable options.

## Stub Templates (Optional)

Publish stub templates for customization:

```bash
php artisan vendor:publish --tag=laravel-generator-stubs
```

Templates are published to `resources/stubs/laravel-generator/`

## Directory Setup

The package automatically creates necessary directories:

- `app/Services/` - For service classes
- `app/Enums/` - For enum classes (if not exists)

## Verify Installation

Test the installation:

```bash
php artisan make:module --help
```

You should see the command help output.

## Quick Test

Generate a test module:

```bash
php artisan make:module TestPost --fields="title:string,content:text"
```

This should generate all the necessary files without errors.

## Integration with Existing Project

### 1. Update Main Composer

If using the local development method, update your main project's composer.json:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "YourName\\LaravelGenerator\\": "packages/laravel-generator/src/"
        }
    }
}
```

Then run:
```bash
composer dump-autoload
```

### 2. Route Registration

After generating modules, add routes to your route files:

```php
// routes/web.php
Route::resource('posts', App\Http\Controllers\PostController::class);

// routes/api.php  
Route::apiResource('posts', App\Http\Controllers\PostController::class);
```

### 3. Database Migration

Run migrations for generated modules:

```bash
php artisan migrate
```

### 4. Seed Database (Optional)

If you generated seeders:

```bash
php artisan db:seed --class=PostSeeder
```

## Troubleshooting Installation

### Common Issues

1. **Composer Install Fails**
   ```bash
   # Clear composer cache
   composer clear-cache
   
   # Try again
   composer install
   ```

2. **Service Provider Not Found**
   ```bash
   # Clear application cache
   php artisan config:clear
   php artisan cache:clear
   
   # Dump autoload
   composer dump-autoload
   ```

3. **Command Not Found**
   ```bash
   # Check if service provider is registered
   php artisan list | grep make:module
   
   # If not found, manually register the service provider
   ```

4. **Permission Issues**
   ```bash
   # Fix directory permissions
   chmod -R 755 app/
   chmod -R 755 database/
   chmod -R 755 resources/
   ```

### Laravel Version Compatibility

| Laravel Version | Package Version | PHP Version |
|----------------|-----------------|-------------|
| 11.x | 1.0.x | 8.2+ |
| 12.x | 1.1.x | 8.2+ |

## Development Installation

For contributing to the package:

```bash
# Clone and install development dependencies
git clone https://github.com/yourname/laravel-comprehensive-generator.git
cd laravel-comprehensive-generator
composer install --dev

# Run tests
composer test

# Run code style check
composer pint
```

## Next Steps

1. Read the [Usage Guide](USAGE.md)
2. Check the [Configuration Options](CONFIGURATION.md)
3. See [Examples](EXAMPLES.md)
4. Review [Best Practices](BEST_PRACTICES.md)

You're now ready to generate comprehensive Laravel modules!
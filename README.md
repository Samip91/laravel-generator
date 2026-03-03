# Laravel Comprehensive Generator

A powerful Laravel package that generates complete CRUD modules with migrations, models, controllers, services, requests, resources, policies, factories, seeders, tests, enums, and views with **beautiful modal dialogs** in a single command.

[![Latest Version](https://img.shields.io/badge/version-1.0.0-blue)](https://github.com/Samip91/laravel-generator)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Laravel](https://img.shields.io/badge/laravel-11%2B-red)](https://laravel.com)
[![Package](https://img.shields.io/badge/package-brikshya%2Flaravel--generator-orange)](https://packagist.org/packages/brikshya/laravel-generator)

## ✨ What Makes This Special

This package goes beyond basic CRUD generation by providing **beautiful, responsive modal dialogs** with smooth animations - similar to SweetAlert but **without external dependencies**. Generate modern, professional interfaces that users love!

## 🚀 Features

- **🎨 Beautiful Modal Dialogs**: Success, error, warning, confirmation, and input dialogs with animations
- **📱 Single-Page CRUD Interface**: Modern modal-based forms with AJAX operations
- **🔧 Complete Module Generation**: Generate all components for a feature in one command
- **🎯 Smart Field Auto-Detection**: Automatically detect fields from existing models - no more manual --fields!
- **⚡ Individual Component Generators**: Laravel-style commands with flags (-m, -c, -r, etc.)
- **🔗 Relationship Detection**: Auto-generate relationships from foreign key fields
- **📊 Enum Support**: Generate and integrate enum classes for status/type fields
- **⚙️ Service Layer**: Implements service pattern for business logic separation
- **🌐 API Ready**: Generate API resources and controllers
- **🧪 Comprehensive Testing**: Generate feature and unit tests
- **🎨 UI Framework Support**: Auto-detects Breeze, Tailwind CSS, Bootstrap
- **🌙 Dark Mode**: All components support dark mode
- **📝 Customizable Templates**: Publish and customize stub templates
- **💬 Interactive Mode**: Prompt-driven field definition

## 📦 Installation

Install the package via Composer:

```bash
composer require brikshya/laravel-generator
```

Publish the configuration file (optional):

```bash
php artisan vendor:publish --tag=laravel-generator-config
```

Publish stub templates for customization (optional):

```bash
php artisan vendor:publish --tag=laravel-generator-stubs
```

## 🎯 Quick Start

Generate a complete CRUD module:

```bash
php artisan make:module Post --fields="title:string,content:text,status:enum:draft,published,category_id:foreign"
```

This single command generates:
- Migration file with proper fields and relationships
- Eloquent model with relationships, casts, and scopes
- Resource controller with full CRUD operations
- Service class for business logic
- Form request classes (Store/Update) with validation rules
- API resource classes for JSON responses
- Policy class for authorization
- Factory class with realistic fake data
- Seeder class
- Feature tests
- Enum classes for status fields
- **Beautiful views with modal dialogs** (index, create, edit, show)

## 🎨 Modal Dialog Features

### Generate Single-Page Interface

```bash
php artisan make:module Product --single-page --fields="name:string,price:decimal,status:enum:active,inactive"
```

This creates a modern single-page interface with:
- **Modal-based forms** for create/edit operations
- **Beautiful confirmation dialogs** for delete actions
- **Success celebrations** with animated checkmarks
- **Error handling** with shake animations
- **Loading indicators** during AJAX operations
- **Responsive design** that works on all devices

### Dialog Types Available

```javascript
// Success with celebration animation
successDialog('Success!', 'Product saved successfully');

// Error with shake animation
errorDialog('Error!', 'Something went wrong');

// Confirmation with custom buttons
confirmDialog('Delete Product?', 'This action cannot be undone').then(result => {
    if (result.confirmed) {
        // User clicked Yes
    }
});

// Input prompts with validation
promptDialog('Enter Name', 'Product name').then(result => {
    if (result.confirmed) {
        console.log(result.value); // User input
    }
});
```

### Features
- ✅ **No External Dependencies**: Pure CSS and JavaScript
- ✅ **Framework Agnostic**: Works with Breeze, Tailwind, Bootstrap
- ✅ **Smooth Animations**: Fade, scale, bounce, shake effects
- ✅ **Keyboard Navigation**: Enter/Escape key support
- ✅ **Mobile Responsive**: Perfect on all screen sizes
- ✅ **Dark Mode Support**: Automatically adapts to theme
- ✅ **Queue System**: Handle multiple dialogs gracefully

## 📖 Usage Examples

### Smart Auto-Detection (Recommended)

The generators now **automatically detect fields** from existing models! No more manual --fields required:

```bash
# Step 1: Create model with fields first
php artisan make:model+ Product --fields="name:string,price:decimal,description:text" -m

# Step 2: Auto-detect from existing model for other components
php artisan make:controller+ ProductController  # Auto-detects from Product model
php artisan make:view+ products --model=Product  # Auto-detects from Product model
php artisan make:request+ Product               # Auto-detects from Product model

# Or generate model with multiple components at once
php artisan make:model+ User --fields="name:string,email:string,role:enum:admin,user" -mcr
```

### Individual Component Generators

Generate specific components with Laravel-style flags:

```bash
# Create new models with fields and related components
php artisan make:model+ User --fields="name:string,email:string" -mfRS   # Model + Migration + Factory + Requests + Service
php artisan make:model+ Post --fields="title:string,content:text,status:enum:draft,published" -a  # Everything

# Generate components from existing models (auto-detects fields)
php artisan make:controller+ ProductController --requests --resources
php artisan make:view+ products --single-page
php artisan make:resource+ Product --collection
php artisan make:request+ Post

# Override auto-detection with explicit fields
php artisan make:controller+ CustomController --fields="name:string,type:enum:basic,premium"
```

### Smart Service Layer Integration

Controllers are **automatically generated** based on service availability:

| Scenario | Controller Template | Behavior |
|----------|-------------------|----------|
| **Service exists** or `-S` flag used | With service injection | `$this->service->create()`, `$this->service->paginate()` |
| **No service** and no `-S` flag | Direct model usage | `Model::create()`, `Model::paginate()` |

```bash
# Creates controller with service injection (and creates service if needed)
php artisan make:controller+ ArticleController -S

# Creates controller with direct model usage (no service)
php artisan make:controller+ ArticleController

# If ArticleService already exists, controller will automatically use it
php artisan make:controller+ ArticleController  # Auto-detects existing service
```

### Complete Module Generation

```bash
# Full module with auto-detection
php artisan make:module Product

# With manual fields (override auto-detection)
php artisan make:module Product --fields="name:string,price:decimal,description:text"
```

### Basic Module

```bash
php artisan make:module Product --fields="name:string,price:decimal,description:text"
```

### With Relationships

```bash
php artisan make:module Order --fields="total:decimal,status:enum:pending,processing,shipped,delivered,user_id:foreign,shipping_address:text"
```

### API Only (No Views)

```bash
php artisan make:module User --api-only --fields="name:string,email:string,role:enum:admin,user"
```

### Interactive Mode

```bash
php artisan make:module Comment
# Follow the interactive prompts to define fields
```

### Full Generation (All Components)

```bash
php artisan make:module Task --full --fields="title:string,due_date:date,completed:boolean"
```

## 🔧 Field Types

### Supported Field Types

| Type | Migration | Cast | Validation |
|------|-----------|------|------------|
| `string` | `string()` | `string` | `string\|max:255` |
| `text` | `text()` | `string` | `string` |
| `integer` | `integer()` | `integer` | `integer` |
| `decimal` | `decimal(8,2)` | `decimal:2` | `numeric` |
| `boolean` | `boolean()` | `boolean` | `boolean` |
| `date` | `date()` | `date` | `date` |
| `datetime` | `dateTime()` | `datetime` | `date_format:Y-m-d H:i:s` |
| `json` | `json()` | `array` | `array` |
| `enum` | `enum()` | `EnumClass` | `in:value1,value2` |
| `foreign` | `foreignId()` | `integer` | `exists:table,id` |
| `uuid` | `uuid()` | `string` | `uuid` |

### Field Modifiers

Add modifiers to fields with additional options:

```bash
--fields="name:string:nullable,email:string:unique,age:integer:index"
```

Available modifiers:
- `nullable`: Field can be null
- `unique`: Field must be unique
- `index`: Add database index

## 🔗 Relationships

The generator automatically detects relationships from field names:

### Belongs To
```bash
--fields="user_id:foreign"
# Generates: belongsTo relationship to User model
```

### Custom Relationships
```bash
--relationships="user:belongsTo,comments:hasMany,tags:belongsToMany"
```

## 📋 Generated Components

### 1. Migration
- Database table with proper column types
- Foreign key constraints
- Indexes for performance
- Timestamps

### 2. Eloquent Model
- Mass assignable fields
- Proper type casting
- Relationships (belongsTo, hasMany, etc.)
- Query scopes for common operations
- Enum casting for status fields

### 3. Controller
- Full CRUD operations (index, create, store, show, edit, update, destroy)
- Service layer integration
- Form request validation
- Proper HTTP responses
- API version available

### 4. Service Class
- Business logic layer
- CRUD operations
- Search functionality
- Pagination support
- Repository pattern ready

### 5. Form Requests
- Store request with validation rules
- Update request with unique validation handling
- Custom error messages support
- Authorization logic

### 6. API Resources
- Resource class for single model transformation
- ResourceCollection for collections
- Customizable data transformation

### 7. Policy
- Authorization methods (view, create, update, delete)
- User-based permissions
- Laravel gate integration

### 8. Factory
- Realistic fake data generation
- Relationship handling
- Customizable data

### 9. Seeder
- Database seeding support
- Factory integration
- Configurable record counts

### 10. Tests
- Feature tests for all endpoints
- API testing support
- Database assertions
- Factory usage

### 11. Enums
- Type-safe enum classes
- Helper methods (labels, colors, etc.)
- Status checking methods
- Form option generation

### 12. Views (Optional)
- Index view with search and pagination
- Create/Edit forms with validation
- Show view for displaying records
- Responsive design with Tailwind CSS

## ⚙️ Configuration

### Default Configuration

The package comes with sensible defaults, but you can customize everything:

```php
// config/laravel-generator.php

return [
    'defaults' => [
        'generate_api_resources' => true,
        'generate_policies' => true,
        'generate_factories' => true,
        'generate_seeders' => true,
        'generate_tests' => true,
        'generate_views' => true,
    ],
    
    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'services' => 'app/Services',
        // ... more paths
    ],
    
    'namespaces' => [
        'models' => 'App\\Models',
        'controllers' => 'App\\Http\\Controllers',
        'services' => 'App\\Services',
        // ... more namespaces
    ],
];
```

### Custom Templates

Publish and customize stub templates:

```bash
php artisan vendor:publish --tag=laravel-generator-stubs
```

Templates will be published to `resources/stubs/laravel-generator/`

## ⚡ Available Commands

### Individual Component Generators (New!)

| Command | Description | Laravel-style Flags | Auto-Detection |
|---------|-------------|-------------------|----------------|
| `make:model+` | Enhanced model generator | `-m`, `-c`, `-r`, `-R`, `-S`, `-f`, `-s`, `-p`, `-a` | ✅ For new models: interactive/manual |
| `make:controller+` | Enhanced controller generator | `-m`, `-r`, `-S`, `--api`, `--requests`, `--resources` | ✅ From existing models |
| `make:view+` | Enhanced view generator | `--single-page`, `--separate-views` | ✅ From existing models |
| `make:request+` | Form request generator | `--store`, `--update` | ✅ From existing models |
| `make:resource+` | API resource generator | `--collection` | ✅ From existing models |

### Laravel-style Flags for `make:model+`

| Flag | Long Form | Description | Generated Files |
|------|-----------|-------------|----------------|
| `-m` | `--migration` | Also create migration | `database/migrations/create_*_table.php` |
| `-c` | `--controller` | Also create controller | `app/Http/Controllers/*Controller.php` |
| `-r` | `--resource` | Create resource controller | Resource controller with CRUD methods |
| `-R` | `--requests` | Also create form requests | `app/Http/Requests/Store*Request.php`, `app/Http/Requests/Update*Request.php` |
| `-S` | `--service` | Also create service class | `app/Services/*Service.php` |
| `-f` | `--factory` | Also create factory | `database/factories/*Factory.php` |
| `-s` | `--seed` | Also create seeder | `database/seeders/*Seeder.php` |
| `-p` | `--policy` | Also create policy | `app/Policies/*Policy.php` |
| `-a` | `--all` | Create **everything** | All of the above components |

### Additional Options

| Option | Description | Example |
|--------|-------------|---------|
| `--fields` | Define model fields explicitly | `--fields="name:string,email:string"` |
| `--api` | Create API controller instead of web | Creates API routes structure |
| `--force` | Overwrite existing files | Replaces existing files without prompt |

### Enhanced Laravel-style Flags Examples

```bash
# Model generator flags (requires --fields for new models)
php artisan make:model+ Product --fields="name:string,price:decimal" -mcRSf
php artisan make:model+ User --fields="name:string,email:string" -a
php artisan make:model+ Post --fields="title:string,content:text" -mcrRSfsp

# Interactive model creation (prompts for fields)
php artisan make:model+ Category -m    # Will prompt for fields interactively

# Specific combinations with service layer
php artisan make:model+ Article --fields="title:string,content:text" -mRS   # Model + Migration + Requests + Service
php artisan make:model+ Order --fields="total:decimal,status:enum:pending,completed" -mcRS --api

# Controller generation with service layer
php artisan make:controller+ ProductController -S        # Auto-detects fields + creates service
php artisan make:controller+ UserController -mS         # Migration + Service + Controller

# Other generators (auto-detect from existing models)
php artisan make:view+ products --single-page            # Auto-detects from Product model
php artisan make:request+ User --store                   # Auto-detects from User model
```

### Module Generator (Complete Solution)

| Command | Description | Key Features |
|---------|-------------|--------------|
| `make:module` | Complete CRUD module | All components + Auto-detection + Modal interface |

## 🎨 Command Options

### Available Options

| Option | Description | Example |
|--------|-------------|---------|
| `--fields` | Override auto-detection | `--fields="name:string,email:string"` |
| `--model` | Specify model for auto-detection | `--model=Product` |
| `--relationships` | Define relationships | `--relationships="user:belongsTo"` |
| `--api-only` | Skip view generation | `--api-only` |
| `--full` | Generate all components | `--full` |
| `--force` | Overwrite existing files | `--force` |
| `--with` | Additional components | `--with="jobs,events"` |

## 🎯 Smart Auto-Detection System

### How Auto-Detection Works

The generator intelligently detects fields using a priority system:

1. **Explicit --fields option** (highest priority)
2. **Auto-detection from existing model** (recommended)
3. **Interactive prompts** (if running in interactive mode)
4. **Error with helpful message** (fallback)

### Field Detection Sources

| Source | What's Detected | Example |
|--------|----------------|---------|
| **Model $fillable** | Field names and relationships | `protected $fillable = ['name', 'email', 'user_id'];` |
| **Model $casts** | Field types and enum classes | `protected $casts = ['status' => StatusEnum::class];` |
| **Migration files** | Column types, lengths, nullable | `$table->string('name', 100)->nullable();` |
| **Relationships** | Foreign keys from belongsTo methods | `public function user() { return $this->belongsTo(User::class); }` |

### Recommended Workflow

```bash
# 1. Create model with fields first (required for new models)
php artisan make:model+ Post --fields="title:string,content:text,status:enum:draft,published" -m

# 2. Generate other components that auto-detect from the model
php artisan make:controller+ PostController    # Auto-detects: title, content, status
php artisan make:view+ posts --single-page     # Auto-detects and creates modal forms
php artisan make:request+ Post                 # Auto-detects with smart validation

# 3. Or do it all at once with the model generator
php artisan make:model+ Product --fields="name:string,price:decimal,description:text" -mcr

# 4. For existing models, components auto-detect seamlessly
php artisan make:controller+ UserController --api    # Auto-detects from existing User model
```

### Auto-Detection Examples

```bash
# These work when models already exist
php artisan make:controller+ PostController     # Auto-detects from Post model
php artisan make:view+ admin/products --model=Product  # Auto-detects from Product model
php artisan make:request+ User                  # Auto-detects from User model

# Override auto-detection when needed
php artisan make:controller+ PostController --fields="title:string,content:text"

# Error handling - helpful messages when model doesn't exist
php artisan make:view+ orders  # Will show: "Model 'Order' not found. Create it first or use --fields"
```

### Interactive Mode

When no fields are provided and no model exists, enters interactive mode:

```bash
php artisan make:module Blog
```

The generator will prompt you to define fields interactively:
- Field name
- Field type (from dropdown)
- Nullable option
- Unique constraint
- Index requirement
- Enum values (if applicable)

## 🧪 Testing

Generate comprehensive tests for your modules:

```bash
# Feature tests are automatically generated
php artisan test tests/Feature/PostTest.php
```

Generated tests include:
- CRUD operations testing
- Validation testing
- Relationship testing
- Authentication testing
- API endpoint testing

## 🛠️ Advanced Usage

### Custom Service Directory

If you don't have a Services directory, the generator creates it automatically:

```
app/
├── Services/
│   ├── PostService.php
│   ├── UserService.php
│   └── OrderService.php
```

### Enum Integration

For enum fields, the generator creates dedicated enum classes:

```php
// app/Enums/PostStatusEnum.php
enum PostStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }
}
```

### Route Integration

After generation, add routes to your routes file:

```php
// routes/web.php (for web interface)
Route::resource('posts', PostController::class);

// routes/api.php (for API)
Route::apiResource('posts', PostController::class);
```

## 🚀 Best Practices

### 1. Field Naming
- Use snake_case for field names: `first_name`, `created_by`
- Use `_id` suffix for foreign keys: `user_id`, `category_id`
- Use descriptive names: `is_active` instead of `active`

### 2. Enum Fields
- Define clear enum values: `status:enum:draft,review,published`
- Use consistent naming: `active,inactive` or `enabled,disabled`

### 3. Relationships
- The generator auto-detects `belongsTo` relationships from `_id` fields
- Use explicit relationship definitions for complex cases

### 4. Services
- Keep controllers thin, move business logic to services
- Use services for complex queries and business rules
- Services handle multiple model interactions

### 5. Validation
- Generated validation rules are starting points
- Customize request classes for complex validation logic
- Add custom validation messages

## 🐛 Troubleshooting

### Common Issues

1. **Service Directory Missing**
   - The generator automatically creates the Services directory
   - Ensure your app has proper write permissions

2. **Enum Directory Missing**
   - Run: `mkdir app/Enums` if it doesn't exist
   - Or the generator creates it automatically

3. **Template Not Found**
   - Publish stubs: `php artisan vendor:publish --tag=laravel-generator-stubs`
   - Check template paths in config

4. **Namespace Issues**
   - Ensure PSR-4 autoloading is configured correctly
   - Run: `composer dump-autoload`

### Debug Mode

Enable verbose output to see what's being generated:

```bash
php artisan make:module Post --fields="title:string" -v
```

## 📝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Submit your changes

### Creating Custom Generators

Extend the base generator to create custom components:

```php
use YourName\LaravelGenerator\Generators\BaseGenerator;

class CustomGenerator extends BaseGenerator
{
    public function getStub(): string
    {
        return __DIR__.'/../Stubs/custom.stub';
    }
    
    public function getPath(): string
    {
        return app_path('Custom/'.$this->getClassName().'.php');
    }
    
    protected function getNamespace(): string
    {
        return 'App\\Custom';
    }
}
```

## 📄 License

The Laravel Comprehensive Generator is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Credits

- Built for Laravel 11+
- Inspired by Laravel's built-in generators
- Uses Laravel's filesystem and console components

## 📞 Support

If you encounter any issues or have questions:

1. Check the documentation above
2. Search existing issues on GitHub
3. Create a new issue with detailed information
4. Join our community discussions

---

**Made with ❤️ for the Laravel community**

Generate complete, production-ready CRUD modules in seconds, not hours!
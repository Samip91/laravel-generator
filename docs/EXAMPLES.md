# Usage Examples

This document provides comprehensive examples of using the Laravel Comprehensive Generator.

## Basic Examples

### 1. Simple Blog Post Module

```bash
php artisan make:module Post --fields="title:string,content:text,excerpt:string:nullable,published:boolean"
```

**Generated files:**
- Migration: `database/migrations/create_posts_table.php`
- Model: `app/Models/Post.php`
- Controller: `app/Http/Controllers/PostController.php`
- Service: `app/Services/PostService.php`
- Requests: `app/Http/Requests/{Store,Update}PostRequest.php`
- Resources: `app/Http/Resources/Post{Resource,ResourceCollection}.php`
- Policy: `app/Policies/PostPolicy.php`
- Factory: `database/factories/PostFactory.php`
- Seeder: `database/seeders/PostSeeder.php`
- Tests: `tests/Feature/PostTest.php`
- Views: `resources/views/posts/{index,create,edit,show}.blade.php`

### 2. E-commerce Product Module

```bash
php artisan make:module Product --fields="name:string,description:text,price:decimal,sku:string:unique,stock:integer,status:enum:active,inactive,category_id:foreign"
```

**Features generated:**
- Product enum with active/inactive status
- Category relationship (belongs to Category)
- Price with decimal casting
- Unique SKU validation
- Stock management fields

### 3. User Management Module

```bash
php artisan make:module User --fields="name:string,email:string:unique,role:enum:admin,editor,user,is_active:boolean,last_login:datetime:nullable" --api-only
```

**API-only features:**
- No Blade views generated
- JSON API resources
- Role-based enum
- Authentication-ready structure

## Advanced Examples

### 4. Complete Order Management System

```bash
php artisan make:module Order --fields="order_number:string:unique,total:decimal,tax_amount:decimal,shipping_cost:decimal,status:enum:pending,processing,shipped,delivered,cancelled,user_id:foreign,shipping_address:text,billing_address:text,notes:text:nullable,ordered_at:datetime" --full
```

**Advanced features:**
- Complex enum with multiple statuses
- Multiple address fields
- Financial calculations (total, tax, shipping)
- User relationship
- Comprehensive status management
- Full component generation

### 5. Content Management with Categories

```bash
# First create Category
php artisan make:module Category --fields="name:string:unique,slug:string:unique,description:text:nullable,is_active:boolean"

# Then create Article with category relationship
php artisan make:module Article --fields="title:string,slug:string:unique,content:text,excerpt:string:nullable,featured_image:string:nullable,status:enum:draft,review,published,category_id:foreign,author_id:foreign,published_at:datetime:nullable,meta_title:string:nullable,meta_description:text:nullable"
```

### 6. Task Management System

```bash
php artisan make:module Task --fields="title:string,description:text:nullable,priority:enum:low,medium,high,urgent,status:enum:todo,in_progress,review,completed,due_date:date:nullable,assigned_to:foreign,created_by:foreign,completed_at:datetime:nullable,estimated_hours:decimal:nullable" --with="jobs,events"
```

**Features:**
- Priority levels
- Assignment system
- Time tracking
- Status workflow
- Additional job and event classes

## Interactive Mode Examples

### 7. Interactive Blog Creation

```bash
php artisan make:module Blog
```

**Interactive prompts:**
```
Define fields for your model (press enter with empty name to finish):
Field name: title
Field type: string
Is this field nullable? (yes/no) [no]: no
Should this field be unique? (yes/no) [no]: no
Should this field be indexed? (yes/no) [no]: yes

Field name: content
Field type: text
Is this field nullable? (yes/no) [no]: no
...

Field name: status
Field type: enum
Is this field nullable? (yes/no) [no]: no
Enum values (comma-separated) [active,inactive]: draft,published,archived
...
```

## Real-World Scenarios

### 8. Restaurant Management

```bash
# Menu categories
php artisan make:module MenuCategory --fields="name:string,description:text:nullable,sort_order:integer,is_active:boolean"

# Menu items
php artisan make:module MenuItem --fields="name:string,description:text,price:decimal,image:string:nullable,is_available:boolean,category_id:foreign,ingredients:json:nullable,allergens:json:nullable"

# Orders
php artisan make:module RestaurantOrder --fields="order_number:string:unique,table_number:integer:nullable,customer_name:string:nullable,customer_phone:string:nullable,total_amount:decimal,tax_amount:decimal,status:enum:pending,preparing,ready,served,cancelled,order_type:enum:dine_in,takeaway,delivery,special_instructions:text:nullable"
```

### 9. Learning Management System

```bash
# Courses
php artisan make:module Course --fields="title:string,description:text,price:decimal:nullable,duration_hours:integer,level:enum:beginner,intermediate,advanced,status:enum:draft,published,archived,instructor_id:foreign,category_id:foreign,thumbnail:string:nullable"

# Lessons
php artisan make:module Lesson --fields="title:string,content:text,video_url:string:nullable,duration_minutes:integer:nullable,sort_order:integer,is_free:boolean,course_id:foreign"

# Enrollments
php artisan make:module Enrollment --fields="user_id:foreign,course_id:foreign,enrolled_at:datetime,completed_at:datetime:nullable,progress_percentage:integer,status:enum:active,completed,cancelled"
```

### 10. Event Management

```bash
php artisan make:module Event --fields="title:string,description:text,start_date:datetime,end_date:datetime,location:string,max_attendees:integer:nullable,price:decimal:nullable,status:enum:draft,published,cancelled,completed,category:enum:conference,workshop,seminar,networking,organizer_id:foreign,registration_deadline:datetime:nullable"

php artisan make:module EventRegistration --fields="event_id:foreign,user_id:foreign,registration_date:datetime,status:enum:pending,confirmed,cancelled,attended,payment_status:enum:pending,paid,refunded,special_requirements:text:nullable"
```

## API-Focused Examples

### 11. Mobile App Backend

```bash
# User profiles for mobile
php artisan make:module UserProfile --api-only --fields="user_id:foreign:unique,avatar:string:nullable,bio:text:nullable,location:string:nullable,website:string:nullable,social_links:json:nullable,preferences:json:nullable,is_public:boolean"

# App notifications
php artisan make:module AppNotification --api-only --fields="user_id:foreign,title:string,message:text,type:enum:info,warning,error,success,is_read:boolean,action_url:string:nullable,data:json:nullable"

# API tokens
php artisan make:module ApiToken --api-only --fields="user_id:foreign,name:string,token:string:unique,permissions:json:nullable,last_used_at:datetime:nullable,expires_at:datetime:nullable,is_active:boolean"
```

## Testing Examples

### 12. Generated Test Usage

After generating a module, the tests are ready to run:

```bash
# Run feature tests for a specific module
php artisan test tests/Feature/PostTest.php

# Run all generated tests
php artisan test --filter="*Test"

# With coverage
php artisan test --coverage
```

**Generated test methods:**
- `test_can_list_posts()`
- `test_can_create_post()`
- `test_can_show_post()`
- `test_can_update_post()`
- `test_can_delete_post()`
- `test_validation_rules()`

## Customization Examples

### 13. Custom Template Usage

After publishing stubs:

```bash
php artisan vendor:publish --tag=laravel-generator-stubs
```

Customize templates in `resources/stubs/laravel-generator/`:

```php
// resources/stubs/laravel-generator/model.stub
<?php

namespace {{ namespace }};

{{ uses }}
use Illuminate\Database\Eloquent\SoftDeletes; // Custom addition

class {{ class }} extends Model
{
    use HasFactory, SoftDeletes; // Custom trait

    // Your customizations...
}
```

### 14. Configuration Override

```php
// config/laravel-generator.php
return [
    'defaults' => [
        'generate_views' => false, // Skip views by default
        'generate_tests' => true,
    ],
    'paths' => [
        'services' => 'app/Domain/Services', // Custom path
        'models' => 'app/Domain/Models',
    ],
];
```

## Integration Examples

### 15. Route Registration

After generation, integrate with your application:

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});

// routes/api.php
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class);
    Route::apiResource('products', ProductController::class);
});
```

### 16. Service Container Binding

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->bind(PostServiceInterface::class, PostService::class);
    $this->app->bind(ProductServiceInterface::class, ProductService::class);
}
```

### 17. Middleware Integration

```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
];

// In your routes
Route::middleware(['auth', 'can:manage-posts'])->group(function () {
    Route::resource('posts', PostController::class)->except(['show', 'index']);
});
```

## Performance Examples

### 18. Optimized Queries

The generated services include optimized methods:

```php
// Generated service methods include:
$posts = $postService->paginate(15); // Paginated results
$posts = $postService->search('keyword'); // Search functionality
$posts = $postService->findBy(['status' => 'published']); // Filtered results
```

### 19. Eager Loading

Generated models include relationship methods for eager loading:

```php
// In your controller or service
$posts = Post::with(['category', 'author'])->paginate(15);
$orders = Order::with(['user', 'orderItems.product'])->get();
```

## Best Practices from Examples

1. **Consistent Naming**: Use clear, descriptive names for modules and fields
2. **Relationship Planning**: Design relationships before generation
3. **Enum Usage**: Leverage enums for status and type fields
4. **API Design**: Use `--api-only` for backend-only applications
5. **Testing**: Always run generated tests after customization
6. **Configuration**: Customize config for project-wide consistency

These examples demonstrate the power and flexibility of the Laravel Comprehensive Generator. Adapt them to your specific use cases and requirements.
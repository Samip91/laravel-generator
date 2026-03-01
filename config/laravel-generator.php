<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Generator Options
    |--------------------------------------------------------------------------
    |
    | These are the default options that will be applied when generating
    | components. You can override these by passing options to the command.
    |
    */

    'defaults' => [
        'generate_api_resources' => true,
        'generate_policies' => true,
        'generate_factories' => true,
        'generate_seeders' => true,
        'generate_tests' => true,
        'generate_views' => true,
        'force_overwrite' => false,
        'use_authentication' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Framework Detection
    |--------------------------------------------------------------------------
    |
    | The generator will automatically detect and use the appropriate UI
    | framework components. Set 'auto_detect' to false to manually specify.
    |
    */

    'ui_framework' => [
        'auto_detect' => true,
        'framework' => 'breeze', // breeze, jetstream, bootstrap, tailwind, custom
        'layout' => 'app', // app, guest, admin
        'use_components' => true,
        'authentication_middleware' => 'auth',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths Configuration
    |--------------------------------------------------------------------------
    |
    | Customize the paths where different components will be generated.
    |
    */

    'paths' => [
        'models' => 'app/Models',
        'controllers' => 'app/Http/Controllers',
        'services' => 'app/Services',
        'requests' => 'app/Http/Requests',
        'resources' => 'app/Http/Resources',
        'policies' => 'app/Policies',
        'enums' => 'app/Enums',
        'factories' => 'database/factories',
        'seeders' => 'database/seeders',
        'migrations' => 'database/migrations',
        'tests' => 'tests/Feature',
        'views' => 'resources/views',
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespaces Configuration
    |--------------------------------------------------------------------------
    |
    | Customize the namespaces for different components.
    |
    */

    'namespaces' => [
        'models' => 'App\\Models',
        'controllers' => 'App\\Http\\Controllers',
        'services' => 'App\\Services',
        'requests' => 'App\\Http\\Requests',
        'resources' => 'App\\Http\\Resources',
        'policies' => 'App\\Policies',
        'enums' => 'App\\Enums',
        'factories' => 'Database\\Factories',
        'seeders' => 'Database\\Seeders',
        'tests' => 'Tests\\Feature',
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Templates
    |--------------------------------------------------------------------------
    |
    | Path to stub templates. You can publish and customize these templates.
    |
    */

    'stubs' => [
        'path' => resource_path('stubs/laravel-generator'),
        'published' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Field Type Mappings
    |--------------------------------------------------------------------------
    |
    | Map field types to database column types and validation rules.
    |
    */

    'field_mappings' => [
        'string' => [
            'migration' => 'string',
            'cast' => 'string',
            'validation' => 'string|max:255',
        ],
        'text' => [
            'migration' => 'text',
            'cast' => 'string',
            'validation' => 'string',
        ],
        'integer' => [
            'migration' => 'integer',
            'cast' => 'integer',
            'validation' => 'integer',
        ],
        'decimal' => [
            'migration' => 'decimal:8,2',
            'cast' => 'decimal:2',
            'validation' => 'numeric',
        ],
        'boolean' => [
            'migration' => 'boolean',
            'cast' => 'boolean',
            'validation' => 'boolean',
        ],
        'date' => [
            'migration' => 'date',
            'cast' => 'date',
            'validation' => 'date',
        ],
        'datetime' => [
            'migration' => 'dateTime',
            'cast' => 'datetime',
            'validation' => 'date_format:Y-m-d H:i:s',
        ],
        'json' => [
            'migration' => 'json',
            'cast' => 'array',
            'validation' => 'array',
        ],
        'enum' => [
            'migration' => 'enum',
            'cast' => 'enum',
            'validation' => 'in',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship Mappings
    |--------------------------------------------------------------------------
    |
    | Common relationship patterns and their configurations.
    |
    */

    'relationships' => [
        'belongs_to' => [
            'method' => 'belongsTo',
            'foreign_key_pattern' => '{relation}_id',
        ],
        'has_many' => [
            'method' => 'hasMany',
            'foreign_key_pattern' => '{model}_id',
        ],
        'has_one' => [
            'method' => 'hasOne',
            'foreign_key_pattern' => '{model}_id',
        ],
        'many_to_many' => [
            'method' => 'belongsToMany',
            'pivot_table_pattern' => '{model1}_{model2}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Style Configuration
    |--------------------------------------------------------------------------
    |
    | Configure code formatting and style preferences.
    |
    */

    'code_style' => [
        'indent' => '    ', // 4 spaces
        'line_ending' => "\n",
        'use_short_array_syntax' => true,
        'use_nullable_types' => true,
        'use_typed_properties' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features of the generator.
    |
    */

    'features' => [
        'generate_model_observers' => false,
        'generate_job_classes' => false,
        'generate_event_classes' => false,
        'generate_notification_classes' => false,
        'generate_middleware' => false,
        'generate_mail_classes' => false,
        'generate_repository_pattern' => false,
        'generate_dto_classes' => false,
        'generate_swagger_docs' => false,
        'generate_postman_collection' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Variables
    |--------------------------------------------------------------------------
    |
    | Default values for template variables.
    |
    */

    'template_variables' => [
        'author' => env('GENERATOR_AUTHOR', 'Generated'),
        'company' => env('GENERATOR_COMPANY', ''),
        'license' => env('GENERATOR_LICENSE', 'MIT'),
        'php_version' => '8.2',
    ],

];
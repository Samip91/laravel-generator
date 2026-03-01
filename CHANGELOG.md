# Changelog

All notable changes to `brikshya/laravel-generator` will be documented in this file.

## [1.0.0] - 2024-03-01

### Added
- **Complete CRUD Module Generation**: Generate migrations, models, controllers, services, requests, resources, policies, factories, seeders, tests, enums, and views in a single command
- **Enhanced Modal Dialog System**: Beautiful modal dialogs with animations inspired by SweetAlert without external dependencies
- **Multiple UI Framework Support**: Auto-detects and supports Laravel Breeze, Tailwind CSS, and Bootstrap
- **Single-Page CRUD Interface**: Modal-based forms with AJAX operations for modern user experience
- **Interactive Field Definition**: Command-line prompts for easy field definition
- **Smart Field Analysis**: Automatically detect field types and generate appropriate validation
- **Enum Support**: Generate and integrate enum classes with helper methods
- **Service Layer Pattern**: Implements service pattern for business logic separation
- **Comprehensive Testing**: Generate feature and unit tests for all components
- **Customizable Templates**: Publish and customize stub templates
- **API Ready**: Generate API resources and controllers
- **Dark Mode Support**: All generated components support dark mode

### Features
- **Modal Dialog Types**: Success, error, warning, info, confirmation, and input prompt dialogs
- **Animations**: Smooth fade-in, scale, bounce, and shake animations
- **Responsive Design**: Mobile-friendly modal sizing and layouts
- **Keyboard Navigation**: Enter and Escape key support
- **Form Validation**: Real-time validation with error display
- **Loading States**: Professional loading indicators for async operations
- **Queue System**: Handle multiple dialogs gracefully
- **Framework Integration**: Seamless integration with Laravel Breeze components

### Command Options
- `--fields`: Define fields with types and options
- `--single-page`: Generate single-page CRUD interface with modals
- `--separate-views`: Generate traditional separate view files (default)
- `--api-only`: Generate API components only (no views)
- `--auth`: Add authentication middleware to controllers
- `--no-auth`: Skip authentication even if framework detected
- `--full`: Generate all possible components
- `--with`: Additional components (jobs, events, notifications)
- `--force`: Overwrite existing files

### Initial Release
This is the first stable release of the Laravel Comprehensive Generator package, providing a complete solution for rapid Laravel application development with modern, user-friendly interfaces.
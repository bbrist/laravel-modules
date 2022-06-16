# Laravel Modules

## Overview
This package provides an organizational structure pattern for domain logic within Laravel projects.

The package provides smart autoloading of code within each module (i.e. routes, commands).

Optionally, many of the Laravel `make:*` commands are proxied for modules to allow for the same
code generation commands within modules.

### Structure
The configuration of these modules is configurable, but the default configuration with two Modules
(`ModuleA` and `ModuleB`) might look like this:
```
app/
bootstrap/
config/
database/
lang/
modules/
----ModuleA/
--------Commands/
------------ACommand.php
--------Controllers/
------------AController.php
--------Models/
------------AModule.php
--------Routes/
------------routes.php
--------module.php
----ModuleB/
--------Commands/
------------BCommand.php
--------Providers/
------------AProvider.php
--------module.php   
public/
resources/
routes/
storage/
tests/
vendor/
```

Out of the box, `ACommand` and `BCommand` would be registered with the Service Container and routes
loaded.

## Installation

Require the package:
```
composer require async/laravel-modules
```

Update `composer.json` to add PSR-4 autoload mapping:
```
"autoload": {
  "psr-4": {
    ...
    "Modules\\": "modules/"
  }
}
```

Replace `Modules\\` and/or `modules/` depending on your configuration in `config/modules.php`

## Using Module Commands

To make use of the `module:` commands provided by this package, you'll need to add the `Async\Modules\ModulesServiceProvider` 
to the list of Service Providers in `config/app.php`.

### Creating Modules

To initialize a new module, run the `php artisan module:init {name}` command to create a new module and initialize
the `module.php` file (can be configured to use a different filename via the `config/modules.php` config).

### Creating Stubs

Laravel contains several stub generation methods (via the `make:*` methods). Some of these stub methods have been
proxied for modules, allowing users to leverage this tool for specific modules.

To use these methods for moodules, just replace `make` with `module` in the command and specify the name of the
module as the first argument.

For example, creating a command `HelloCommand` for module `Hello` would look like this:
```
php artisan module:command Hello HelloCommand --command "hello:cmd"
```

The above command would generate a `HelloCommand` class stub for file `modules/Hello/Commands/HelloCommand.php` (using the
default configuration).

The current supported (proxied) stub generation commands are:
- `module:command`
- `module:controller`
- `module:model`
- `module:provider`
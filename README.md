# Seeder Generation for Laravel

This is an experimental package to generate seeders from Laravel Telescope entries.

You can contribute by testing it, adding more automate tests, documentation or features!

## Installation

Require the package with Composer as a dev dependency:

`composer require cyber-duck/seeder-generator --dev`

If you are NOT using the Laravel package auto-discovery feature, please add the following service-provider to `config/app.php`

```php
[
    'providers' => [
        // ...
        \CyberDuck\Seeder\Providers\SeederGeneratorProvider::class,
    ]
];
```

## Usage

1. Run this command to get the last Telescope entry:

`php artisan telescope:last-entry-uuid`

You will receive a response like:

> The last entry uuid is: 937a170d-1aa2-495c-92e2-33389841bab5 (2021-05-20 15:50:20)

2. Reproduce the steps you want to reproduce (by interacting with the app manually or automatically with Cypress, Laravel Dusk, etc.)

3. Generate the seeder:

`php artisan telescope:seeder`

## Configuration options

This package will do its best to work without any configuration option, but you can also:

### Map tables to models:

```php
// config/seeder-generator.php
[
    //...
    'tablesDictionary' => [
        'my_table' => 'MyModel'
    ],
];
```

### Map static values to variables:

You can map static values of foreign keys to variables:

For example, everytime a query includes the field 'user_id' as a field or condition, it will be map to a `$userN` variable, where `N` will be dynamic, depending on the user ID.

```php
[
    //...
    'expectedVariables' => [
        'user_id' => 'user'
    ],
];
```

You can also map polymorphic relationships where the variable should change depending on the model:

```php
[
    //...
    'expectedVariables' => [
        'addressable_id' => 'morph:addressable_type'
    ],
    'morphVariables' => [
        'App\Models\User' => 'user',
    ],
];
```

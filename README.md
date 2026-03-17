# PHP_Laravel11_TypeScript_Transformer

## Project Description

PHP_Laravel11_TypeScript_Transformer is a simple Laravel 11 project that demonstrates how to automatically generate TypeScript types from your PHP models using the Spatie Laravel TypeScript Transformer package.

This project is ideal for developers who want type-safe frontend development with TypeScript while keeping PHP models as the single source of truth. Any change in the PHP models can be reflected in TypeScript automatically.


## Features

1. Automatic TypeScript Generation – Converts PHP models into TypeScript interfaces automatically.

2. Multiple Models Support – Works with any number of models (User, Post, Comment, etc.).

3. Public Properties Mapping – Only public properties are included in generated TypeScript types.

4. Nullable & Optional Properties – PHP nullable properties are mapped to optional TypeScript properties.

5. Custom Transformers – Supports Enums, DTOs, and other custom PHP types.

6. Configurable Output – Choose output file path for generated TypeScript types.

7. Type Safety – Ensures frontend TypeScript knows exactly the shape of backend models.

8. Consistency Across Stack – Keeps PHP and TypeScript models synchronized automatically.

9. Easy to Use – Just annotate PHP classes and run php artisan typescript:transform.

10. Scalable – Add new models or DTOs without changing config; just annotate them.


## Technology

1. Laravel 11 – PHP backend framework

2. PHP 8+ – Modern PHP with typed properties

3. Spatie TypeScript Transformer v2 – Auto-generate TypeScript types from PHP models

4. MySQL – Database (optional)

5. TypeScript – Frontend type safety


---



## Installation Steps


---


## STEP 1: Create Laravel 11 Project

### Open terminal / CMD and run:

```
composer create-project laravel/laravel PHP_Laravel11_TypeScript_Transformer "11.*"

```

### Go inside project:

```
cd PHP_Laravel11_TypeScript_Transformer

```

#### Explanation:

This installs a fresh Laravel 11 project and moves into the project folder to start development.




## STEP 2: Database Setup (Optional)

### Update database details:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel11_TypeScript_Transformer
DB_USERNAME=root
DB_PASSWORD=

```

### Create database in MySQL / phpMyAdmin:

```
Database name: laravel11_TypeScript_Transformer

```

### Then Run:

```
php artisan migrate

```


#### Explanation:

This connects Laravel to MySQL and creates default tables like users and migrations for the project.





## STEP 3: Install Spatie Laravel TypeScript Transformer

### Install package:

```
composer require spatie/laravel-typescript-transformer:^2.4

```

### Publish config

```
php artisan vendor:publish --provider="Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerServiceProvider" --tag="config"

```

### Now config file will be created

```
config/typescript-transformer.php

```

### Open: config/typescript-transformer.php

```
<?php

return [
    /*
     * This path will save generated TS definitions.
     */
    'output_file' => resource_path('ts/generated.d.ts'),

    /*
     * Which collectors to use (default collector finds @typescript/#[TypeScript])
     */
    'collectors' => [
        Spatie\TypeScriptTransformer\Collectors\DefaultCollector::class,
    ],

    /*
     * Transformers define how PHP types convert
     */
    'transformers' => [
        Spatie\TypeScriptTransformer\Transformers\EnumTransformer::class,
        Spatie\TypeScriptTransformer\Transformers\DtoTransformer::class,
    ],

    /*
     * Paths to scan for typed PHP classes
     */
    'auto_discover_types' => [
        app_path(),
    ],

    /*
     * Replacements for native types
     */
    'default_type_replacements' => [
        DateTime::class => 'string',
        DateTimeImmutable::class => 'string',
        Carbon\Carbon::class => 'string',
    ],
];

```

#### Explanation:

This installs the transformer package, publishes the config, and sets where and how PHP classes are converted into TypeScript types.





## STEP 4: Create Example Models

### In: app/Models/User.php

#### Add:

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @typescript
 */
class User extends Model
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $password; // nullable
}

```



### Create: app/Models/Post.php

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @typescript
 */
class Post extends Model
{
    public int $id;
    public string $title;
    public string $body;
    public int $user_id;
}

```

#### Explanation:

Annotating with @typescript tells the transformer to generate TypeScript types for these models automatically.



## STEP 5: Run Transformer

### Run:

```
php artisan typescript:transform

```

### You’ll get generated TypeScript definitions here:

```
resources/ts/generated.d.ts

```

### Example output:

```
export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Post {
    id: number;
    title: string;
    body: string;
    user_id: number;
}

```

#### Explanation:

This command scans all annotated classes and generates TypeScript definitions in the file specified in config/typescript-transformer.php.


## Expected Output:


<img src="screenshots/Screenshot 2026-03-17 165108.png" width="900">

<img src="screenshots/Screenshot 2026-03-17 174158.png" width="900">


---

## Project Folder Structure:

```
PHP_Laravel11_TypeScript_Transformer/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php        <-- annotated with /** @typescript */
│   │   └── Post.php        <-- annotated with /** @typescript */
│   ├── Providers/
│   └── ...
├── bootstrap/
│   └── cache/
├── config/
│   ├── app.php
│   ├── database.php
│   └── typescript-transformer.php   <-- config file for TS transformer
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── index.php
├── resources/
│   ├── views/
│   └── ts/
│       └── generated.d.ts         <-- generated TypeScript types
├── routes/
│   ├── api.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
├── vendor/
├── composer.json
├── composer.lock
├── package.json
├── tsconfig.json
└── .env

```

<?php

return [

    'models_path' => app_path('Models'),

    'output' => [
        'mode' => env('TS_OUTPUT_MODE', 'single'), // 'single' | 'per-model'
        'single_file' => resource_path('ts/generated.d.ts'),
        'per_model_dir' => resource_path('ts/models'),
        'namespace' => 'App.Models',
    ],

    'naming' => [
        'type_suffix' => '',
        'case' => 'PascalCase', // PascalCase | camelCase
    ],

    'excluded_fields' => [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ],

    'casts_map' => [
        'int' => 'number',
        'integer' => 'number',
        'float' => 'number',
        'double' => 'number',
        'decimal' => 'number',
        'string' => 'string',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'array' => 'Record<string, any>',
        'json' => 'Record<string, any>',
        'object' => 'Record<string, any>',
        'collection' => 'any[]',
        'date' => 'string',
        'datetime' => 'string',
        'immutable_date' => 'string',
        'immutable_datetime' => 'string',
        'timestamp' => 'string',
    ],

    'relations' => [
        'detect' => true,
        'methods' => ['hasMany', 'belongsTo', 'hasOne', 'belongsToMany', 'morphMany', 'morphTo', 'morphOne'],
    ],

    'validation_comments' => [
        'enabled' => true,
        'source' => 'rules', // looks for a static $rules or rules() on the model or a matching FormRequest
    ],

    'history' => [
        'enabled' => true,
        'path' => resource_path('ts/history'),
        'keep' => 20,
    ],

    'watch' => [
        'interval_seconds' => 2,
    ],

];
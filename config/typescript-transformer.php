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
<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionEnum;
use ReflectionMethod;
use ReflectionNamedType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class TypeScriptTransformerService
{
    protected array $config;
    protected array $modelClasses = [];
    protected array $generatedTypes = [];
    protected array $enumTypes = [];

    public function __construct()
    {
        $this->config = config('typescript-transformer');
    }

    public function run(): array
    {
        $this->discoverModels();

        foreach ($this->modelClasses as $class) {
            $this->transformModel($class);
        }

        return $this->write();
    }

    protected function discoverModels(): void
    {
        $path = $this->config['models_path'];

        if (!File::exists($path)) {
            return;
        }

        foreach (File::allFiles($path) as $file) {
            $content = File::get($file->getPathname());

            if (!Str::contains($content, ['@typescript', '#[TypeScript]'])) {
                continue;
            }

            $class = $this->classFromFile($file->getPathname());

            if ($class && class_exists($class) && is_subclass_of($class, Model::class)) {
                $this->modelClasses[] = $class;
            }
        }
    }

    protected function classFromFile(string $path): ?string
    {
        $content = File::get($path);

        preg_match('/namespace\s+(.+?);/', $content, $nsMatch);
        preg_match('/class\s+(\w+)/', $content, $classMatch);

        if (empty($classMatch[1])) {
            return null;
        }

        $namespace = $nsMatch[1] ?? 'App\\Models';

        return $namespace . '\\' . $classMatch[1];
    }

    protected function transformModel(string $class): void
    {
        $reflection = new ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();

        $fields = [];
        $excluded = $this->config['excluded_fields'];

        $casts = method_exists($instance, 'getCasts') ? $instance->getCasts() : [];
        $fillable = method_exists($instance, 'getFillable') ? $instance->getFillable() : [];
        $columns = array_unique(array_merge(['id'], $fillable, array_keys($casts)));

        $rules = $this->resolveValidationRules($class, $instance);

        foreach ($columns as $column) {
            if (in_array($column, $excluded, true)) {
                continue;
            }

            $tsType = $this->resolveFieldType($column, $casts, $rules);
            $nullable = $this->isNullable($column, $rules);
            $comment = $this->buildFieldComment($column, $rules);

            $fields[] = [
                'name' => $column,
                'type' => $tsType,
                'nullable' => $nullable,
                'comment' => $comment,
            ];
        }

        $relationFields = $this->config['relations']['detect']
            ? $this->detectRelations($reflection, $instance)
            : [];

        $this->generatedTypes[Str::afterLast($class, '\\')] = [
            'fields' => $fields,
            'relations' => $relationFields,
        ];
    }

    protected function resolveFieldType(string $column, array $casts, array $rules = []): string
    {
        if (isset($casts[$column])) {
            $cast = $casts[$column];
            $base = explode(':', $cast)[0];

            if (enum_exists($base)) {
                return $this->registerEnum($base);
            }

            return $this->config['casts_map'][$base] ?? 'string';
        }

        $ruleString = $this->ruleStringFor($column, $rules);

        if ($ruleString !== '') {
            if (Str::contains($ruleString, ['integer', 'numeric'])) {
                return 'number';
            }
            if (Str::contains($ruleString, 'boolean')) {
                return 'boolean';
            }
            if (Str::contains($ruleString, 'array')) {
                return 'Record<string, any>';
            }
        }

        if ($column === 'id' || Str::contains($column, '_id')) {
            return 'number';
        }

        return 'string';
    }

    protected function registerEnum(string $enumClass): string
    {
        $shortName = Str::afterLast($enumClass, '\\');

        if (isset($this->enumTypes[$shortName])) {
            return $shortName;
        }

        $reflection = new ReflectionEnum($enumClass);
        $cases = $reflection->getCases();
        $values = [];

        foreach ($cases as $case) {
            $backed = $case->getBackingValue();
            $values[] = is_string($backed) ? "'{$backed}'" : $backed;
        }

        $this->enumTypes[$shortName] = implode(' | ', $values);

        return $shortName;
    }

    protected function detectRelations(ReflectionClass $reflection, Model $instance): array
    {
        $relations = [];
        $allowedMethods = $this->config['relations']['methods'];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0 || $method->class !== $reflection->getName()) {
                continue;
            }

            $returnType = $method->getReturnType();

            if (!$returnType instanceof ReflectionNamedType) {
                continue;
            }

            $returnClass = $returnType->getName();

            if (!is_subclass_of($returnClass, Relation::class)) {
                continue;
            }

            $shortRelationType = Str::afterLast($returnClass, '\\');
            $normalizedType = lcfirst($shortRelationType);

            if (!in_array($normalizedType, $allowedMethods, true)) {
                continue;
            }

            try {
                $related = $method->invoke($instance)->getRelated();
                $relatedClass = Str::afterLast(get_class($related), '\\');
            } catch (\Throwable $e) {
                continue;
            }

            $isMany = in_array($normalizedType, ['hasMany', 'belongsToMany', 'morphMany'], true);

            $relations[] = [
                'name' => $method->getName(),
                'type' => $relatedClass . ($isMany ? '[]' : ''),
                'nullable' => !$isMany,
            ];
        }

        return $relations;
    }

    protected function resolveValidationRules(string $class, Model $instance): array
    {
        if (!$this->config['validation_comments']['enabled']) {
            return [];
        }

        if (method_exists($instance, 'rules')) {
            return $instance->rules();
        }

        if (property_exists($instance, 'rules')) {
            return $instance->rules ?? [];
        }

        $requestClass = 'App\\Http\\Requests\\Store' . Str::afterLast($class, '\\') . 'Request';

        if (class_exists($requestClass)) {
            $request = new $requestClass();
            if (method_exists($request, 'rules')) {
                return $request->rules();
            }
        }

        return [];
    }

    protected function ruleStringFor(string $column, array $rules): string
    {
        $rule = $rules[$column] ?? null;

        if ($rule === null) {
            return '';
        }

        return is_array($rule) ? implode('|', $rule) : (string) $rule;
    }

    protected function isNullable(string $column, array $rules): bool
    {
        if ($column === 'id') {
            return false;
        }

        $ruleString = $this->ruleStringFor($column, $rules);

        if (Str::contains($ruleString, 'required')) {
            return false;
        }

        return $ruleString === '' || Str::contains($ruleString, 'nullable');
    }

    protected function buildFieldComment(string $column, array $rules): ?string
    {
        $ruleString = $this->ruleStringFor($column, $rules);

        if ($ruleString === '') {
            return null;
        }

        return "/** validation: {$ruleString} */";
    }

    protected function write(): array
    {
        $mode = $this->config['output']['mode'];

        if ($mode === 'per-model') {
            return $this->writePerModel();
        }

        return $this->writeSingleFile();
    }

    protected function writeSingleFile(): array
    {
        $path = $this->config['output']['single_file'];
        $content = $this->buildFileContent(array_keys($this->generatedTypes));

        $this->ensureDirectory(dirname($path));
        File::put($path, $content);

        $this->recordHistory($path, $content);

        return ['mode' => 'single', 'path' => $path];
    }

    protected function writePerModel(): array
    {
        $dir = $this->config['output']['per_model_dir'];
        $this->ensureDirectory($dir);

        $paths = [];

        foreach ($this->generatedTypes as $name => $data) {
            $path = $dir . DIRECTORY_SEPARATOR . Str::snake($name) . '.d.ts';
            $content = $this->buildFileContent([$name]);
            File::put($path, $content);
            $paths[] = $path;
        }

        return ['mode' => 'per-model', 'paths' => $paths];
    }

    protected function buildFileContent(array $modelNames): string
    {
        $namespace = $this->config['output']['namespace'];
        $lines = ["declare namespace {$namespace} {"];

        foreach ($this->enumTypes as $name => $union) {
            $lines[] = "  export type {$name} = {$union};";
            $lines[] = "";
        }

        foreach ($modelNames as $name) {
            $data = $this->generatedTypes[$name] ?? null;
            if (!$data) {
                continue;
            }

            $lines[] = "  export type {$name} = {";

            foreach ($data['fields'] as $field) {
                $optional = $field['nullable'] ? '?' : '';
                $comment = $field['comment'] ? '  ' . $field['comment'] : '';
                $lines[] = "    {$field['name']}{$optional}: {$field['type']};{$comment}";
            }

            foreach ($data['relations'] as $relation) {
                $optional = $relation['nullable'] ? '?' : '';
                $lines[] = "    {$relation['name']}{$optional}: {$relation['type']};";
            }

            $lines[] = "  };";
            $lines[] = "";
        }

        $lines[] = "}";

        return implode("\n", $lines) . "\n";
    }

    protected function ensureDirectory(string $dir): void
    {
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    protected function recordHistory(string $path, string $content): void
    {
        if (!$this->config['history']['enabled']) {
            return;
        }

        $historyDir = $this->config['history']['path'];
        $this->ensureDirectory($historyDir);

        $filename = 'generated_' . now()->format('Y-m-d_H-i-s') . '.d.ts';
        File::put($historyDir . DIRECTORY_SEPARATOR . $filename, $content);

        $files = collect(File::files($historyDir))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->values();

        $keep = $this->config['history']['keep'];

        foreach ($files->slice($keep) as $old) {
            File::delete($old->getPathname());
        }
    }
}
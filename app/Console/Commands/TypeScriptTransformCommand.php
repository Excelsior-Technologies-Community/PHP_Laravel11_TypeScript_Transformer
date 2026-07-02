<?php

namespace App\Console\Commands;

use App\Services\TypeScriptTransformerService;
use Illuminate\Console\Command;

class TypeScriptTransformCommand extends Command
{
    protected $signature = 'typescript:transform';
    protected $description = 'Generate TypeScript definitions from annotated Eloquent models';

    public function handle(TypeScriptTransformerService $service): int
    {
        $this->info('Scanning models...');

        $result = $service->run();

        if ($result['mode'] === 'single') {
            $this->info("Generated: {$result['path']}");
        } else {
            $this->info('Generated ' . count($result['paths']) . ' files:');
            foreach ($result['paths'] as $path) {
                $this->line(" - {$path}");
            }
        }

        return self::SUCCESS;
    }
}
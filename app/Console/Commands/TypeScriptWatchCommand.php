<?php

namespace App\Console\Commands;

use App\Services\TypeScriptTransformerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TypeScriptWatchCommand extends Command
{
    protected $signature = 'typescript:watch';
    protected $description = 'Watch model files and regenerate TypeScript definitions on change';

    public function handle(TypeScriptTransformerService $service): int
    {
        $path = config('typescript-transformer.models_path');
        $interval = config('typescript-transformer.watch.interval_seconds', 2);

        $this->info("Watching {$path} for changes... (Ctrl+C to stop)");

        $lastHashes = $this->snapshot($path);

        while (true) {
            sleep($interval);

            $currentHashes = $this->snapshot($path);

            if ($currentHashes !== $lastHashes) {
                $this->info('[' . now()->format('H:i:s') . '] Change detected, regenerating...');

                try {
                    $service->run();
                    $this->info('Done.');
                } catch (\Throwable $e) {
                    $this->error('Failed: ' . $e->getMessage());
                }

                $lastHashes = $currentHashes;
            }
        }
    }

    protected function snapshot(string $path): array
    {
        if (!File::exists($path)) {
            return [];
        }

        $hashes = [];

        foreach (File::allFiles($path) as $file) {
            $hashes[$file->getPathname()] = $file->getMTime();
        }

        return $hashes;
    }
}
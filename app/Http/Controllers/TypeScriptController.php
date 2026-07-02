<?php

namespace App\Http\Controllers;

use App\Services\TypeScriptTransformerService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class TypeScriptController extends Controller
{
    protected array $allowedFormats = ['ts', 'json', 'txt'];

    public function index(Request $request)
    {
        $models = [];
        $modelPath = app_path('Models');

        if (File::exists($modelPath)) {
            $files = File::files($modelPath);
            foreach ($files as $file) {
                $content = File::get($file);
                if (
                    str_contains($content, '@typescript') ||
                    str_contains($content, '#[TypeScript]')
                ) {
                    $models[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        $generatedFile = resource_path('ts/generated.d.ts');
        $generatedContent = File::exists($generatedFile) ? File::get($generatedFile) : '';

        $stats = $this->computeStats($generatedContent);

        $lastGenerated = File::exists($generatedFile)
            ? date('d M Y h:i A', File::lastModified($generatedFile))
            : 'Not Generated';

        return view('dashboard', array_merge(compact(
            'models',
            'generatedContent',
            'lastGenerated'
        ), $stats));
    }

    public function generate(Request $request, TypeScriptTransformerService $service)
    {
        try {
            $service->run();

            return redirect()->back()->with([
                'success' => 'TypeScript definitions generated successfully!',
                'timestamp' => now()->format('d M Y h:i A'),
            ]);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->back()->withErrors([
                'error' => 'Failed to generate types: ' . $e->getMessage(),
            ]);
        }
    }

    public function download(Request $request)
    {
        $path = resource_path('ts/generated.d.ts');

        if (!File::exists($path)) {
            return redirect()->back()->withErrors([
                'error' => 'No generated types found. Please generate types first.',
            ]);
        }

        $format = $request->get('format', 'ts');

        if (!in_array($format, $this->allowedFormats, true)) {
            abort(422, 'Invalid export format.');
        }

        $content = File::get($path);

        switch ($format) {
            case 'json':
                $filename = 'typescript-definitions.json';
                $payload = [
                    'generated_at' => now()->toISOString(),
                    'typescript' => $content,
                    'metadata' => $this->computeStats($content),
                ];
                $content = json_encode($payload, JSON_PRETTY_PRINT);
                $mime = 'application/json';
                break;

            case 'txt':
                $filename = 'typescript-definitions.txt';
                $mime = 'text/plain';
                break;

            default:
                $filename = 'generated-types.d.ts';
                $mime = 'text/plain';
        }

        $filename = basename($filename);

        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function stats()
    {
        $path = resource_path('ts/generated.d.ts');

        if (!File::exists($path)) {
            return response()->json(['error' => 'No types generated'], 404);
        }

        return response()->json($this->computeStats(File::get($path)));
    }

    public function history()
    {
        $historyDir = resource_path('ts/history');

        if (!File::exists($historyDir)) {
            return response()->json([]);
        }

        $files = collect(File::files($historyDir))
            ->sortByDesc(fn ($f) => $f->getMTime())
            ->map(fn ($f) => [
                'filename' => $f->getFilename(),
                'generated_at' => date('d M Y h:i A', $f->getMTime()),
                'size_kb' => round($f->getSize() / 1024, 2),
            ])
            ->values();

        return response()->json($files);
    }

    protected function computeStats(string $content): array
    {
        $interfaceCount = substr_count($content, 'export type');
        $nullableCount = substr_count($content, '?:');
        $totalFieldMatches = preg_match_all('/^\s*\w+\??:\s*[^;]+;/m', $content);
        $requiredCount = max($totalFieldMatches - $nullableCount, 0);

        return [
            'interfaceCount' => $interfaceCount,
            'nullableCount' => $nullableCount,
            'requiredCount' => $requiredCount,
            'totalLines' => substr_count($content, "\n"),
            'totalSize' => round(strlen($content) / 1024, 2),
            'emailFieldCount' => preg_match_all('/\bemail\b/i', $content),
            'uniqueFieldCount' => preg_match_all('/\bunique\b/i', $content),
        ];
    }
}
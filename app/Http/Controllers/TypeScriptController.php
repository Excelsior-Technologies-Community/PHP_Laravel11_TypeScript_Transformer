<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;

class TypeScriptController extends Controller
{
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
        $generatedContent = '';

        if (File::exists($generatedFile)) {
            $generatedContent = File::get($generatedFile);
        }

        $interfaceCount = substr_count($generatedContent, 'export type');
        $nullableCount = substr_count($generatedContent, '?');
        
        $lastGenerated = File::exists($generatedFile)
            ? date('d M Y h:i A', File::lastModified($generatedFile))
            : 'Not Generated';

        // Get additional stats
        $totalLines = substr_count($generatedContent, "\n");
        $totalSize = File::exists($generatedFile) ? round(File::size($generatedFile) / 1024, 2) : 0;
        
        return view('dashboard', compact(
            'models',
            'generatedContent',
            'interfaceCount',
            'nullableCount',
            'lastGenerated',
            'totalLines',
            'totalSize'
        ));
    }

    public function generate(Request $request)
    {
        try {
            Artisan::call('typescript:transform');
            
            return redirect()->back()->with([
                'success' => 'TypeScript definitions generated successfully!',
                'timestamp' => now()->format('d M Y h:i A')
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors([
                'error' => 'Failed to generate types: ' . $e->getMessage()
            ]);
        }
    }

    public function download(Request $request)
    {
        $path = resource_path('ts/generated.d.ts');
        
        if (!File::exists($path)) {
            return redirect()->back()->withErrors([
                'error' => 'No generated types found. Please generate types first.'
            ]);
        }
        
        $format = $request->get('format', 'ts');
        
        switch($format) {
            case 'json':
                $content = File::get($path);
                $export = [
                    'generated_at' => now()->toISOString(),
                    'typescript' => $content,
                    'metadata' => [
                        'lines' => substr_count($content, "\n"),
                        'size' => round(strlen($content) / 1024, 2) . ' KB'
                    ]
                ];
                $filename = 'typescript-definitions.json';
                $content = json_encode($export, JSON_PRETTY_PRINT);
                $mime = 'application/json';
                break;
                
            case 'txt':
                $filename = 'typescript-definitions.txt';
                $content = File::get($path);
                $mime = 'text/plain';
                break;
                
            default:
                $filename = 'generated-types.d.ts';
                $content = File::get($path);
                $mime = 'text/plain';
        }
        
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
        
        $content = File::get($path);
        
        return response()->json([
            'total_interfaces' => substr_count($content, 'export type'),
            'total_lines' => substr_count($content, "\n"),
            'total_size' => round(File::size($path) / 1024, 2) . ' KB',
            'last_modified' => date('d M Y h:i A', File::lastModified($path)),
            'nullable_fields' => substr_count($content, '?'),
            'required_fields' => substr_count($content, ':') - substr_count($content, '?:')
        ]);
    }
}
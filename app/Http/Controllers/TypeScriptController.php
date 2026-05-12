<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class TypeScriptController extends Controller
{
    public function index()
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

        $interfaceCount = substr_count($generatedContent, 'interface');

        $nullableCount = substr_count($generatedContent, '?');

        $lastGenerated = File::exists($generatedFile)
            ? date('d M Y h:i A', File::lastModified($generatedFile))
            : 'Not Generated';

        return view('dashboard', compact(
            'models',
            'generatedContent',
            'interfaceCount',
            'nullableCount',
            'lastGenerated'
        ));
    }

    public function generate()
    {
        Artisan::call('typescript:transform');

        return redirect()->back()->with(
            'success',
            'TypeScript definitions generated successfully!'
        );
    }

    public function download()
    {
        $path = resource_path('ts/generated.d.ts');

        return response()->download(
            $path,
            'generated-types.txt',
            [
                'Content-Type' => 'text/plain',
            ]
        );
    }
}

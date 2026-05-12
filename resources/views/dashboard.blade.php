<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel TS Transformer</title>

    @vite(['resources/css/app.css'])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 min-h-screen text-white">

    <div class="max-w-7xl mx-auto p-6">

        <div class="flex justify-between items-center mb-10">

            <div>
                <h1 class="text-5xl font-bold mb-2">
                    Laravel TypeScript Transformer
                </h1>

                <p class="text-slate-300">
                    Auto Generate TypeScript Definitions From Laravel Models
                </p>
            </div>

            <div class="flex gap-4">

                <form action="{{ route('generate') }}" method="POST">
                    @csrf

                    <button
                        class="bg-indigo-600 hover:bg-indigo-700 transition px-6 py-3 rounded-xl shadow-lg font-semibold"
                    >
                        Generate Types
                    </button>
                </form>

                <a
                    href="{{ route('download') }}"
                    class="bg-emerald-600 hover:bg-emerald-700 transition px-6 py-3 rounded-xl shadow-lg font-semibold"
                >
                    Download TS
                </a>

            </div>

        </div>

        @if(session('success'))

            <div class="bg-green-500/20 border border-green-500 text-green-300 px-5 py-4 rounded-xl mb-8">
                {{ session('success') }}
            </div>

        @endif

        <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6 mb-10">

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">
                <h2 class="text-slate-300 text-sm mb-2">
                    Total Models
                </h2>

                <p class="text-4xl font-bold">
                    {{ count($models) }}
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">
                <h2 class="text-slate-300 text-sm mb-2">
                    Interfaces
                </h2>

                <p class="text-4xl font-bold">
                    {{ $interfaceCount }}
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">
                <h2 class="text-slate-300 text-sm mb-2">
                    Nullable Fields
                </h2>

                <p class="text-4xl font-bold">
                    {{ $nullableCount }}
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">
                <h2 class="text-slate-300 text-sm mb-2">
                    Last Generated
                </h2>

                <p class="text-lg font-semibold">
                    {{ $lastGenerated }}
                </p>
            </div>

        </div>

        <div class="grid lg:grid-cols-3 gap-8">

            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">

                <h2 class="text-2xl font-bold mb-6">
                    Detected Models
                </h2>

                <div class="space-y-4">

                    @forelse($models as $model)

                        <div class="bg-slate-800 rounded-xl px-5 py-4 flex items-center justify-between">

                            <span class="font-medium">
                                {{ $model }}
                            </span>

                            <span class="bg-indigo-500/20 text-indigo-300 px-3 py-1 rounded-full text-sm">
                                TypeScript
                            </span>

                        </div>

                    @empty

                        <p class="text-slate-400">
                            No annotated models found.
                        </p>

                    @endforelse

                </div>

            </div>

            <div class="lg:col-span-2 bg-white/10 backdrop-blur-lg rounded-2xl p-6 border border-white/10">

                <div class="flex justify-between items-center mb-6">

                    <h2 class="text-2xl font-bold">
                        Generated TypeScript
                    </h2>

                    <span class="bg-emerald-500/20 text-emerald-300 px-4 py-2 rounded-full text-sm">
                        generated.d.ts
                    </span>

                </div>

                <div class="bg-black rounded-2xl p-6 overflow-auto max-h-[650px]">

                    <pre class="text-green-400 text-sm whitespace-pre-wrap">{{ $generatedContent }}</pre>

                </div>

            </div>

        </div>

    </div>

</body>
</html>
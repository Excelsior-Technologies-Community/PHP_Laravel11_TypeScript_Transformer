<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel TypeScript Transformer - Smart Type Generator</title>

    @vite(['resources/css/app.css'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .code-block {
            background: #1e1e1e;
            border-radius: 12px;
            overflow: hidden;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .search-highlight {
            background-color: #fef3c7;
            color: #92400e;
        }

        .tab-active {
            border-bottom: 3px solid #667eea;
            color: #667eea;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <div id="toastContainer" class="toast-notification hidden">
        <div class="bg-white rounded-lg shadow-xl p-4 flex items-center gap-3 min-w-[300px]">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
            <div>
                <p class="font-semibold text-gray-800" id="toastMessage">Success!</p>
                <p class="text-sm text-gray-600" id="toastSubMessage">Operation completed successfully</p>
            </div>
        </div>
    </div>

    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="gradient-bg w-10 h-10 rounded-lg flex items-center justify-center">
                        <i class="fas fa-code text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-indigo-600 bg-clip-text text-transparent">
                            Laravel TS Transformer
                        </h1>
                        <p class="text-xs text-gray-500">Laravel → TypeScript Models</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="toggleTheme()" class="text-gray-600 hover:text-gray-900" id="themeToggle">
                        <i class="fas fa-moon text-xl"></i>
                    </button>
                    <button onclick="showSettings()" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-cog text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 rounded-lg px-4 py-3 mb-6">
                {{ session('success') }} <span class="text-sm text-green-600">({{ session('timestamp') }})</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-800 rounded-lg px-4 py-3 mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Models</p>
                        <p class="text-3xl font-bold text-gray-800" id="totalModels">{{ count($models) }}</p>
                    </div>
                    <div class="bg-indigo-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-database text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Interfaces</p>
                        <p class="text-3xl font-bold text-gray-800" id="interfaceCount">{{ $interfaceCount }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-code-branch text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Nullable Fields</p>
                        <p class="text-3xl font-bold text-gray-800" id="nullableCount">{{ $nullableCount }}</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-question-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6 card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Last Generated</p>
                        <p class="text-xl font-bold text-gray-800" id="lastGenerated">{{ $lastGenerated }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full w-12 h-12 flex items-center justify-center">
                        <i class="fas fa-clock text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8">
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <div class="flex gap-4">
                    <form action="{{ route('generate') }}" method="POST" id="generateForm">
                        @csrf
                        <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition flex items-center gap-2" id="generateBtn">
                            <i class="fas fa-sync-alt"></i>
                            <span>Generate Types</span>
                        </button>
                    </form>

                    <a href="{{ route('download') }}" class="bg-emerald-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition flex items-center gap-2" id="downloadBtn">
                        <i class="fas fa-download"></i>
                        <span>Download TS</span>
                    </a>

                    <button onclick="copyToClipboard()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-copy"></i>
                        <span>Copy to Clipboard</span>
                    </button>
                </div>

                <div class="flex gap-2">
                    <input type="text" id="searchInput" placeholder="Search in types..." class="border border-gray-300 rounded-lg px-4 py-2 w-64 focus:outline-none focus:border-purple-500">
                    <button onclick="searchInTypes()" class="bg-gray-100 px-4 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-search text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                    <h2 class="text-white font-bold text-xl flex items-center gap-2">
                        <i class="fas fa-cubes"></i>
                        Detected Models
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-3 max-h-[500px] overflow-y-auto">
                        @forelse($models as $model)
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition cursor-pointer" onclick="scrollToModel('{{ $model }}')">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-indigo-100 rounded-lg w-10 h-10 flex items-center justify-center">
                                            <i class="fas fa-file-code text-indigo-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800">{{ $model }}</p>
                                            <p class="text-xs text-gray-500">Model Interface</p>
                                        </div>
                                    </div>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-8">
                                <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                No annotated models found.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <h2 class="text-white font-bold text-xl flex items-center gap-2">
                            <i class="fas fa-code"></i>
                            Generated TypeScript
                        </h2>
                        <div class="flex gap-2">
                            <span class="bg-emerald-600 text-white px-3 py-1 rounded-lg text-sm">
                                generated.d.ts
                            </span>
                            <span class="bg-gray-700 text-white px-3 py-1 rounded-lg text-sm" id="lineCount">
                                {{ $totalLines }} lines
                            </span>
                            <span class="bg-gray-700 text-white px-3 py-1 rounded-lg text-sm" id="sizeCount">
                                {{ $totalSize }} KB
                            </span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="code-block overflow-auto max-h-[600px]">
                        <pre class="text-sm p-6" style="background: #1e1e1e; color: #d4d4d4; margin: 0;"><code id="typescriptCode" class="language-typescript">{{ $generatedContent ?: '// No types generated yet. Click "Generate Types" to start.' }}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-purple-600"></i>
                    Validation Rules
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-gray-600">Required Fields</span>
                        <span class="font-semibold text-purple-600" id="requiredCount">{{ $requiredCount }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-gray-600">Email Fields</span>
                        <span class="font-semibold text-purple-600" id="emailCount">{{ $emailFieldCount }}</span>
                    </div>
                    <div class="flex justify-between items-center border-b border-gray-200 pb-2">
                        <span class="text-gray-600">Unique Fields</span>
                        <span class="font-semibold text-purple-600" id="uniqueCount">{{ $uniqueFieldCount }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-export text-emerald-600"></i>
                    Export Options
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="exportAsJSON()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-file-code"></i> Export as JSON
                    </button>
                    <button onclick="exportAsMarkdown()" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        <i class="fas fa-file-alt"></i> Export as MD
                    </button>
                    <button onclick="shareTypes()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition">
                        <i class="fas fa-share-alt"></i> Share
                    </button>
                    <button onclick="printTypes()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>

    </div>

    <div id="settingsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="border-b border-gray-200 p-6">
                <h3 class="text-xl font-bold text-gray-800">Settings</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="autoRefresh" class="rounded border-gray-300">
                            <span class="text-gray-700">Auto-refresh on model changes</span>
                        </label>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Theme Mode</label>
                        <select id="themeSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="system">System</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 p-6 flex justify-end gap-3">
                <button onclick="closeSettings()" class="px-4 py-2 bg-gray-200 rounded-lg">Cancel</button>
                <button onclick="saveSettings()" class="px-4 py-2 bg-purple-600 text-white rounded-lg">Save</button>
            </div>
        </div>
    </div>

    <script>
        let currentTheme = 'light';

        function showToast(message, subMessage = '') {
            const toast = document.getElementById('toastContainer');
            document.getElementById('toastMessage').textContent = message;
            document.getElementById('toastSubMessage').textContent = subMessage;
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        function copyToClipboard() {
            const code = document.getElementById('typescriptCode').textContent;
            navigator.clipboard.writeText(code).then(() => {
                showToast('Copied!', 'TypeScript code copied to clipboard');
            });
        }

        function searchInTypes() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const code = document.getElementById('typescriptCode').textContent;

            if (searchTerm && code.toLowerCase().includes(searchTerm)) {
                showToast('Found!', `"${searchTerm}" found in type definitions`);
            } else if (searchTerm) {
                showToast('Not Found', `"${searchTerm}" not found in type definitions`);
            }
        }

        function scrollToModel(modelName) {
            const codeElement = document.getElementById('typescriptCode');
            const code = codeElement.textContent;

            if (code.includes(`export type ${modelName}`)) {
                showToast('Model Found', `${modelName} type definition exists`);
            } else {
                showToast('Model Not Found', `${modelName} not in generated types`);
            }
        }

        function exportAsJSON() {
            const code = document.getElementById('typescriptCode').textContent;
            const data = {
                exportedAt: new Date().toISOString(),
                typescriptCode: code,
                stats: {
                    lines: code.split('\n').length,
                    characters: code.length
                }
            };
            const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `typescript-export-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('Exported!', 'Types exported as JSON');
        }

        function exportAsMarkdown() {
            const code = document.getElementById('typescriptCode').textContent;
            const markdown = `# TypeScript Definitions\n\nGenerated on: ${new Date().toLocaleString()}\n\n\`\`\`typescript\n${code}\n\`\`\``;
            const blob = new Blob([markdown], {type: 'text/markdown'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `typescript-export-${Date.now()}.md`;
            a.click();
            URL.revokeObjectURL(url);
            showToast('Exported!', 'Types exported as Markdown');
        }

        function shareTypes() {
            const code = document.getElementById('typescriptCode').textContent;
            if (navigator.share) {
                navigator.share({
                    title: 'Laravel TypeScript Definitions',
                    text: code.substring(0, 1000),
                }).catch(() => {
                    copyToClipboard();
                });
            } else {
                copyToClipboard();
            }
        }

        function printTypes() {
            const code = document.getElementById('typescriptCode').textContent;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head><title>TypeScript Definitions</title></head>
                <body>
                    <pre style="font-family: monospace;">${code}</pre>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }

        function toggleTheme() {
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            applyTheme(currentTheme);
        }

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.body.classList.add('bg-gray-900');
                document.querySelectorAll('.bg-white').forEach(el => {
                    el.classList.remove('bg-white');
                    el.classList.add('bg-gray-800');
                });
                document.getElementById('themeToggle').innerHTML = '<i class="fas fa-sun text-xl"></i>';
            } else {
                document.body.classList.remove('bg-gray-900');
                document.querySelectorAll('.bg-gray-800').forEach(el => {
                    el.classList.remove('bg-gray-800');
                    el.classList.add('bg-white');
                });
                document.getElementById('themeToggle').innerHTML = '<i class="fas fa-moon text-xl"></i>';
            }
            localStorage.setItem('theme', theme);
        }

        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            currentTheme = savedTheme;
            applyTheme(currentTheme);
        }

        function showSettings() {
            document.getElementById('settingsModal').classList.remove('hidden');
            document.getElementById('settingsModal').classList.add('flex');
        }

        function closeSettings() {
            document.getElementById('settingsModal').classList.add('hidden');
            document.getElementById('settingsModal').classList.remove('flex');
        }

        function saveSettings() {
            const autoRefresh = document.getElementById('autoRefresh').checked;
            const theme = document.getElementById('themeSelect').value;
            localStorage.setItem('autoRefresh', autoRefresh);
            localStorage.setItem('theme', theme);
            applyTheme(theme);
            closeSettings();
            showToast('Settings Saved', 'Your preferences have been updated');
        }

        document.getElementById('generateForm')?.addEventListener('submit', function(e) {
            const btn = document.getElementById('generateBtn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
        });

        window.addEventListener('load', () => {
            showToast('Welcome!', 'Laravel TypeScript Transformer is ready');
        });
    </script>
</body>
</html>
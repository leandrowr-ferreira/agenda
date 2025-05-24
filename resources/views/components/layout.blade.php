<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Minha Aplicação' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">

    <!-- Header opcional -->
    <header class="bg-white shadow-md p-4 mb-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold">
                {{ $header ?? 'Bem-vindo' }}
            </h1>
        </div>
    </header>

    <!-- Conteúdo -->
    <main class="max-w-4xl mx-auto px-4">
        {{ $slot }}
    </main>

    <!-- Footer opcional -->
    <footer class="mt-12 text-center text-sm text-gray-500 py-4">
        &copy; {{ date('Y') }} Minha Aplicação. Todos os direitos reservados.
    </footer>

</body>
</html>

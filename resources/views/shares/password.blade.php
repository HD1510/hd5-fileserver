<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – Password Required</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="bg-white rounded-2xl shadow-lg p-8 w-full max-w-sm text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-400 mx-auto mb-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Password Required</h1>
        <p class="text-gray-500 text-sm mb-6">This share is password-protected.</p>

        @if($errors->any())
            <p class="text-red-600 text-sm mb-4">{{ $errors->first('password') }}</p>
        @endif

        <form method="POST" action="{{ route('shares.authenticate', $share->token) }}">
            @csrf
            <input type="password" name="password" placeholder="Enter password" required autofocus
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm mb-4 focus:ring-blue-500 focus:border-blue-500">
            <button type="submit" class="w-full bg-blue-600 text-white font-medium py-2.5 rounded-xl hover:bg-blue-700 transition-colors">
                Unlock
            </button>
        </form>
    </div>
</body>
</html>

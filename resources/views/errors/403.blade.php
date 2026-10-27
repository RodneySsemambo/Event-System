<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Evently</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Plus+Jakarta+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        h1 { font-family: 'Fraunces', serif; }
    </style>
</head>
<body class="bg-[#FAF6F1] min-h-screen flex items-center justify-center p-6 text-[#1C1220]">
    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-200 p-10 text-center">
        <!-- Logo -->
        <div class="mb-8">
            <span class="text-2xl font-bold text-[#241528]">Evently</span>
        </div>

        <!-- Icon -->
        <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
        </div>

        <h1 class="text-3xl font-semibold mb-3">Access Denied</h1>
        <p class="text-gray-500 mb-8 leading-relaxed">
            You do not have administrator privileges to access this page. If you are a client or provider, please return to the main website.
        </p>

        <div class="flex flex-col gap-3">
            <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}" class="w-full bg-[#C9A227] hover:bg-[#E4CE86] text-[#170D19] font-semibold py-3 px-6 rounded-lg transition-colors">
                Return to Evently
            </a>
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button type="submit" class="w-full bg-transparent hover:bg-gray-50 text-gray-600 font-medium py-3 px-6 rounded-lg border border-gray-200 transition-colors">
                    Log out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
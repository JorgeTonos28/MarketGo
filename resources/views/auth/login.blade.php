<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · MarketGo</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-xl p-8">
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Bienvenido a MarketGo</h1>
            <p class="text-sm text-slate-500 mb-6">Ingresa tus credenciales para continuar con tus compras inteligentes.</p>
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-600 mb-1">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="demo@marketgo.test">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-600 mb-1">Contraseña</label>
                    <input id="password" name="password" type="password" required class="w-full border border-slate-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="password">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        Recordarme
                    </label>
                    <a href="#" class="text-sm text-indigo-600 hover:underline">¿Olvidaste tu contraseña?</a>
                </div>
                @if($errors->any())
                    <div class="text-sm text-red-600">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg shadow hover:bg-indigo-500 transition-colors">Entrar</button>
            </form>
            <p class="mt-6 text-xs text-slate-400">
                Usa las cuentas demo: <span class="font-medium text-slate-600">admin@marketgo.test</span> o <span class="font-medium text-slate-600">demo@marketgo.test</span> con contraseña <span class="font-medium text-slate-600">password</span>.
            </p>
        </div>
    </div>
</body>
</html>

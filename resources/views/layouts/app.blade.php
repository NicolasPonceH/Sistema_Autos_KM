<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo', 'AutoTrack — PDI Fleet Control')</title>
    
    {{-- Google Fonts Oficiales de AutoTrack PDI --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hanken+Grotesk:wght@600;700;800&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-background font-body-md text-on-background min-h-full flex flex-col antialiased">
    {{-- Header Oficial AutoTrack PDI --}}
    <header class="fixed top-0 left-0 right-0 h-20 bg-primary z-50 flex items-center justify-between px-4 sm:px-8 shadow-lg">
        <div class="flex items-center gap-6">
            {{-- Logo y Nombre Oficial PDI --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group">
                <img src="{{ asset('img/pdi-logo.png') }}" alt="Logo PDI" class="h-10 w-auto brightness-0 invert transition-transform group-hover:scale-105" />
                <div>
                    <div class="text-white font-headline-md text-headline-md leading-none">AutoTrack</div>
                    <div class="text-on-primary-container font-label-mono text-xs tracking-tighter uppercase mt-0.5">PDI Fleet Control</div>
                </div>
            </a>

            {{-- Navegación Desktop --}}
            <nav class="hidden md:flex items-center h-20 ml-6 gap-2">
                <a href="{{ route('dashboard') }}"
                   class="transition-colors h-full flex items-center px-3.5 {{ request()->routeIs('dashboard') ? 'border-b-4 border-secondary-fixed text-white font-bold' : 'text-primary-fixed hover:text-white' }}">
                    Dashboard
                </a>
                <a href="{{ route('vehiculos.index') }}"
                   class="transition-colors h-full flex items-center px-3.5 {{ request()->routeIs('vehiculos.*') ? 'border-b-4 border-secondary-fixed text-white font-bold' : 'text-primary-fixed hover:text-white' }}">
                    Vehículos
                </a>
                <a href="{{ route('planes-mantencion.index') }}"
                   class="transition-colors h-full flex items-center px-3.5 {{ request()->routeIs('planes-mantencion.*') ? 'border-b-4 border-secondary-fixed text-white font-bold' : 'text-primary-fixed hover:text-white' }}">
                    Planes de Mantención
                </a>
                <a href="{{ route('reportes.km') }}"
                   class="transition-colors h-full flex items-center px-3.5 {{ request()->routeIs('reportes.*') ? 'border-b-4 border-secondary-fixed text-white font-bold' : 'text-primary-fixed hover:text-white' }}">
                    Reportes de KM
                </a>
            </nav>
        </div>

        {{-- Acciones y Perfil --}}
        <div class="flex items-center gap-4 sm:gap-6">
            <button type="button" onclick="openQuickOdometroModal()"
                    class="hidden lg:flex items-center gap-1.5 bg-surface-container-lowest text-primary px-3.5 py-1.5 rounded-lg text-xs font-bold hover:bg-primary-fixed transition-colors shadow-xs cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">speed</span>
                <span>+ Odómetro</span>
            </button>

            <div class="hidden sm:flex gap-3 border-r border-primary-container/60 pr-5">
                <button type="button" class="material-symbols-outlined text-primary-fixed hover:text-white transition-colors cursor-pointer" title="Notificaciones">
                    notifications
                </button>
            </div>

            <div class="flex items-center gap-3">
                <div class="text-right hidden lg:block">
                    <p class="text-sm font-bold text-white leading-tight">Oficial PDI</p>
                    <p class="text-xs text-on-primary-container leading-tight">RM Santiago</p>
                </div>
                <div class="w-10 h-10 rounded-full border-2 border-secondary-fixed bg-primary-container flex items-center justify-center text-white font-bold shadow-xs">
                    <span class="material-symbols-outlined text-[22px] text-secondary-fixed">shield_person</span>
                </div>
            </div>

            {{-- Botón Móvil --}}
            <button type="button" aria-label="Abrir menú" id="boton-menu-movil"
                    class="p-1.5 text-primary-fixed hover:text-white md:hidden cursor-pointer"
                    onclick="const p=document.getElementById('nav-movil'); p.classList.toggle('hidden');">
                <span class="material-symbols-outlined text-[28px]">menu</span>
            </button>
        </div>
    </header>

    {{-- Navegación Móvil Desplegable --}}
    <nav id="nav-movil" class="fixed top-20 left-0 right-0 z-40 bg-primary border-t border-primary-container hidden md:hidden shadow-xl">
        <div class="flex flex-col p-4 space-y-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-lg text-sm font-bold {{ request()->routeIs('dashboard') ? 'bg-primary-container text-white' : 'text-primary-fixed' }}">
                Dashboard
            </a>
            <a href="{{ route('vehiculos.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-bold {{ request()->routeIs('vehiculos.*') ? 'bg-primary-container text-white' : 'text-primary-fixed' }}">
                Vehículos
            </a>
            <a href="{{ route('planes-mantencion.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-bold {{ request()->routeIs('planes-mantencion.*') ? 'bg-primary-container text-white' : 'text-primary-fixed' }}">
                Planes de Mantención
            </a>
            <a href="{{ route('reportes.km') }}" class="px-4 py-2.5 rounded-lg text-sm font-bold {{ request()->routeIs('reportes.*') ? 'bg-primary-container text-white' : 'text-primary-fixed' }}">
                Reportes de KM
            </a>
            <div class="pt-2 border-t border-primary-container/60">
                <button type="button" onclick="openQuickOdometroModal(); document.getElementById('nav-movil').classList.add('hidden');"
                        class="w-full flex items-center justify-center gap-2 bg-surface-container-lowest text-primary py-2.5 rounded-lg text-xs font-bold">
                    <span class="material-symbols-outlined text-[18px]">speed</span>
                    Registrar Odómetro Rápido
                </button>
            </div>
        </div>
    </nav>

    {{-- Contenedor Principal --}}
    <main class="relative pt-24 min-h-screen bg-surface pb-12 flex-1">
        <div class="flex flex-col w-full px-4 sm:px-8 py-6 gap-6 max-w-[1280px] mx-auto">
            @if (session('status'))
                <div class="animar-entrada flex items-center justify-between rounded-xl bg-status-success/10 border border-status-success/30 p-4 text-status-success shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-status-success">check_circle</span>
                        <span class="font-medium text-sm text-on-surface">{{ session('status') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="animar-entrada flex items-center justify-between rounded-xl bg-status-danger/10 border border-status-danger/30 p-4 text-status-danger shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-status-danger">error</span>
                        <span class="font-medium text-sm text-on-surface">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-on-surface cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
            @endif

            @yield('contenido')
        </div>
    </main>

    {{-- Modal de lectura rápida --}}
    <x-quick-odometro-modal />
</body>
</html>

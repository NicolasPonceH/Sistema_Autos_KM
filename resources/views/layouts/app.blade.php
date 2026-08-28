<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('titulo', 'Sistema Autos KM')</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <header class="border-b border-border bg-surface">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm font-semibold tracking-tight">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-accent">
                    <path d="M5 17h14M5 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm14 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0M5 17V9.5a1 1 0 0 1 .3-.71l3-3A1 1 0 0 1 9 5.5h6a1 1 0 0 1 .7.29l3 3a1 1 0 0 1 .3.71V17M8 12h8" />
                </svg>
                Sistema Autos KM
            </a>

            <button type="button" aria-label="Abrir menú" aria-expanded="false" id="boton-menu-movil"
                    class="rounded-md p-1.5 text-text-muted hover:bg-surface-muted sm:hidden"
                    onclick="const p=document.getElementById('nav-movil'); const abierto=p.classList.toggle('abierto'); this.setAttribute('aria-expanded', abierto);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="h-5 w-5">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <nav class="hidden items-center gap-1 sm:flex">
                <x-nav-link route="dashboard" pattern="dashboard">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M3 13h4v8H3zM10 8h4v13h-4zM17 3h4v18h-4z" />
                        </svg>
                    </x-slot:icon>
                    Dashboard
                </x-nav-link>
                <x-nav-link route="vehiculos.index" pattern="vehiculos.*">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M5 17h14M5 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm14 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0M5 17V9.5a1 1 0 0 1 .3-.71l3-3A1 1 0 0 1 9 5.5h6a1 1 0 0 1 .7.29l3 3a1 1 0 0 1 .3.71V17" />
                        </svg>
                    </x-slot:icon>
                    Vehículos
                </x-nav-link>
                <x-nav-link route="planes-mantencion.index" pattern="planes-mantencion.*">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M14.7 6.3a4 4 0 0 1-5.6 5.6L4 17l3 3 5.1-5.1a4 4 0 0 1 5.6-5.6L15 12l-3-3 2.7-2.7Z" />
                        </svg>
                    </x-slot:icon>
                    Planes de mantención
                </x-nav-link>
                <x-nav-link route="reportes.km" pattern="reportes.*">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M3 17l5-5 4 4 8-8M20 8h-4v4" />
                        </svg>
                    </x-slot:icon>
                    Reporte de km
                </x-nav-link>
            </nav>
        </div>

        <nav id="nav-movil" class="acordeon border-t border-border sm:hidden">
            <div class="flex flex-col gap-1 px-4 py-3">
                <x-nav-link route="dashboard" pattern="dashboard" :mobile="true">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M3 13h4v8H3zM10 8h4v13h-4zM17 3h4v18h-4z" />
                        </svg>
                    </x-slot:icon>
                    Dashboard
                </x-nav-link>
                <x-nav-link route="vehiculos.index" pattern="vehiculos.*" :mobile="true">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M5 17h14M5 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm14 0a2 2 0 1 0 4 0 2 2 0 0 0-4 0M5 17V9.5a1 1 0 0 1 .3-.71l3-3A1 1 0 0 1 9 5.5h6a1 1 0 0 1 .7.29l3 3a1 1 0 0 1 .3.71V17" />
                        </svg>
                    </x-slot:icon>
                    Vehículos
                </x-nav-link>
                <x-nav-link route="planes-mantencion.index" pattern="planes-mantencion.*" :mobile="true">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M14.7 6.3a4 4 0 0 1-5.6 5.6L4 17l3 3 5.1-5.1a4 4 0 0 1 5.6-5.6L15 12l-3-3 2.7-2.7Z" />
                        </svg>
                    </x-slot:icon>
                    Planes de mantención
                </x-nav-link>
                <x-nav-link route="reportes.km" pattern="reportes.*" :mobile="true">
                    <x-slot:icon>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                            <path d="M3 17l5-5 4 4 8-8M20 8h-4v4" />
                        </svg>
                    </x-slot:icon>
                    Reporte de km
                </x-nav-link>
            </div>
        </nav>
    </header>

    <div class="mx-auto max-w-6xl px-4 py-8">
        @if (session('status'))
            <div class="animar-entrada mb-6 flex items-center gap-2 rounded-md bg-success-surface px-4 py-3 text-sm text-success">
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4 shrink-0">
                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                </svg>
                {{ session('status') }}
            </div>
        @endif

        <main>
            @yield('contenido')
        </main>
    </div>
</body>
</html>

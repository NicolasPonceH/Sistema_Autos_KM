@extends('layouts.app')

@section('titulo', 'Editar Plan — ' . $plan->nombre)

@section('contenido')
    <div class="max-w-2xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="font-headline-lg text-2xl font-bold text-primary">Editar Plan de Mantención</h1>
                <p class="text-sm text-on-surface-variant">Modificación de parámetros para {{ $plan->nombre }}</p>
            </div>
            <a href="{{ route('planes-mantencion.index') }}" class="text-primary font-bold text-sm hover:underline">
                ← Volver al listado
            </a>
        </div>

        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm border border-surface-variant">
            <form method="POST" action="{{ route('planes-mantencion.update', $plan) }}" class="space-y-6">
                @csrf
                @method('PUT')

                @include('planes-mantencion._campos', ['plan' => $plan])

                <div class="flex items-center justify-end gap-3 border-t border-surface-variant pt-5">
                    <a href="{{ route('planes-mantencion.index') }}"
                       class="bg-surface-container-lowest text-primary border border-surface-variant px-4 py-2 rounded-lg font-bold hover:bg-surface-container text-sm">
                        Cancelar
                    </a>
                    <x-button type="submit" variant="primary">
                        Guardar Cambios
                    </x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('titulo', 'Nuevo plan de mantención')

@section('contenido')
    <h1 class="mb-6 text-xl font-semibold tracking-tight">Nuevo plan de mantención</h1>

    <form method="POST" action="{{ route('planes-mantencion.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('planes-mantencion._campos')

        <div class="flex items-center gap-4">
            <x-button type="submit">Guardar</x-button>
            <x-button variant="link" as="a" href="{{ route('planes-mantencion.index') }}">Cancelar</x-button>
        </div>
    </form>
@endsection

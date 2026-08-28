@extends('layouts.app')

@section('titulo', 'Nuevo vehículo')

@section('contenido')
    <h1 class="mb-6 text-xl font-semibold tracking-tight">Nuevo vehículo</h1>

    <form method="POST" action="{{ route('vehiculos.store') }}" class="max-w-2xl space-y-6">
        @csrf

        @include('vehiculos._campos')

        <div class="flex items-center gap-4">
            <x-button type="submit">Guardar</x-button>
            <x-button variant="link" as="a" href="{{ route('vehiculos.index') }}">Cancelar</x-button>
        </div>
    </form>
@endsection

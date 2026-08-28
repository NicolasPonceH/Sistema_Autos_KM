@extends('layouts.app')

@section('titulo', 'Editar vehículo')

@section('contenido')
    <h1 class="mb-6 text-xl font-semibold tracking-tight">Editar vehículo — {{ $vehiculo->patente }}</h1>

    <form method="POST" action="{{ route('vehiculos.update', $vehiculo) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @include('vehiculos._campos')

        <div class="flex items-center gap-4">
            <x-button type="submit">Guardar cambios</x-button>
            <x-button variant="link" as="a" href="{{ route('vehiculos.index') }}">Cancelar</x-button>
        </div>
    </form>
@endsection

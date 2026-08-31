@props(['vehiculos' => null])

@php
    $listaVehiculos = $vehiculos ?? \App\Models\Vehiculo::where('activo', true)->orderBy('patente')->get();
@endphp

<div id="quick-odometro-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-primary/60 backdrop-blur-xs p-4 transition-opacity duration-200"
     role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
    <div class="animar-entrada w-full max-w-lg rounded-xl border border-surface-variant bg-surface-container-lowest p-6 shadow-2xl">
        <div class="flex items-center justify-between border-b border-surface-variant pb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-white shadow-xs">
                    <span class="material-symbols-outlined text-[22px]">speed</span>
                </div>
                <div>
                    <h3 id="modal-titulo" class="text-base font-bold font-headline-md text-primary">Registrar Odómetro Rápido</h3>
                    <p class="text-xs text-on-surface-variant">Lectura absoluta de tablero en tiempo real</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('quick-odometro-modal').classList.add('hidden'); document.getElementById('quick-odometro-modal').classList.remove('flex');"
                    class="rounded-lg p-1 text-on-surface-variant hover:bg-surface-container hover:text-primary cursor-pointer">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="form-quick-odometro" method="POST" action="" class="mt-5 space-y-4" onsubmit="return validarEnvioQuick(event)">
            @csrf

            <div>
                <label for="quick_patente" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                    Vehículo Operativo
                </label>
                <select id="quick_patente" required onchange="actualizarKmActualQuick(this)"
                        class="w-full rounded-lg border-surface-variant bg-surface-container-low px-3 py-2.5 text-sm font-medium text-primary focus:border-primary focus:bg-white">
                    <option value="">Seleccionar placa patente…</option>
                    @foreach ($listaVehiculos as $v)
                        <option value="{{ $v->patente }}" data-km="{{ $v->km_actual }}" data-action="{{ route('vehiculos.lecturas.store', $v) }}">
                            {{ $v->patente }} — {{ $v->marca }} {{ $v->modelo }} (Actual: {{ number_format($v->km_actual, 0, ',', '.') }} km)
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="quick_info_km" class="hidden rounded-lg border border-primary/20 bg-primary/5 p-3.5 text-xs text-primary">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-on-surface-variant">Último odómetro registrado:</span>
                    <span id="quick_km_actual_txt" class="font-label-mono font-bold text-primary text-sm">0 km</span>
                </div>
            </div>

            <div>
                <label for="quick_km" class="block text-xs font-bold font-label-mono uppercase tracking-wider text-on-surface-variant mb-1">
                    Nuevo Kilometraje Absoluto
                </label>
                <div class="relative">
                    <input type="number" name="km" id="quick_km" required min="0" placeholder="Ej. 45320"
                           class="w-full rounded-lg border-surface-variant px-3 py-2.5 font-label-mono text-base font-bold text-primary placeholder:text-outline-variant focus:border-primary">
                    <span class="absolute right-3.5 top-2.5 font-label-mono text-sm font-bold text-on-surface-variant">KM</span>
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-xs text-on-surface-variant">
                <input type="checkbox" name="es_correccion" id="quick_es_correccion" value="1"
                       class="rounded border-outline text-primary focus:ring-primary"
                       onchange="document.getElementById('quick_acordeon_obs').classList.toggle('abierto', this.checked)">
                <span>Es una corrección (el valor anterior estaba erróneo)</span>
            </label>

            <div id="quick_acordeon_obs" class="acordeon">
                <div class="space-y-1 pt-1">
                    <label for="quick_observacion" class="block text-xs font-bold text-on-surface-variant">Motivo de la corrección</label>
                    <textarea name="observacion" id="quick_observacion" rows="2" placeholder="Explica la razón de la corrección..."
                              class="w-full rounded-lg border-surface-variant text-xs text-on-surface"></textarea>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-surface-variant pt-4">
                <button type="button" onclick="document.getElementById('quick-odometro-modal').classList.add('hidden'); document.getElementById('quick-odometro-modal').classList.remove('flex');"
                        class="rounded-lg border border-surface-variant bg-surface-container-lowest px-4 py-2 text-sm font-bold text-primary hover:bg-surface-container cursor-pointer">
                    Cancelar
                </button>
                <x-button type="submit" variant="primary">
                    Guardar Odómetro
                </x-button>
            </div>
        </form>
    </div>
</div>

<script>
    function openQuickOdometroModal(patente = null) {
        const modal = document.getElementById('quick-odometro-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        if (patente) {
            const select = document.getElementById('quick_patente');
            select.value = patente;
            actualizarKmActualQuick(select);
        }
        setTimeout(() => document.getElementById('quick_km').focus(), 100);
    }

    function actualizarKmActualQuick(select) {
        const selected = select.options[select.selectedIndex];
        const infoDiv = document.getElementById('quick_info_km');
        const form = document.getElementById('form-quick-odometro');
        
        if (selected && selected.dataset.action) {
            form.action = selected.dataset.action;
            const km = parseInt(selected.dataset.km || 0);
            document.getElementById('quick_km_actual_txt').textContent = new Intl.NumberFormat('es-CL').format(km) + ' km';
            infoDiv.classList.remove('hidden');
            document.getElementById('quick_km').min = km;
        } else {
            form.action = '';
            infoDiv.classList.add('hidden');
        }
    }

    function validarEnvioQuick(e) {
        const select = document.getElementById('quick_patente');
        if (!select.value) {
            alert('Por favor selecciona un vehículo.');
            e.preventDefault();
            return false;
        }
        return true;
    }
</script>

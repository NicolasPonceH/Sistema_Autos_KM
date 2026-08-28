<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanMantencionRequest;
use App\Models\PlanMantencion;
use App\Models\TipoVehiculo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanMantencionController extends Controller
{
    public function index(): View
    {
        $planes = PlanMantencion::with('tipoVehiculo')->orderBy('nombre')->paginate(20);

        return view('planes-mantencion.index', compact('planes'));
    }

    public function create(): View
    {
        $tipos = TipoVehiculo::orderBy('nombre')->get();

        return view('planes-mantencion.create', compact('tipos'));
    }

    public function store(StorePlanMantencionRequest $request): RedirectResponse
    {
        PlanMantencion::create($request->validated());

        return redirect()->route('planes-mantencion.index')->with('status', 'Plan de mantención creado.');
    }

    public function edit(PlanMantencion $plan): View
    {
        $tipos = TipoVehiculo::orderBy('nombre')->get();

        return view('planes-mantencion.edit', compact('plan', 'tipos'));
    }

    public function update(StorePlanMantencionRequest $request, PlanMantencion $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()->route('planes-mantencion.index')->with('status', 'Plan de mantención actualizado.');
    }

    public function destroy(PlanMantencion $plan): RedirectResponse
    {
        if ($plan->eventos()->exists()) {
            return back()->withErrors(['plan' => 'No se puede eliminar: ya tiene servicios registrados contra este plan.']);
        }

        $plan->delete();

        return redirect()->route('planes-mantencion.index')->with('status', 'Plan de mantención eliminado.');
    }
}

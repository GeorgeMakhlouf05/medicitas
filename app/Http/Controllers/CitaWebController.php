<?php

namespace App\Http\Controllers;

use App\Actions\AgendarCita;
use App\Http\Requests\StoreCitaRequest;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CitaWebController extends Controller
{
    /**
     * Lista las citas. Opcionalmente filtra con ?estado=...
     * En la web se muestran todas por defecto.
     */
    public function index(Request $request): View
    {
        $filtro = $request->query('estado');

        // Un estado inválido se ignora en lugar de provocar un error
        if (! in_array($filtro, Cita::ESTADOS, true)) {
            $filtro = null;
        }

        // with() carga paciente y médico de una vez (evita el problema N+1)
        $citas = Cita::with(['patient', 'doctor'])
            ->when($filtro, fn ($consulta) => $consulta->estado($filtro))
            ->orderBy('fecha_hora')
            ->get();

        return view('citas.index', [
            'citas' => $citas,
            'estados' => Cita::ESTADOS,
            'filtro' => $filtro,
        ]);
    }

    /**
     * Muestra el formulario de agendamiento.
     */
    public function create(Request $request): View
    {
        // El médico puede ser cualquier usuario distinto del paciente actual
        $medicos = User::where('id', '!=', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('citas.create', ['medicos' => $medicos]);
    }

    /**
     * Guarda la cita. La validación ocurre en StoreCitaRequest antes de
     * llegar aquí, y el paciente siempre es el usuario autenticado.
     */
    public function store(StoreCitaRequest $request, AgendarCita $agendarCita): RedirectResponse
    {
        $agendarCita->ejecutar($request->user(), $request->validated());

        return redirect()
            ->route('citas.index')
            ->with('status', 'La cita fue agendada correctamente.');
    }
}
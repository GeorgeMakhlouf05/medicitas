<?php

namespace App\Http\Controllers\Api;

use App\Actions\AgendarCita;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * Lista citas filtradas por estado.
     * Si no se envía ?estado=..., se devuelven solo las confirmadas.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'estado' => ['nullable', Rule::in(Cita::ESTADOS)],
        ]);

        $estado = $validated['estado'] ?? Cita::ESTADO_CONFIRMADA;

        // with(): carga paciente y médico en 2 consultas adicionales en total,
        // sin importar cuántas citas haya (evita el problema N+1)
        $citas = Cita::with(['patient', 'doctor'])
            ->estado($estado)
            ->orderBy('fecha_hora')
            ->get();

        return CitaResource::collection($citas);
    }

    /**
     * Agenda una cita para el paciente autenticado.
     */
    public function store(StoreCitaRequest $request, AgendarCita $agendarCita): JsonResponse
    {
        $cita = $agendarCita->ejecutar($request->user(), $request->validated());

        $cita->loadMissing(['patient', 'doctor']);

        return (new CitaResource($cita))
            ->response()
            ->setStatusCode(201);
    }
}
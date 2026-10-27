<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Cita
 */
class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'especialidad' => $this->especialidad,
            'fecha_hora' => $this->fecha_hora?->toIso8601String(),
            'estado' => $this->estado,
            'motivo' => $this->motivo,
            // Siempre se incluyen paciente y médico. Si la relación no fue
            // cargada con with(), Eloquent la consulta fila por fila (N+1).
            'patient' => [
                'id' => $this->patient->id,
                'name' => $this->patient->name,
            ],
            'doctor' => [
                'id' => $this->doctor->id,
                'name' => $this->doctor->name,
                'email' => $this->doctor->email,
            ],
        ];
    }
}
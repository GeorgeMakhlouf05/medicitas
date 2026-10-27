<?php

namespace App\Actions;

use App\Mail\CitaAgendada;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AgendarCita
{
    /**
     * Crea una cita pendiente para el paciente y notifica al médico.
     *
     * @param  array{doctor_id: int, especialidad: string, fecha_hora: string, motivo: string}  $datos
     */
    public function ejecutar(User $paciente, array $datos): Cita
    {
        // 1. Se persiste la cita. La validación ya ocurrió en StoreCitaRequest.
        $cita = Cita::create([
            'patient_id' => $paciente->id,
            'doctor_id' => $datos['doctor_id'],
            'especialidad' => $datos['especialidad'],
            'fecha_hora' => $datos['fecha_hora'],
            'motivo' => $datos['motivo'],
            // "estado" toma el valor por defecto "pendiente"
        ]);

        $cita->load(['patient', 'doctor']);

        // 2. Solo después de guardarla se avisa al médico. Si el envío falla,
        //    la cita ya existe: el error se registra en el log en lugar de
        //    devolver un error 500 al paciente.
        try {
            Mail::to($cita->doctor->email)->send(new CitaAgendada($cita));
        } catch (Throwable $e) {
            report($e);
        }

        return $cita;
    }
}
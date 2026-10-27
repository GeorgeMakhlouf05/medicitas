<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCitaRequest extends FormRequest
{
    /**
     * Solo un usuario autenticado puede agendar una cita.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Reglas de validación. El paciente NO se recibe en la petición:
     * siempre es el usuario autenticado. El estado tampoco se recibe:
     * toda cita nueva nace como "pendiente".
     */
    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'integer',
                'exists:users,id',
                // Un usuario no puede ser su propio médico
                Rule::notIn([$this->user()?->id]),
            ],
            'especialidad' => ['required', 'string', 'max:100'],
            'fecha_hora' => ['required', 'date', 'after:now'],
            'motivo' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Debes seleccionar un médico.',
            'doctor_id.exists' => 'El médico seleccionado no existe.',
            'doctor_id.not_in' => 'No puedes seleccionarte a ti mismo como médico.',
            'especialidad.required' => 'La especialidad es obligatoria.',
            'especialidad.max' => 'La especialidad no puede superar los 100 caracteres.',
            'fecha_hora.required' => 'La fecha y hora de la cita son obligatorias.',
            'fecha_hora.date' => 'La fecha y hora no tienen un formato válido.',
            'fecha_hora.after' => 'La fecha y hora deben ser posteriores al momento actual.',
            'motivo.required' => 'El motivo de la consulta es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max' => 'El motivo no puede superar los 1000 caracteres.',
        ];
    }
}
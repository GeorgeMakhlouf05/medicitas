<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Cita extends Model
{
    use HasFactory;

    // Estados permitidos según el enunciado
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_CONFIRMADA = 'confirmada';
    public const ESTADO_CANCELADA = 'cancelada';

    public const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADA,
        self::ESTADO_CANCELADA,
    ];

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'especialidad',
        'fecha_hora',
        'estado',
        'motivo',
    ];

    // Valor por defecto también en el modelo, para que el objeto recién
    // creado ya incluya "estado" sin necesidad de recargarlo
    protected $attributes = [
        'estado' => self::ESTADO_PENDIENTE,
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    // Paciente que solicita la cita
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Médico asignado a la cita
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
    // Permite escribir Cita::estado('confirmada')->get()
public function scopeEstado(Builder $query, string $estado): Builder
{
    return $query->where('estado', $estado);
}
}
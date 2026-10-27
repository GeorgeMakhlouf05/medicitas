<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'doctor_id' => User::factory(),
            'especialidad' => $this->faker->randomElement([
                'Cardiología',
                'Pediatría',
                'Dermatología',
                'Neurología',
                'Medicina General',
            ]),
            'fecha_hora' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            'estado' => Cita::ESTADO_PENDIENTE,
            'motivo' => $this->faker->sentence(),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => Cita::ESTADO_PENDIENTE]);
    }

    public function confirmada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => Cita::ESTADO_CONFIRMADA]);
    }

    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => Cita::ESTADO_CANCELADA]);
    }
}
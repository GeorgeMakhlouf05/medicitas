<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        // Evita duplicar datos si el seeder se ejecuta más de una vez
        if (Cita::exists()) {
            return;
        }

        // Paciente de demostración (contraseña: password)
        $paciente = User::firstOrCreate(
            ['email' => 'paciente@medicitas.test'],
            [
                'name' => 'Paciente Demo',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Médicos de demostración
        $medicos = collect([
            ['name' => 'Dra. Laura Gómez', 'email' => 'laura.gomez@medicitas.test'],
            ['name' => 'Dr. Carlos Rivera', 'email' => 'carlos.rivera@medicitas.test'],
            ['name' => 'Dra. Ana Torres', 'email' => 'ana.torres@medicitas.test'],
        ])->map(fn (array $datos) => User::firstOrCreate(
            ['email' => $datos['email']],
            [
                'name' => $datos['name'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        ));

        // Citas de ejemplo con los tres estados
        foreach ($medicos as $medico) {
            $ids = ['patient_id' => $paciente->id, 'doctor_id' => $medico->id];

            Cita::factory()->count(2)->confirmada()->create($ids);
            Cita::factory()->pendiente()->create($ids);
            Cita::factory()->cancelada()->create($ids);
        }
    }
}
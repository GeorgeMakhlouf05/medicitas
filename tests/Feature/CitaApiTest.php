<?php

namespace Tests\Feature;

use App\Mail\CitaAgendada;
use App\Models\Cita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    // Migra la base de datos en memoria antes de cada prueba y la deja limpia
    use RefreshDatabase;

    public function test_muestra_citas_confirmadas(): void
    {
        // Preparación: citas en los tres estados posibles
        $confirmadas = Cita::factory()->count(3)->confirmada()->create();
        Cita::factory()->count(2)->pendiente()->create();
        Cita::factory()->count(2)->cancelada()->create();

        // Acción: consulta al endpoint sin filtro (por defecto, solo confirmadas)
        $respuesta = $this->getJson('/api/citas');

        // Verificación: código HTTP y cantidad
        $respuesta->assertStatus(200)
            ->assertJsonCount(3, 'data');

        $datos = collect($respuesta->json('data'));

        // Todas las citas devueltas están confirmadas
        $this->assertSame(
            ['confirmada'],
            $datos->pluck('estado')->unique()->values()->all()
        );

        // Y son exactamente las tres confirmadas que se crearon
        $this->assertEqualsCanonicalizing(
            $confirmadas->pluck('id')->all(),
            $datos->pluck('id')->all()
        );

        // Las citas pendientes y canceladas no aparecen en la respuesta
        $respuesta->assertJsonMissing(['estado' => 'pendiente'])
            ->assertJsonMissing(['estado' => 'cancelada']);
    }

    public function test_agendar_cita_y_envio_correo(): void
    {
        // Evita enviar correos reales: registra los envíos en memoria
        Mail::fake();

        $medico = User::factory()->create();
        $paciente = User::factory()->create();

        $datos = [
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'fecha_hora' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'motivo' => 'Dolor en el pecho al hacer ejercicio',
        ];

        // Autentica al paciente con el guard "sanctum" (el de la ruta protegida)
        Sanctum::actingAs($paciente);

        $respuesta = $this->postJson('/api/citas', $datos);

        // Respuesta: 201 Created con los datos de la cita
        $respuesta->assertStatus(201)
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.patient_id', $paciente->id)
            ->assertJsonPath('data.doctor_id', $medico->id)
            ->assertJsonPath('data.especialidad', 'Cardiología');

        // Persistencia: la cita existe con las llaves foráneas correctas
        $this->assertDatabaseHas('citas', [
            'patient_id' => $paciente->id,
            'doctor_id' => $medico->id,
            'especialidad' => 'Cardiología',
            'fecha_hora' => $datos['fecha_hora'],
            'estado' => 'pendiente',
            'motivo' => 'Dolor en el pecho al hacer ejercicio',
        ]);

        // Correo: se envió exactamente uno, dirigido al médico y no al paciente
        Mail::assertSent(CitaAgendada::class, 1);

        Mail::assertSent(CitaAgendada::class, function ($correo) use ($medico, $paciente) {
            return $correo->hasTo($medico->email)
                && ! $correo->hasTo($paciente->email)
                && $correo->cita->doctor_id === $medico->id;
        });
    }

    public function test_detecta_problema_n_mas_1(): void
    {
        // Con 3 citas (cada cita trae su propio paciente y su propio médico)
        Cita::factory()->count(3)->confirmada()->create();
        [$consultasConTres] = $this->peticionConConteoDeConsultas();

        // Con 10 citas en total (se agregan 7 más)
        Cita::factory()->count(7)->confirmada()->create();
        [$consultasConDiez, $citas] = $this->peticionConConteoDeConsultas();

        // La respuesta realmente usa los datos de paciente y médico; así el
        // conteo no es bajo solo porque se omitieron las relaciones
        $this->assertCount(10, $citas);
        foreach ($citas as $cita) {
            $this->assertNotEmpty($cita['patient']['name']);
            $this->assertNotEmpty($cita['doctor']['name']);
        }

        // 1) El número de consultas NO debe crecer al pasar de 3 a 10 citas
        $this->assertCount(
            count($consultasConTres),
            $consultasConDiez,
            sprintf(
                'Se detectó un problema N+1: con 3 citas se ejecutaron %d consultas y con 10 citas se ejecutaron %d.',
                count($consultasConTres),
                count($consultasConDiez)
            )
        );

        // 2) Desglose esperado con carga ansiosa: 1 consulta a citas y
        //    2 a users (una para los pacientes y otra para los médicos)
        $this->assertCount(1, $this->consultasSobreTabla($consultasConDiez, 'citas'));
        $this->assertCount(
            2,
            $this->consultasSobreTabla($consultasConDiez, 'users'),
            'Se esperaban 2 consultas a users (pacientes y médicos), no una por cada cita.'
        );
    }

    /**
     * Ejecuta GET /api/citas y devuelve [consultas SQL ejecutadas, citas del JSON].
     *
     * El listener se registra DESPUÉS de crear los datos de prueba, de modo que
     * solo cuenta las consultas de la petición. La ruta es pública, así que no
     * hay consultas de autenticación, y GET no abre transacciones.
     */
    private function peticionConConteoDeConsultas(): array
    {
        $consultas = [];
        $escuchando = true;

        DB::listen(function ($consulta) use (&$consultas, &$escuchando) {
            if ($escuchando) {
                $consultas[] = $consulta->sql;
            }
        });

        $respuesta = $this->getJson('/api/citas');
        $escuchando = false;

        $respuesta->assertOk();

        return [$consultas, $respuesta->json('data')];
    }

    /**
     * Filtra las consultas SQL que leen de una tabla dada.
     */
    private function consultasSobreTabla(array $consultas, string $tabla): array
    {
        return array_values(array_filter(
            $consultas,
            fn (string $sql) => preg_match('/\bfrom\s+["`\[]?' . $tabla . '\b/i', $sql) === 1
        ));
    }
}
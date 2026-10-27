<x-mail::message>
# Nueva cita agendada

Hola, {{ $cita->doctor->name }}. El paciente **{{ $cita->patient->name }}** ha agendado una cita contigo.

- **Fecha y hora:** {{ $cita->fecha_hora->format('d/m/Y H:i') }}
- **Especialidad:** {{ $cita->especialidad }}
- **Estado:** {{ ucfirst($cita->estado) }}
- **Motivo de la consulta:** {{ $cita->motivo }}

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
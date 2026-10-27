<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agendar cita médica
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-4 p-4 rounded bg-red-100 text-red-800 text-sm">
                        Revisa los campos marcados: la cita no fue guardada.
                    </div>
                @endif

                <form method="POST" action="{{ route('citas.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Paciente</label>
                        {{-- El paciente no es un campo del formulario: es el usuario autenticado --}}
                        <p class="mt-1 text-gray-900">{{ auth()->user()->name }}</p>
                    </div>

                    <div>
                        <label for="doctor_id" class="block text-sm font-medium text-gray-700">Médico</label>
                        <select id="doctor_id" name="doctor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">Selecciona un médico</option>
                            @foreach ($medicos as $medico)
                                <option value="{{ $medico->id }}" @selected((int) old('doctor_id') === $medico->id)>
                                    {{ $medico->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('doctor_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="especialidad" class="block text-sm font-medium text-gray-700">Especialidad</label>
                        <input id="especialidad" name="especialidad" type="text" list="especialidades"
                               value="{{ old('especialidad') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <datalist id="especialidades">
                            <option value="Cardiología"></option>
                            <option value="Pediatría"></option>
                            <option value="Dermatología"></option>
                            <option value="Neurología"></option>
                            <option value="Medicina General"></option>
                        </datalist>
                        @error('especialidad')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fecha_hora" class="block text-sm font-medium text-gray-700">Fecha y hora</label>
                        <input id="fecha_hora" name="fecha_hora" type="datetime-local"
                               value="{{ old('fecha_hora') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @error('fecha_hora')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="motivo" class="block text-sm font-medium text-gray-700">Motivo de la consulta</label>
                        <textarea id="motivo" name="motivo" rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('motivo') }}</textarea>
                        @error('motivo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">
                            Agendar cita
                        </button>
                        <a href="{{ route('citas.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Citas médicas
        </h2>
    </x-slot>

    @php
        $colores = [
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'confirmada' => 'bg-green-100 text-green-800',
            'cancelada' => 'bg-red-100 text-red-800',
        ];
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
                    <form method="GET" action="{{ route('citas.index') }}" class="flex items-end gap-2">
                        <div>
                            <label for="estado" class="block text-sm font-medium text-gray-700">
                                Filtrar por estado
                            </label>
                            <select id="estado" name="estado" class="mt-1 rounded-md border-gray-300 shadow-sm">
                                <option value="">Todos</option>
                                @foreach ($estados as $estado)
                                    <option value="{{ $estado }}" @selected($filtro === $estado)>
                                        {{ ucfirst($estado) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                            Filtrar
                        </button>
                    </form>

                    <a href="{{ route('citas.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">
                        Agendar cita
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha y hora</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Paciente</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Médico</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Especialidad</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Estado</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Motivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($citas as $cita)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $cita->fecha_hora->format('d/m/Y H:i') }}</td>
                                    <td class="px-4 py-3">{{ $cita->patient->name }}</td>
                                    <td class="px-4 py-3">{{ $cita->doctor->name }}</td>
                                    <td class="px-4 py-3">{{ $cita->especialidad }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $colores[$cita->estado] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($cita->estado) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $cita->motivo }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                        No hay citas para mostrar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Cotizaciones') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-indigo-800">
                    <div class="flex flex-col gap-4 mb-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold">Listado de cotizaciones</h3>
                        </div>

                        <a href="{{ route('cotizaciones.create') }}" class="inline-flex items-center justify-center rounded bg-indigo-700 px-4 py-2 font-bold text-white shadow transition duration-150 ease-in-out hover:bg-indigo-800">
                            + Nueva cotización
                        </a>
                    </div>

                    <div class="grid gap-4 mb-6 md:grid-cols-3">
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                            <p class="text-sm text-indigo-500">Pendientes</p>
                            <p class="mt-1 text-2xl font-bold text-indigo-900">{{ $resumen['pendientes'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg border border-green-100 bg-green-50 p-4">
                            <p class="text-sm text-green-600">Aceptadas</p>
                            <p class="mt-1 text-2xl font-bold text-green-800">{{ $resumen['aceptadas'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-sm text-gray-600">Convertidas</p>
                            <p class="mt-1 text-2xl font-bold text-gray-800">{{ $resumen['convertidas'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Folio</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Cliente</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Emisión</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Vigencia</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Estado</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Total</th>
                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-indigo-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($cotizaciones as $cotizacion)
                                    @php
                                        $estadoClase = [
                                            'Borrador' => 'bg-gray-100 text-gray-700',
                                            'Creada' => 'bg-slate-100 text-slate-700',
                                            'Enviada' => 'bg-blue-100 text-blue-700',
                                            'Aceptada' => 'bg-green-100 text-green-700',
                                            'Convertida' => 'bg-indigo-100 text-indigo-700',
                                            'Rechazada' => 'bg-red-100 text-red-700',
                                            'Vencida' => 'bg-amber-100 text-amber-700',
                                        ][$cotizacion['estado']] ?? 'bg-gray-100 text-gray-700';
                                    @endphp

                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-sm font-semibold text-gray-800">{{ $cotizacion['folio'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $cotizacion['cliente'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $cotizacion['fecha_emision'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $cotizacion['vigencia'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm">
                                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $estadoClase }}">
                                                {{ $cotizacion['estado'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border-b text-right text-sm font-semibold text-gray-800">${{ number_format($cotizacion['total'], 2) }}</td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <div class="flex justify-center items-center gap-3">
                                                <a href="{{ route('cotizaciones.show', $cotizacion['id']) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                    Ver
                                                </a>

                                                @if (! in_array($cotizacion['status'] ?? null, ['borrador', 'enviada'], true))
                                                    <span class="text-gray-400 text-sm font-semibold cursor-not-allowed" aria-disabled="true">
                                                        Editar
                                                    </span>
                                                @else
                                                    <a href="{{ route('cotizaciones.edit', $cotizacion['id']) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                        Editar
                                                    </a>
                                                @endif

                                                <form action="{{ route('cotizaciones.destroy', $cotizacion['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta cotización?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-semibold">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 px-4 text-center text-sm text-gray-500">
                                            No hay cotizaciones registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

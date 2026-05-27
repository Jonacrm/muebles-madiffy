<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Pedidos') }}
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
                            <h3 class="text-lg font-bold">Pedidos generados</h3>
                        </div>

                        <a href="{{ route('cotizaciones.index') }}" class="inline-flex items-center justify-center rounded border border-indigo-200 px-4 py-2 font-semibold text-indigo-700 hover:bg-indigo-50">
                            Ver cotizaciones
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Folio</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Cliente</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Cotización origen</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Fecha</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Estado</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Total</th>
                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-indigo-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedidos as $pedido)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-sm font-semibold text-gray-800">{{ $pedido['folio'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $pedido['cliente'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $pedido['cotizacion_folio'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $pedido['fecha_pedido'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm">
                                            <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                                {{ $pedido['estado'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-4 border-b text-right text-sm font-semibold text-gray-800">${{ number_format($pedido['total'], 2) }}</td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <a href="{{ route('pedidos.show', $pedido['id']) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

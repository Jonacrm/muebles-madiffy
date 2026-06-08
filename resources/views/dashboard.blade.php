<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @php
        $estadoClase = [
            'borrador' => 'bg-gray-100 text-gray-700',
            'creada' => 'bg-slate-100 text-slate-700',
            'enviada' => 'bg-blue-100 text-blue-700',
            'aceptada' => 'bg-green-100 text-green-700',
            'convertida' => 'bg-indigo-100 text-indigo-700',
            'rechazada' => 'bg-red-100 text-red-700',
            'vencida' => 'bg-amber-100 text-amber-700',
        ];

        $metricCards = [
            [
                'label' => 'Total cotizado del mes',
                'value' => '$'.number_format($metrics['total_cotizado_mes'], 2),
                'hint' => 'Cotizaciones enviadas, aceptadas y convertidas',
                'accent' => 'border-indigo-200 bg-indigo-50 text-indigo-900',
            ],
            [
                'label' => 'Valor pedidos activos del mes',
                'value' => '$'.number_format($metrics['valor_pedidos_mes'], 2),
                'hint' => 'Pedidos pendientes y enviados',
                'accent' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            ],
            [
                'label' => 'Cotizaciones pendientes',
                'value' => number_format($metrics['cotizaciones_pendientes']),
                'hint' => 'Borrador, creada y enviada',
                'accent' => 'border-blue-200 bg-blue-50 text-blue-900',
            ],
            [
                'label' => 'Pedidos pendientes',
                'value' => number_format($metrics['pedidos_pendientes']),
                'hint' => 'Pedidos por enviar',
                'accent' => 'border-amber-200 bg-amber-50 text-amber-900',
            ],
        ];

        $alertCards = [
            [
                'label' => 'Cotizaciones por vencer',
                'value' => number_format($metrics['cotizaciones_por_vencer']),
                'hint' => 'Vencen en los próximos 3 días',
            ],
            [
                'label' => 'Cotizaciones vencidas',
                'value' => number_format($metrics['cotizaciones_vencidas']),
                'hint' => 'Requieren revisión',
            ],
            [
                'label' => 'Pedidos vencidos',
                'value' => number_format($metrics['pedidos_vencidos']),
                'hint' => 'Pedidos no enviados a tiempo',
            ],
            [
                'label' => 'Stock crítico',
                'value' => number_format($metrics['productos_bajo_stock']),
                'hint' => $metrics['productos_sin_stock'].' sin stock, límite '.$stockThreshold,
            ],
        ];
    @endphp

    <div class="py-10">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-2 px-4 sm:px-0">
                <h3 class="text-2xl font-bold text-gray-900">Resumen operativo</h3>
                <p class="text-sm text-gray-600">Indicadores principales de cotizaciones, pedidos y catálogo.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($metricCards as $card)
                    <div class="rounded-xl border p-5 shadow-sm {{ $card['accent'] }}">
                        <p class="text-sm font-semibold">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold">{{ $card['value'] }}</p>
                        <p class="mt-2 text-xs opacity-80">{{ $card['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($alertCards as $card)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-600">{{ $card['label'] }}</p>
                                <p class="mt-2 text-2xl font-bold text-indigo-900">{{ $card['value'] }}</p>
                            </div>
                            <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Alerta</span>
                        </div>
                        <p class="mt-3 text-xs text-gray-500">{{ $card['hint'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg xl:col-span-2">
                    <div class="p-6">
                        <div class="mb-4 flex items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-indigo-800">Últimas cotizaciones</h3>
                                <p class="text-sm text-gray-500">Actividad reciente según tu alcance.</p>
                            </div>
                            <a href="{{ route('cotizaciones.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">Ver todas</a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border-b px-4 py-2 text-left text-sm font-semibold text-indigo-500">Folio</th>
                                        <th class="border-b px-4 py-2 text-left text-sm font-semibold text-indigo-500">Cliente</th>
                                        <th class="border-b px-4 py-2 text-left text-sm font-semibold text-indigo-500">Estado</th>
                                        <th class="border-b px-4 py-2 text-right text-sm font-semibold text-indigo-500">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($ultimasCotizaciones as $cotizacion)
                                        <tr class="hover:bg-gray-50">
                                            <td class="border-b px-4 py-2 text-sm font-semibold text-gray-800">
                                                <a href="{{ route('cotizaciones.show', $cotizacion['id']) }}" class="text-indigo-700 hover:text-indigo-900">
                                                    {{ $cotizacion['folio'] }}
                                                </a>
                                            </td>
                                            <td class="border-b px-4 py-2 text-sm text-gray-600">{{ $cotizacion['cliente'] }}</td>
                                            <td class="border-b px-4 py-2 text-sm">
                                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $estadoClase[$cotizacion['status']] ?? 'bg-gray-100 text-gray-700' }}">
                                                    {{ $cotizacion['estado'] }}
                                                </span>
                                            </td>
                                            <td class="border-b px-4 py-2 text-right text-sm font-semibold text-gray-800">${{ number_format($cotizacion['total'], 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">No hay cotizaciones recientes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-indigo-800">Productos con bajo stock</h3>
                                <p class="text-sm text-gray-500">Activos con {{ $stockThreshold }} piezas o menos.</p>
                            </div>
                            <a href="{{ route('catalogo.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">Catálogo</a>
                        </div>

                        <div class="space-y-3">
                            @forelse ($productosBajoStock as $producto)
                                <div class="rounded-lg border border-gray-200 px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $producto['name'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $producto['sku'] ?: 'Sin SKU' }}</p>
                                        </div>
                                        <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700">
                                            {{ $producto['stock'] }} disp.
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-6 text-center text-sm text-green-800">
                                    No hay productos con bajo stock.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

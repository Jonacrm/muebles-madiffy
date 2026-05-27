<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Detalle de pedido') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-md border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-medium text-indigo-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-2xl font-bold text-indigo-900">{{ $pedido['folio'] }}</h3>
                                <span class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700">
                                    {{ $pedido['estado'] }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-gray-600">{{ $pedido['snapshot'] }}</p>
                        </div>

                        <a href="{{ route('cotizaciones.show', $pedido['cotizacion_id']) }}" class="rounded border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                            Ver cotización origen
                        </a>
                    </div>

                    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-sm text-gray-500">Cliente</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $pedido['cliente'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-sm text-gray-500">Cotización origen</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $pedido['cotizacion_folio'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-sm text-gray-500">Fecha de pedido</p>
                            <p class="mt-1 font-semibold text-gray-900">{{ $pedido['fecha_pedido'] }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 p-4">
                            <p class="text-sm text-gray-500">Total congelado</p>
                            <p class="mt-1 font-semibold text-gray-900">${{ number_format($pedido['total'], 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                        Los precios de esta tabla representan el snapshot del pedido. Si el catálogo cambia después, estos importes se mantienen.
                    </div>

                    <h3 class="mb-6 text-lg font-bold text-indigo-800">Conceptos del pedido</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">SKU</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Producto</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Descripción</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Cantidad</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Precio pactado</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Descuento</th>
                                    <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pedido['lineas'] as $linea)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $linea['sku'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm font-semibold text-gray-800">{{ $linea['producto'] }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $linea['descripcion'] }}</td>
                                        <td class="py-2 px-4 border-b text-right text-sm text-gray-600">{{ $linea['cantidad'] }}</td>
                                        <td class="py-2 px-4 border-b text-right text-sm text-gray-600">${{ number_format($linea['precio_unitario'], 2) }}</td>
                                        <td class="py-2 px-4 border-b text-right text-sm text-gray-600">${{ number_format($linea['descuento_linea'], 2) }}</td>
                                        <td class="py-2 px-4 border-b text-right text-sm font-semibold text-gray-800">${{ number_format($linea['subtotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-indigo-800">Snapshot de precios</h3>
                        <p class="mt-2 text-sm text-gray-600">Este pedido conserva los productos, cantidades, descuentos y precios pactados al convertir la cotización.</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-sm text-gray-700">
                        <h3 class="mb-4 text-lg font-bold text-indigo-800">Totales</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-semibold">${{ number_format($pedido['subtotal'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Descuento global</span>
                                <span class="font-semibold">${{ number_format($pedido['descuento_global'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Base gravable</span>
                                <span class="font-semibold">${{ number_format($pedido['base'], 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>IVA 16%</span>
                                <span class="font-semibold">${{ number_format($pedido['iva'], 2) }}</span>
                            </div>
                            <div class="border-t pt-3 flex justify-between text-base font-bold text-indigo-900">
                                <span>Total</span>
                                <span>${{ number_format($pedido['total'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('pedidos.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">
                    Volver a pedidos
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Editar cotización') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('cotizaciones.update', $cotizacion['id']) }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-indigo-800">{{ $cotizacion['folio'] }}</h3>
                                <p class="mt-1 text-sm text-gray-600">Actualiza datos generales, conceptos y estado comercial.</p>
                            </div>
                            <a href="{{ route('cotizaciones.show', $cotizacion['id']) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-900">
                                Ver detalle
                            </a>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label for="folio" class="block text-sm font-medium text-indigo-500">Folio</label>
                                <input type="text" name="folio" id="folio" value="{{ old('folio', $cotizacion['folio']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="cliente" class="block text-sm font-medium text-indigo-500">Cliente</label>
                                <input type="text" name="cliente" id="cliente" value="{{ old('cliente', $cotizacion['cliente']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="fecha_emision" class="block text-sm font-medium text-indigo-500">Fecha de emisión</label>
                                <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', $cotizacion['fecha_emision']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="vigencia" class="block text-sm font-medium text-indigo-500">Vigencia</label>
                                <input type="date" name="vigencia" id="vigencia" value="{{ old('vigencia', $cotizacion['vigencia']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="vendedor" class="block text-sm font-medium text-indigo-500">Vendedor</label>
                                <input type="text" name="vendedor" id="vendedor" value="{{ old('vendedor', $cotizacion['vendedor']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="estado" class="block text-sm font-medium text-indigo-500">Estado</label>
                                <select name="estado" id="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach (['Borrador', 'Enviada', 'Aceptada', 'Convertida', 'Rechazada', 'Vencida'] as $estado)
                                        <option @selected(old('estado', $cotizacion['estado']) === $estado)>{{ $estado }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-6 text-lg font-bold text-indigo-800">Conceptos cotizados</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border border-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Producto</th>
                                        <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Descripción</th>
                                        <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Cantidad</th>
                                        <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Precio</th>
                                        <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Descuento</th>
                                        <th class="py-2 px-4 border-b text-right text-sm font-semibold text-indigo-500">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cotizacion['lineas'] as $index => $linea)
                                        <tr>
                                            <td class="py-2 px-4 border-b text-sm text-gray-800">
                                                <input type="text" name="lineas[{{ $index }}][producto]" value="{{ $linea['producto'] }}" class="min-w-48 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="py-2 px-4 border-b text-sm text-gray-600">
                                                <input type="text" name="lineas[{{ $index }}][descripcion]" value="{{ $linea['descripcion'] }}" class="min-w-64 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                                <input type="number" name="lineas[{{ $index }}][cantidad]" value="{{ $linea['cantidad'] }}" min="1" class="w-20 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                                <input type="number" name="lineas[{{ $index }}][precio_unitario]" value="{{ $linea['precio_unitario'] }}" step="0.01" min="0" class="w-32 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
                                            <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                                <input type="number" name="lineas[{{ $index }}][descuento_linea]" value="{{ $linea['descuento_linea'] }}" step="0.01" min="0" class="w-32 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </td>
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
                            <label for="notas" class="block text-sm font-medium text-indigo-500">Notas comerciales</label>
                            <textarea name="notas" id="notas" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notas', $cotizacion['notas']) }}</textarea>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-sm text-gray-700">
                            <h3 class="mb-4 text-lg font-bold text-indigo-800">Totales</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span>Subtotal</span>
                                    <span class="font-semibold">${{ number_format($cotizacion['subtotal'], 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Descuento global</span>
                                    <span class="font-semibold">${{ number_format($cotizacion['descuento_global'], 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>IVA 16%</span>
                                    <span class="font-semibold">${{ number_format($cotizacion['iva'], 2) }}</span>
                                </div>
                                <div class="border-t pt-3 flex justify-between text-base font-bold text-indigo-900">
                                    <span>Total</span>
                                    <span>${{ number_format($cotizacion['total'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <a href="{{ route('cotizaciones.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">Cancelar</a>
                    <button type="submit" class="rounded bg-indigo-700 px-4 py-2 font-bold text-white shadow transition duration-150 ease-in-out hover:bg-indigo-800">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

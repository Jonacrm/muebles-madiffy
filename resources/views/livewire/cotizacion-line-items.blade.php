<div class="space-y-6" @if ($status === 'borrador') wire:poll.5s="refrescarPreciosDeCatalogo" @endif>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-indigo-800">Conceptos</h3>
                </div>
                <button type="button" wire:click="agregarLinea" class="rounded border border-indigo-200 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">
                    + Agregar línea
                </button>
            </div>

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
                            <th class="py-2 px-4 border-b text-center text-sm font-semibold text-indigo-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lineas as $index => $linea)
                            <tr wire:key="cotizacion-linea-{{ $index }}-{{ $linea['product_id'] ?? 'nueva' }}">
                                <td class="py-2 px-4 border-b text-sm text-gray-800">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $linea['id'] ?? '' }}">

                                    @if ($this->lineaBloqueada($linea))
                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $linea['product_id'] }}">
                                        <div class="min-w-48 rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-700">
                                            {{ $linea['producto'] }}
                                        </div>
                                    @else
                                        <select name="items[{{ $index }}][product_id]" wire:change="seleccionarProducto({{ $index }}, $event.target.value)" class="min-w-48 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                            <option value="">Selecciona un producto</option>
                                            @foreach ($productos as $producto)
                                                <option value="{{ $producto['id'] }}" @selected((string) ($linea['product_id'] ?? '') === (string) $producto['id'])>{{ $producto['name'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif

                                    @if (($linea['stock'] ?? null) !== null)
                                        <p class="mt-1 text-xs text-gray-500">Disponible: {{ $linea['stock'] }}</p>
                                    @endif

                                    @error("items.$index.product_id")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="py-2 px-4 border-b text-sm text-gray-600">
                                    <input type="text" value="{{ $linea['descripcion'] }}" class="min-w-64 rounded-md border-gray-300 bg-gray-100 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                </td>
                                <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                    <input type="number" name="items[{{ $index }}][quantity]" wire:model.live="lineas.{{ $index }}.quantity" value="{{ $linea['quantity'] }}" min="1" @if (($linea['stock'] ?? null) !== null && ! $this->lineaBloqueada($linea)) max="{{ $linea['stock'] }}" @endif class="w-20 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500" @disabled($this->lineaBloqueada($linea))>
                                    @if ($this->lineaBloqueada($linea))
                                        <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $linea['quantity'] }}">
                                    @endif
                                    @error("items.$index.quantity")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                    <span class="inline-block w-32 rounded-md border border-gray-200 bg-gray-100 px-3 py-2 text-right text-sm text-gray-700">
                                        ${{ number_format((float) $linea['unit_price'], 2) }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border-b text-right text-sm text-gray-600">
                                    <input type="number" name="items[{{ $index }}][line_discount]" wire:model.live="lineas.{{ $index }}.line_discount" value="{{ $linea['line_discount'] }}" step="0.01" min="0" class="w-32 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:text-gray-500" @disabled($this->lineaBloqueada($linea))>
                                    @if ($this->lineaBloqueada($linea))
                                        <input type="hidden" name="items[{{ $index }}][line_discount]" value="{{ $linea['line_discount'] }}">
                                    @endif
                                    @error("items.$index.line_discount")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="py-2 px-4 border-b text-right text-sm font-semibold text-gray-800">
                                    ${{ number_format($this->subtotalLinea($linea), 2) }}
                                </td>
                                <td class="py-2 px-4 border-b text-center">
                                    @if ($this->lineaBloqueada($linea))
                                        <span class="text-sm font-semibold text-gray-400">Bloqueada</span>
                                    @else
                                        <button type="button" wire:click="quitarLinea({{ $index }})" class="text-sm font-semibold text-red-600 hover:text-red-900">
                                            Eliminar
                                        </button>
                                    @endif
                                </td>
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
                <h3 class="text-lg font-bold text-indigo-800">Cambios de precio</h3>

                @if ($status === 'borrador')
                    @php($cambios = $this->cambiosPrecio())

                    @if ($cambios === [])
                        <p class="mt-4 text-sm text-gray-600">No hay cambios de precio del catálogo para esta cotización en borrador.</p>
                    @else
                        <ul class="mt-4 space-y-2 text-sm text-gray-700">
                            @foreach ($cambios as $cambio)
                                <li class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                                    {{ $cambio['producto'] }} cambió de ${{ number_format($cambio['precio_anterior'], 2) }} a ${{ number_format($cambio['precio_actual'], 2) }}.
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p class="mt-4 text-sm text-gray-600">Los precios de esta cotización se conservan como snapshot y no se actualizan desde catálogo.</p>
                @endif
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-sm text-gray-700">
                <h3 class="mb-4 text-lg font-bold text-indigo-800">Totales</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span class="font-semibold">${{ number_format($this->subtotal(), 2) }}</span>
                    </div>
                    <div>
                        <label for="discount_global" class="block text-sm font-medium text-gray-700">Descuento global</label>
                        <input type="number" step="0.01" min="0" name="discount_global" id="discount_global" wire:model.live="discount_global" value="{{ $discount_global }}" class="mt-1 block w-full rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('discount_global')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-between">
                        <span>IVA 16%</span>
                        <span class="font-semibold">${{ number_format($this->iva(), 2) }}</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-base font-bold text-indigo-900">
                        <span>Total</span>
                        <span>${{ number_format($this->total(), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

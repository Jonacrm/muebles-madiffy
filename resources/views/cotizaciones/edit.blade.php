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
                                <label for="client_id" class="block text-sm font-medium text-indigo-500">Cliente</label>
                                <select name="client_id" id="client_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Selecciona un cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected(old('client_id', $cotizacion['client_id']) == $cliente->id)>{{ $cliente->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="fecha_emision" class="block text-sm font-medium text-indigo-500">Fecha de emisión</label>
                                <input type="date" name="fecha_emision" id="fecha_emision" value="{{ old('fecha_emision', $cotizacion['fecha_emision']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="expires_at" class="block text-sm font-medium text-indigo-500">Vigencia</label>
                                <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $cotizacion['expires_at'] ?? $cotizacion['vigencia']) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="user_id" class="block text-sm font-medium text-indigo-500">Vendedor</label>
                                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id', $cotizacion['user_id'] ?? auth()->id()) }}">
                                <input type="text" value="{{ $cotizacion['vendedor'] ?? auth()->user()->name }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-indigo-500">Estado</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach (['borrador' => 'Borrador', 'enviada' => 'Enviada', 'aceptada' => 'Aceptada', 'rechazada' => 'Rechazada', 'vencida' => 'Vencida'] as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('status', $cotizacion['status'] ?? strtolower($cotizacion['estado'])) === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </div>
                </div>

                @livewire('cotizacion-line-items', [
                    'productosIniciales' => $productos,
                    'lineasIniciales' => old('items', $cotizacion['lineas']),
                    'descuentoGlobalInicial' => old('discount_global', $cotizacion['discount_global'] ?? $cotizacion['descuento_global']),
                    'notasIniciales' => old('notas', $cotizacion['notas']),
                ], key('cotizacion-edit-line-items-'.$cotizacion['id']))

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

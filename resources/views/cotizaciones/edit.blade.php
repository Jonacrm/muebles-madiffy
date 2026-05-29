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
                                <input type="date" id="fecha_emision" value="{{ $cotizacion['fecha_emision'] }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                            </div>

                            <div x-data="{ days: '{{ old('validity_days', $cotizacion['validity_days']) }}', base: '{{ now()->toDateString() }}', expires() { const date = new Date(`${this.base}T00:00:00`); date.setDate(date.getDate() + Number(this.days)); return date.toISOString().slice(0, 10); } }">
                                <label for="validity_days" class="block text-sm font-medium text-indigo-500">Vigencia</label>
                                <select name="validity_days" id="validity_days" x-model="days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="3">3 días</option>
                                    <option value="7">7 días</option>
                                    <option value="14">14 días</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Caducidad actual: {{ $cotizacion['expires_at'] }}. Nueva caducidad: <span x-text="expires()"></span></p>
                            </div>

                            <div>
                                <label for="user_id" class="block text-sm font-medium text-indigo-500">Vendedor</label>
                                <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id', $cotizacion['user_id'] ?? auth()->id()) }}">
                                <input type="text" value="{{ $cotizacion['vendedor'] ?? auth()->user()->name }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-indigo-500">Estado</label>
                                <input type="hidden" name="status" value="{{ $cotizacion['status'] }}">
                                <div class="mt-1 rounded-md border border-gray-300 bg-gray-100 px-3 py-2 text-gray-700">{{ $cotizacion['estado'] }}</div>
                            </div>

                        </div>
                    </div>
                </div>

                @livewire('cotizacion-line-items', [
                    'productosIniciales' => $productos,
                    'lineasIniciales' => old('items', $cotizacion['lineas']),
                    'descuentoGlobalInicial' => old('discount_global', $cotizacion['discount_global'] ?? $cotizacion['descuento_global']),
                    'status' => $cotizacion['status'],
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

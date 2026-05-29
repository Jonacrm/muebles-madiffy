<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Producto') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ route('catalogo.update', $catalogoId) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="sku" class="block text-sm font-medium text-indigo-500">SKU</label>
                            <input type="text" id="sku" value="{{ $producto->sku ?? 'Sin SKU' }}" class="mt-1 block w-full border-gray-300 bg-gray-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                            <p class="mt-1 text-xs text-gray-500">El SKU se bloquea después de crear el producto.</p>
                        </div>

                        <div>
                            <label for="name" class="block text-sm font-medium text-indigo-500">Nombre del producto</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $producto->name ?? 'Mesa de comedor') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div>
                            <label for="material" class="block text-sm font-medium text-indigo-500">Material</label>
                            <input type="text" name="material" id="material" value="{{ old('material', $producto->material ?? 'Madera') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-indigo-500">Descripción</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $producto->description ?? 'Mesa rectangular para comedor') }}</textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="unit_price" class="block text-sm font-medium text-indigo-500">Precio unitario</label>
                                <input type="text" name="unit_price" id="unit_price" value="{{ old('unit_price', number_format((float) ($producto->unit_price ?? 0), 2)) }}" inputmode="decimal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label for="stock" class="block text-sm font-medium text-indigo-500">Stock</label>
                                <input type="number" name="stock" id="stock" value="{{ old('stock', $producto->stock ?? 0) }}" min="0" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>
                        </div>

                        <label class="inline-flex items-center">
                            <input type="checkbox" name="active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('active', $producto->active ?? true))>
                            <span class="ml-2 text-sm text-gray-700">Producto activo</span>
                        </label>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('catalogo.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out shadow">
                                Actualizar producto
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

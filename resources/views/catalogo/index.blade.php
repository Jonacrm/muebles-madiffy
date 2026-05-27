<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Catálogo') }}
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
                    
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold">Listado de productos</h3>
                        <a href="{{ route('catalogo.create') }}" class="bg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out shadow">
                            + Nuevo producto
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">SKU</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Nombre</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Material</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Descripción</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Precio unitario</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Stock</th>
                                    <th class="py-2 px-4 border-b text-left text-sm font-semibold text-indigo-500">Activo</th>

                                    <th class="py-2 px-4 border-b text-center text-sm font-semibold text-indigo-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productos as $producto)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $producto->sku ?? 'Sin SKU' }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-800">{{ $producto->name }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $producto->material ?? 'Sin material' }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $producto->description ?? 'Sin descripción' }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">${{ number_format($producto->unit_price, 2) }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $producto->stock ?? 0 }}</td>
                                        <td class="py-2 px-4 border-b text-sm text-gray-600">{{ $producto->active ? 'Sí' : 'No' }}</td>
                                        <td class="py-2 px-4 border-b text-center">
                                            <div class="flex justify-center items-center">
                                                <a href="{{ route('catalogo.edit', $producto) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                    Editar
                                                </a>

                                                <form action="{{ route('catalogo.destroy', $producto) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
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
                                        <td colspan="8" class="py-6 px-4 text-center text-sm text-gray-500">
                                            No hay productos registrados.
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

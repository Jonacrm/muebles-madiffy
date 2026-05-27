<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-indigo-900 leading-tight">
            {{ __('Catálogo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">SKU-001</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-800">Mesa de comedor</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">Madera</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">Mesa rectangular para comedor</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">$4,500.00</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">10</td>
                                    <td class="py-2 px-4 border-b text-sm text-gray-600">Sí</td>
                                    <td class="py-2 px-4 border-b text-center">
                                        <div class="flex justify-center items-center">
                                            <a href="{{ route('catalogo.edit', 1) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">
                                                Editar
                                            </a>

                                            <form action="{{ route('catalogo.destroy', 1) }}" method="POST" class="inline ml-2" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-semibold">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cliente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form action="{{ route('clientes.update', $cliente->id ?? 1) }}" method="POST" class="space-y-4">
                        @csrf 
                        @method('PUT')
                        
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-indigo-500">Nombre de la empresa/cliente</label>
                            <input type="text" name="nombre" id="nombre" value="{{ $cliente->nombre ?? 'Empresa de Prueba' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="correo" class="block text-sm font-medium text-indigo-500">Correo electrónico</label>
                            <input type="email" name="correo" id="correo" value="{{ $cliente->correo ?? 'prueba@test.com' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label for="telefono" class="block text-sm font-medium text-indigo-500">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ $cliente->telefono ?? '555-1234' }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('clientes.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bbg-indigo-700 hover:bg-indigo-800 text-white font-bold py-2 px-4 rounded">
                                Actualizar cliente
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
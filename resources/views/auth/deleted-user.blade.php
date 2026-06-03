<x-guest-layout>
    <div class="text-center">
        <h1 class="text-xl font-semibold text-gray-900">
            Tu cuenta ya no está disponible
        </h1>

        <p class="mt-3 text-sm text-gray-600">
            La cuenta asociada a esta sesión fue eliminada. Cierra esta sesión para poder ingresar con otro usuario.
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf

            <x-primary-button>
                Cerrar sesión
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>

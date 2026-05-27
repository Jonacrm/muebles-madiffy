<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('clientes.index', [
            'clientes' => Client::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Client::create($this->validatedData($request));

        return redirect()->route('clientes.index')->with('status', 'Cliente guardado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        return redirect()->route('clientes.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $cliente = Client::findOrFail($id);

        return view('clientes.edit', [
            'clienteId' => $id,
            'cliente' => $cliente,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $cliente = Client::findOrFail($id);
        $cliente->update($this->validatedData($request));

        return redirect()->route('clientes.index')->with('status', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $cliente = Client::findOrFail($id);

        if ($cliente->quotations()->exists()) {
            return redirect()->route('clientes.index')->with('status', 'No se puede eliminar un cliente con cotizaciones registradas.');
        }

        $cliente->delete();

        return redirect()->route('clientes.index')->with('status', 'Cliente eliminado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'rfc' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);
    }
}

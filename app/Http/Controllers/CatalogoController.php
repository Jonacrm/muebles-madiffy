<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        Gate::authorize('ver-catalogo');

        return view('catalogo.index', [
            'productos' => Product::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        Gate::authorize('gestionar-catalogo');

        return view('catalogo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('gestionar-catalogo');
        $data = $this->validatedData($request);
        $data['active'] = $request->boolean('active');

        Product::create($data);

        return redirect()->route('catalogo.index')->with('status', 'Producto guardado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RedirectResponse
    {
        Gate::authorize('gestionar-catalogo');

        return redirect()->route('catalogo.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        Gate::authorize('gestionar-catalogo');
        $producto = Product::findOrFail($id);

        return view('catalogo.edit', [
            'catalogoId' => $id,
            'producto' => $producto,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        Gate::authorize('gestionar-catalogo');
        $producto = Product::findOrFail($id);
        $data = $this->validatedData($request, $producto->id);
        $data['active'] = $request->boolean('active');
        unset($data['sku']);

        $producto->update($data);

        return redirect()->route('catalogo.index')->with('status', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        Gate::authorize('gestionar-catalogo');
        $producto = Product::findOrFail($id);

        if ($producto->quotationItems()->exists() || $producto->orderItems()->exists()) {
            return redirect()->route('catalogo.index')->with('status', 'No se puede eliminar un producto usado en cotizaciones.');
        }

        $producto->delete();

        return redirect()->route('catalogo.index')->with('status', 'Producto eliminado correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?int $productId = null): array
    {
        $request->merge([
            'unit_price' => $this->normalizarNumero($request->input('unit_price', 0)),
            'stock' => (int) $this->normalizarNumero($request->input('stock', 0)),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ] + ($productId === null ? [
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')],
        ] : []));
    }

    private function normalizarNumero(mixed $value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return round(max((float) str_replace(',', '', (string) $value), 0), 2);
    }
}

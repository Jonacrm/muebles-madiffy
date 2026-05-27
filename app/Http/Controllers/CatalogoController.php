<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('catalogo.index', [
            'productos' => Product::latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('catalogo.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
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
        return redirect()->route('catalogo.edit', $id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
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
        $producto = Product::findOrFail($id);
        $data = $this->validatedData($request, $producto->id);
        $data['active'] = $request->boolean('active');

        $producto->update($data);

        return redirect()->route('catalogo.index')->with('status', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
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
        return $request->validate([
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['required', 'string', 'max:255'],
            'material' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);
    }
}

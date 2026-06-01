<?php

namespace App\Http\Controllers;

use App\Models\Ruta;
use Illuminate\Http\Request;

class RutaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Consultamos todas las rutas de Zitácuaro con sus puntos usando Eloquent ORM
        $rutas = Ruta::with('puntosNavegacion')->get();

        // Si la petición es asíncrona (Axios/AJAX), retornamos un JSON estructurado
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($rutas);
        }

        // Si es una petición tradicional, retornamos la vista principal pasando los datos
        return view('welcome', compact('rutas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('rutas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación obligatoria con tokens CSRF en formularios
        $validated = $request->validate([
            'color' => 'required|string|unique:rutas,color',
        ]);

        // Insertar usando Eloquent
        $ruta = Ruta::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($ruta, 201);
        }

        return redirect()->route('rutas.index')->with('success', 'Ruta creada con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Consultar una ruta específica con sus puntos geográficos
        $ruta = Ruta::with('puntosNavegacion')->findOrFail($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($ruta);
        }

        return view('rutas.show', compact('ruta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ruta = Ruta::findOrFail($id);
        return view('rutas.edit', compact('ruta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $ruta = Ruta::findOrFail($id);

        $validated = $request->validate([
            'color' => 'required|string|unique:rutas,color,' . $ruta->id,
        ]);

        // Actualizar usando Eloquent
        $ruta->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($ruta);
        }

        return redirect()->route('rutas.index')->with('success', 'Ruta actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $ruta = Ruta::findOrFail($id);
        $ruta->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Ruta eliminada con éxito']);
        }

        return redirect()->route('rutas.index')->with('success', 'Ruta eliminada con éxito.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\RedSocial;
use Illuminate\Http\Request;

class RedSocialController extends Controller
{
    public function index()
    {
        $redes = RedSocial::withCount('registros')->latest()->get();
        return view('redes.index', compact('redes'));
    }

    public function create()
    {
        return view('redes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:redes_sociales,nombre',
            'icono' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'url_logo' => 'nullable|url|max:500',
        ]);

        RedSocial::create($request->only(['nombre', 'icono', 'color', 'url_logo']));

        return redirect()->route('redes.index')->with('success', 'Red social creada exitosamente.');
    }

    public function edit($id)
    {
        $red = RedSocial::findOrFail($id);
        return view('redes.edit', compact('red'));
    }

    public function update(Request $request, $id)
    {
        $red = RedSocial::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100|unique:redes_sociales,nombre,' . $id,
            'icono' => 'required|string|max:50',
            'color' => 'required|string|max:20',
            'url_logo' => 'nullable|url|max:500',
            'activa' => 'required|boolean',
        ]);

        $red->update($request->only(['nombre', 'icono', 'color', 'url_logo', 'activa']));

        return redirect()->route('redes.index')->with('success', 'Red social actualizada exitosamente.');
    }

    public function destroy($id)
    {
        $red = RedSocial::findOrFail($id);
        $red->delete();

        return redirect()->route('redes.index')->with('success', 'Red social eliminada exitosamente.');
    }
}

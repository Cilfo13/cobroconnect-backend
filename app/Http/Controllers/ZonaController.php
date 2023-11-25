<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zona;

class ZonaController extends Controller
{
    public function index()
    {
        $zonas = Zona::all();
        return view('zonas')->with('zonas', $zonas);
    }
    public function store(Request $request)
    {
        $zona = new Zona();
        $zona->nombre = $request->input('nombre');
        $zona->save();
        return back()->with('success', 'Cliente almacenado con exito');
    }
}
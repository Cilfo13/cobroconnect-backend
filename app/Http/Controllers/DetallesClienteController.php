<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Zona;

class DetallesClienteController extends Controller
{
    public function index(Request $request, $id_cliente)
    {
        $cliente = Cliente::find($id_cliente);
        $zonas = Zona::all();
        return view('detallesCliente')
            ->with('cliente', $cliente)
            ->with('zonas', $zonas);
    }
    public function modificarDatos(Request $request)
    {
        $cliente = Cliente::find($request->input('id_cliente'));
        $cliente->direccion = $request->input('direccion');
        $cliente->razon_social = $request->input('razon_social');
        $cliente->limiteTotal = $request->input('limite');
        $cliente->save();
        return back()->with('success', 'Datos del cliente modificados con exito');
    }
    public function cambiarZona(Request $request)
    {
        $cliente = Cliente::find($request->input('id_cliente'));
        $zonaId = (int) $request->input('zona_id');
        $cliente->zona_id = $zonaId;
        $cliente->save();
        return back()->with('success', 'Zona cambiada con exito');
    }
}
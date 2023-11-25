<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zona;
use App\Models\Cliente;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request, $id_cliente)
    {
        $user = User::find($id_cliente);
        $zonas = Zona::all();
        return view('detallesUsuario')
            ->with('user', $user)
            ->with('zonas', $zonas);
    }
    public function modificarDatos(Request $request)
    {
        $user = User::find($request->input('id_user'));
        $user->name = $request->input('nombre');
        $user->email = $request->input('nombre_usuario'); //Nombre de usuario
        $user->save();
        return back()->with('success', 'Datos del usuario modificados con exito');
    }
    public function cambiarContra(Request $request)
    {
        $user = User::find($request->input('id_user'));
        $user->password = $request->input('nuevaContra');
        $user->save();
        return back()->with('success', 'Contraseña cambiada con exito');
    }
    public function quitarZona(Request $request)
    {
        $user = User::find($request->input('id_user'));
        $zona = Zona::find($request->input('id_zona'));
        $user->zonas()->detach($zona);
        $user->save();
        return back()->with('success', 'Zona quitada con exito');

    }
    public function asignarZona(Request $request)
    {
        $user = User::find($request->input('id_user'));
        $zona = Zona::find($request->input('id_zona'));
        $user->zonas()->attach($zona);
        $user->save();
        return back()->with('success', 'Zona asignada con exito');
    }
}
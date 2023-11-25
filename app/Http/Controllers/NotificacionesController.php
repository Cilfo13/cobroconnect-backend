<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Cliente;

class NotificacionesController extends Controller
{
    public function index()
    {
        $notificaciones = Notificacion::where('leido', false)->get();
        return view('notificaciones')
            ->with('notificaciones', $notificaciones);
    }
    public function reiniciarLimites()
    {
        $clientes = Cliente::all();

        foreach ($clientes as $cliente) {
            $cliente->limiteActual = $cliente->limiteTotal;
            $cliente->save();
        }
        $notificacionesNoLeidas = Notificacion::where('leido', false)->get();
        foreach ($notificacionesNoLeidas as $notificacion) {
            $notificacion->leido = true;
            $notificacion->save();
        }
        return back()->with('success', 'Limites reiniciados');
    }
    public function historialNotificaciones()
    {
        $notificaciones = Notificacion::where('leido', true)
            ->take(200)
            ->get();
        return view('notificacionesHistorial')
            ->with('notificaciones', $notificaciones);
    }
}
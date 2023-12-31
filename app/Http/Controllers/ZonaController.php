<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zona;
use App\Models\Cobro;
use App\Models\Nota;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;

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
    public function informeGlobal(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha'))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha'))->endOfDay();
        //$userId = Auth::id();
        //$user = User::find($userId);
        $cobradores = User::whereNotIn('id', [1, 2])->get();

        $resultadoCobros = [];
        foreach ($cobradores as $user) {
            $cobros = Cobro::where('usuario_id', $user->id)
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->orderBy('fecha', 'asc')
                ->get();
            $clientes = [];
            foreach ($cobros as $cobro) {
                $cliente = $cobro->cliente;
                if (!isset($clientes[$cliente->id])) {
                    $clientes[$cliente->id] = [
                        'cliente' => $cliente,
                        'total_cobros' => 0,
                    ];
                }
                $clientes[$cliente->id]['total_cobros'] += $cobro->monto;
            }
            $clientes = array_values($clientes);

            $resultadoCobros[] = [
                'cobrador' => $user,
                'clientes' => $clientes,
            ];
        }


        //Cambiar la logica de las notas para ahorrar recursos, no hace falta ahora identificar por zona
        //ni recorrer todos los clientes
        // $zonas = $user->zonas;
        // $clientesTotales = [];
        // foreach ($zonas as $zona) {
        //     $clientesTotales = array_merge($clientesTotales, $zona->clientes->toArray());
        // }
        // $transferencias = [];
        // foreach ($clientesTotales as $cliente) {
        //     $transferenciasObtenidas = Nota::where('cliente_id', $cliente['id'])
        //         ->where('motivo', 'TRANSFERENCIA')
        //         ->whereBetween('created_at', [$fechaInicio, $fechaFin])
        //         ->orderBy('fecha', 'asc')
        //         ->get();
        //     $transferencias = array_merge($transferencias, $transferenciasObtenidas->toArray());
        // }


        $fechaActual = $fechaInicio->format('d-m');
        //dd($fechaActual);
        $pdf = PDF::loadView('informeCobrosGlobal', compact('fechaActual', 'user', 'resultadoCobros'));
        return $pdf->download('InformeCobro.pdf');
    }
}
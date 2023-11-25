<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zona;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Reporte;
use App\Models\Notificacion;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ComisionesController extends Controller
{
    public function index()
    {
        $zonas = Zona::all();
        $datosAcumulados = session('datosAcumulados');
        // Elimina los datos de la sesión después de recuperarlos (opcional)
        session()->forget('datosAcumulados');

        return view('comisiones', compact('zonas', 'datosAcumulados'));
    }
    public function buscar(Request $request)
    {
        $ZonaId = (int) $request->input('zona_id');
        $zona = Zona::find($ZonaId);
        $clientes = $zona->clientes;
        $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();

        $datosAcumulados = [];

        $comisionSUBE = (float) $request->input('comisionSUBE');
        $comisionCARGA = (float) $request->input('comisionCARGA');

        foreach ($clientes as $cliente) {
            $reportes = Reporte::where('cliente_id', $cliente->id)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->get();
            $SUBETOTAL = 0;
            $CARGAVIRTUALTOTAL = 0;
            foreach ($reportes as $reporte) {
                if ($reporte->cargasTotal) {
                    $SUBETOTAL += $reporte->cargasTotal;
                }
                if ($reporte->cargaVirtualTotal) {
                    $CARGAVIRTUALTOTAL += $reporte->cargaVirtualTotal;
                }
            }
            $comisionesSUBE = $SUBETOTAL * ($comisionSUBE / 100);
            $comisionesCARGA = $CARGAVIRTUALTOTAL * ($comisionCARGA / 100);
            $totalComision = $comisionesSUBE + $comisionesCARGA;
            $ventasTotales = $SUBETOTAL + $CARGAVIRTUALTOTAL;
            $datosAcumulados[] = [
                'cliente' => $cliente,
                'subeTotal' => $SUBETOTAL,
                'cargaVirtualTotal' => $CARGAVIRTUALTOTAL,
                'ventasTotales' => $ventasTotales,
                'comisionSUBE' => $comisionesSUBE,
                'comisionCARGA' => $comisionesCARGA,
                'totalComision' => $totalComision
            ];
        }
        //dd($datosAcumulados);
        return redirect()->route('comisiones.index')
            ->with('datosAcumulados', $datosAcumulados)
            ->withInput()
        ;
    }
    public function buscarTodas(Request $request)
    {
        $zonas = Zona::all();
        $datosAcumuladosZonas = [];
        foreach ($zonas as $zona) {
            if ($zona->nombre == 'X') {
                continue;
            }
            $clientes = $zona->clientes;
            $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
            $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();

            $datosAcumuladosClientes = [];

            $comisionSUBE = (float) $request->input('comisionSUBE');
            $comisionCARGA = (float) $request->input('comisionCARGA');

            foreach ($clientes as $cliente) {
                $reportes = Reporte::where('cliente_id', $cliente->id)
                    ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->get();
                $SUBETOTAL = 0;
                $CARGAVIRTUALTOTAL = 0;
                foreach ($reportes as $reporte) {
                    if ($reporte->cargasTotal) {
                        $SUBETOTAL += $reporte->cargasTotal;
                    }
                    if ($reporte->cargaVirtualTotal) {
                        $CARGAVIRTUALTOTAL += $reporte->cargaVirtualTotal;
                    }
                }
                $comisionesSUBE = $SUBETOTAL * ($comisionSUBE / 100);
                $comisionesCARGA = $CARGAVIRTUALTOTAL * ($comisionCARGA / 100);
                $totalComision = $comisionesSUBE + $comisionesCARGA;
                $ventasTotales = $SUBETOTAL + $CARGAVIRTUALTOTAL;
                $datosAcumuladosClientes[] = [
                    'cliente' => $cliente,
                    'subeTotal' => $SUBETOTAL,
                    'cargaVirtualTotal' => $CARGAVIRTUALTOTAL,
                    'ventasTotales' => $ventasTotales,
                    'comisionSUBE' => $comisionesSUBE,
                    'comisionCARGA' => $comisionesCARGA,
                    'totalComision' => $totalComision
                ];
            }
            $datosAcumuladosZonas[] = [
                'zona' => $zona,
                'clientes' => $datosAcumuladosClientes
            ];
        }
        //dd($datosAcumulados);
        $pdf = PDF::loadView('comisionesTodasLasZonas', compact('datosAcumuladosZonas'));
        return $pdf->download('ComisionesZonas.pdf');
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Cobro;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Nota;
use App\Models\User;

class APIController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth:api');
    // }
    public function MisClientes()
    {
        $user = Auth::user();
        $clientes = [];
        if ($user->hasRole('cobrador')) {
            $zonas = $user->zonas()->get();
            $clientes = collect();
            foreach ($zonas as $zona) {
                $clientesZona = $zona->clientes()->get();
                $clientes = $clientes->concat($clientesZona);
            }
        } else {
            $clientes = Cliente::all();
        }
        return response()->json($clientes);
    }

    public function cobrar(Request $request)
    {
        $userId = Auth::id();
        $cliente = Cliente::find((int) $request->input('id_cliente'));
        $cobro = new Cobro();
        $monto = (float) $request->input('monto');
        $cobro->monto = $monto;
        $cobro->usuario_id = (int) $userId;
        $ClienteId = (int) $request->id_cliente;
        $cobro->cliente_id = $ClienteId;
        //Formateamos la fecha y la hora
        $fechaCompleta = null;
        if ($request->input('fecha') != null) {
            $fechaCompleta = Carbon::parse($request->input('fecha'))->setTimezone('America/Argentina/Buenos_Aires');
        } else {
            $fechaCompleta = Carbon::now();
        }
        $cobro->fecha = $fechaCompleta;
        $fechaRecibida = $fechaCompleta->format('Y-m-d');
        //Por la SUBE que va a ser el unico
        $cobro->artefacto_id = 1;
        $saldo_viejo = $cliente->saldo;
        $saldo_nuevo = ((float) $saldo_viejo - (float) $request->input('monto'));
        $cobro->saldo_nuevo = $saldo_nuevo;
        $cobro->saldo_viejo = $saldo_viejo;
        $cobro->save();

        //Logica de reportes
        $reporte = Reporte::where('fecha', $fechaRecibida)
            ->where('cliente_id', $ClienteId)
            ->first();
        //Validamos si hay un reporte con esa fecha, si lo hay lo actualizamos, sino, lo creamos
        if ($reporte) {
            //Si tiene cobrosTotal, se lo sumamos, sino le asignamos ese valor
            if ($reporte->cobrosTotal) {
                $cobrosTotalActual = $reporte->cobrosTotal;
                $reporte->cobrosTotal = $cobrosTotalActual + $monto;
            } else {
                $reporte->cobrosTotal = $monto;
            }
            $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'desc')
                ->first();
            //Si hay algun reporte anterior usa su saldo_nuevo como saldo_viejo, sino usa el saldo Actual
            if ($reporteAnterior) {
                $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
            } else {
                $reporte->saldo_viejo = $cliente->saldoInicial;
            }
            $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'asc')
                ->first();
            //Si hay un reporte posterior debo ordenar todos los que haya, sino vuelvo a calcular el saldo_nuevo y guardo
            if ($primerReportePosterior) {
                $reporte->save();
                $this->acomodarSaldoNotas($fechaRecibida, $ClienteId, $reporte->saldo_viejo);
            } else {
                //dd("Calcular saldo nuevo");
                //$reporte->saldo_nuevo = $saldoActual + $monto;
                $saldoNuevo = $this->calcularSaldoNuevo($reporte, $reporte->saldo_viejo);
                $reporte->saldo_nuevo = $saldoNuevo;
                $reporte->save();
            }

        } else {
            //Si no hay un reporte que lo cree
            $reporte = new Reporte();
            $reporte->fecha = $fechaRecibida;
            $reporte->cliente_id = $ClienteId;
            $reporte->artefacto_id = 1;
            $reporte->cobrosTotal = $monto;
            $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'desc')
                ->first();
            //dd($reporteAnterior);
            if ($reporteAnterior) {
                $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
            } else {
                $reporte->saldo_viejo = $cliente->saldoInicial;
            }
            $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'asc')
                ->first();
            //Si hay un reporte posterior debo ordenar todos los que haya, sino dejo todo como esta
            if ($primerReportePosterior) {
                //Pongo cero, igual se va a actualizar mas adelante
                $reporte->saldo_nuevo = 0;
                $reporte->save();
                $this->acomodarSaldoNotas($fechaRecibida, $ClienteId, $reporte->saldo_viejo);
            } else {
                $reporte->saldo_nuevo = $reporte->saldo_viejo - $monto;
                $reporte->save();
            }
        }
        $cliente->saldo = $saldo_nuevo;
        $cliente->save();

        $response = [
            'message' => 'Se ha cobrado al cliente: ' . $cliente->id . " el monto de: " . $monto,
            'saldo_nuevo' => $saldo_nuevo,
            'status' => 'success',
        ];
        return response()->json($response);
    }
    public function anotarTransferencia(Request $request)
    {
        $userId = Auth::id();
        $cliente = Cliente::find((int) $request->input('id_cliente'));
        $monto = (float) $request->input('monto');
        $ClienteId = (int) $request->id_cliente;
        //Formateamos la fecha y la hora
        $fechaCompleta = null;
        if ($request->input('fecha') != null) {
            $fechaCompleta = Carbon::parse($request->input('fecha'))->setTimezone('America/Argentina/Buenos_Aires');
        } else {
            $fechaCompleta = Carbon::now();
        }
        $fechaRecibida = $fechaCompleta->format('Y-m-d');
        // Obtener la hora actual en formato "H:i:s" (hora:minuto:segundo)
        $horaActual = Carbon::now()->format('H:i:s');
        // Concatenar la fecha recibida con la hora actual
        $fechaHoraCompleta = $fechaRecibida . ' ' . $horaActual;
        // Crear un objeto Carbon con la fecha y hora combinadas
        $fechaHoraCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fechaHoraCompleta);
        // Ahora puedes guardar $fechaHoraCarbon en el campo 'fecha' del modelo $cobro.


        $nota = new Nota();
        $nota->fecha = $fechaHoraCarbon;
        $nota->cliente_id = $ClienteId;
        $nota->artefacto_id = 1;
        $nota->monto = $monto;
        $nota->motivo = 'TRANSFERENCIA';
        $nota->tipo = 'CREDITO';
        $saldo_viejo = $cliente->saldo;
        $saldo_nuevo = ((float) $saldo_viejo - (float) $request->input('monto'));
        $nota->saldo_viejo = $saldo_viejo;
        $nota->saldo_nuevo = $saldo_nuevo;
        $nota->save();


        //Logica de reportes
        $reporte = Reporte::where('fecha', $fechaRecibida)
            ->where('cliente_id', $ClienteId)
            ->first();
        //Validamos si hay un reporte con esa fecha, si lo hay lo actualizamos, sino, lo creamos
        if ($reporte) {
            //Si tiene cobrosTotal, se lo sumamos, sino le asignamos ese valor
            if ($reporte->notasTotal) {
                $notasTotalActual = $reporte->notasTotal;
                $reporte->notasTotal = $notasTotalActual - $monto;
            } else {
                $reporte->notasTotal = $monto;
            }
            $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'desc')
                ->first();
            //Si hay algun reporte anterior usa su saldo_nuevo como saldo_viejo, sino usa el saldo Actual
            if ($reporteAnterior) {
                $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
            } else {
                $reporte->saldo_viejo = $cliente->saldoInicial;
            }
            $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'asc')
                ->first();
            //Si hay un reporte posterior debo ordenar todos los que haya, sino vuelvo a calcular el saldo_nuevo y guardo
            if ($primerReportePosterior) {
                $reporte->save();
                $this->acomodarSaldoNotas($fechaRecibida, $ClienteId, $reporte->saldo_viejo);
            } else {
                //dd("Calcular saldo nuevo");
                //$reporte->saldo_nuevo = $saldoActual + $monto;
                $saldoNuevo = $this->calcularSaldoNuevo($reporte, $reporte->saldo_viejo);
                $reporte->saldo_nuevo = $saldoNuevo;
                $reporte->save();
            }

        } else {
            //Si no hay un reporte que lo cree
            $reporte = new Reporte();
            $reporte->fecha = $fechaRecibida;
            $reporte->cliente_id = $ClienteId;
            $reporte->artefacto_id = 1;
            $reporte->notasTotal = -$monto;
            $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'desc')
                ->first();
            //dd($reporteAnterior);
            if ($reporteAnterior) {
                $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
            } else {
                $reporte->saldo_viejo = $cliente->saldoInicial;
            }
            $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
                ->where('cliente_id', $ClienteId)
                ->orderBy('fecha', 'asc')
                ->first();
            //Si hay un reporte posterior debo ordenar todos los que haya, sino dejo todo como esta
            if ($primerReportePosterior) {
                //Pongo cero, igual se va a actualizar mas adelante
                $reporte->saldo_nuevo = 0;
                $reporte->save();
                $this->acomodarSaldoNotas($fechaRecibida, $ClienteId, $reporte->saldo_viejo);
            } else {
                $reporte->saldo_nuevo = $reporte->saldo_viejo - $monto;
                $reporte->save();
            }
        }

        $cliente->saldo = $saldo_nuevo;
        $cliente->save();
        $response = [
            'message' => 'Se ha anotado la transferencia del cliente: ' . $cliente->id . " el monto de: " . $monto,
            'saldo_nuevo' => $saldo_nuevo,
            'status' => 'success',
        ];
        return response()->json($response);
    }
    public function acomodarSaldoNotas($fecha, $id_cliente, $saldo_viejo)
    {
        $reportesPosteriores = Reporte::where('fecha', '>=', $fecha)
            ->where('cliente_id', $id_cliente)
            ->orderBy('fecha', 'asc')
            ->get();
        //dd($reportesPosteriores);
        $saldoActual = $saldo_viejo;
        if (!$reportesPosteriores->isEmpty()) {
            foreach ($reportesPosteriores as $reporte) {
                $saldo_nuevo = $this->calcularSaldoNuevo($reporte, $saldoActual);
                $reporte->saldo_nuevo = $saldo_nuevo;
                $reporte->save();
                $saldoActual = $saldo_nuevo;
            }
        }
    }
    public function calcularSaldoNuevo($reporte, $saldo_viejo)
    {
        $reporte->saldo_viejo = $saldo_viejo;
        $reporte->save();
        $saldo_nuevo = $saldo_viejo;
        if ($reporte->notasTotal) {
            $saldo_nuevo = $saldo_nuevo + $reporte->notasTotal;
        }
        if ($reporte->cargasTotal) {
            $saldo_nuevo = $saldo_nuevo + $reporte->cargasTotal;
        }
        if ($reporte->cobrosTotal) {
            $saldo_nuevo = $saldo_nuevo - $reporte->cobrosTotal;
        }
        if ($reporte->comision) {
            $saldo_nuevo = $saldo_nuevo - $reporte->comision;
        }
        if ($reporte->cargaVirtualTotal) {
            $saldo_nuevo = $saldo_nuevo + $reporte->cargaVirtualTotal;
        }
        return (float) $saldo_nuevo;
    }
    public function generarPDFRapido(Request $request)
    {
        $clienteId = (int) $request->query('id_cliente');
        $cliente = Cliente::find($clienteId);
        if ($request->query('fechaSeleccionada') == 'true') {
            $fechaInicio = Carbon::parse($request->query('startDate'))->startOfDay();
            $fechaFin = Carbon::parse($request->query('endDate'))->endOfDay();
        } else {
            $fechaInicio = Carbon::parse(now()->subDays(7))->startOfDay();
            $fechaFin = Carbon::parse(now())->endOfDay();
        }
        $reportes = Reporte::where('cliente_id', $clienteId)
            ->where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get();
        $notas = Nota::where('cliente_id', $clienteId)
            ->where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get();
        $pdf = PDF::loadView('reporte', compact('cliente', 'reportes', 'fechaInicio', 'fechaFin', 'notas'));
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="InformeCobro.pdf"');
    }
    public function generarInformeCobranzas(Request $request)
    {
        $fechaActual = "";
        if ($request->query('fechaSeleccionada') == 'true') {
            $fechaInicio = Carbon::parse($request->query('startDate'))->startOfDay();
            $fechaFin = Carbon::parse($request->query('endDate'))->endOfDay();
            $fechaActual = $fechaInicio->format('d-m');
        } else {
            $fechaInicio = Carbon::parse(now())->startOfDay();
            $fechaFin = Carbon::parse(now())->endOfDay();
            $fechaActual = Carbon::now()->format('Y-m-d');
            $fechaActual = Carbon::createFromFormat('Y-m-d', $fechaActual)->format('d-m');
        }
        $userId = Auth::id();
        $user = User::find($userId);
        $cobros = Cobro::where('usuario_id', $userId)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();

        //Logica para que no se repitan los clientes
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
        $zonas = $user->zonas;
        $clientesTotales = [];
        foreach ($zonas as $zona) {
            $clientesTotales = array_merge($clientesTotales, $zona->clientes->toArray());
        }
        $transferencias = [];
        foreach ($clientesTotales as $cliente) {
            $transferenciasObtenidas = Nota::where('cliente_id', $cliente['id'])
                ->where('motivo', 'TRANSFERENCIA')
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->orderBy('fecha', 'asc')
                ->get();
            $transferencias = array_merge($transferencias, $transferenciasObtenidas->toArray());
        }
        $pdf = PDF::loadView('informeCobros', compact('fechaActual', 'user', 'cobros', 'clientes', 'transferencias'));
        return response($pdf->output())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="InformeCobranzas.pdf"');
    }
}
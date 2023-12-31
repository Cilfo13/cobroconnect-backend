<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cobro;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Zona;
use App\Models\Nota;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Models\Reporte;
use Carbon\Carbon;

class CobroController extends Controller
{
    public function index()
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
        $zonas = Zona::all();
        return view('cobrar')
            ->with('clientes', $clientes)
            ->with('zonas', $zonas);
    }
    public function store(Request $request)
    {
        $userId = Auth::id();
        $cliente = Cliente::find((int) $request->id_cliente);
        $cobro = new Cobro();
        $monto = (float) $request->input('monto');
        $cobro->monto = $monto;
        $cobro->usuario_id = (int) $userId;
        $ClienteId = (int) $request->id_cliente;
        $cobro->cliente_id = $ClienteId;
        //Formateamos la fecha y la hora
        $fechaRecibida = $request->fecha;
        // Obtener la hora actual en formato "H:i:s" (hora:minuto:segundo)
        $horaActual = Carbon::now()->format('H:i:s');
        // Concatenar la fecha recibida con la hora actual
        $fechaHoraCompleta = $fechaRecibida . ' ' . $horaActual;
        // Crear un objeto Carbon con la fecha y hora combinadas
        $fechaHoraCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fechaHoraCompleta);
        // Ahora puedes guardar $fechaHoraCarbon en el campo 'fecha' del modelo $cobro.
        $cobro->fecha = $fechaHoraCarbon;

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
        return back()->with('success', 'Cobro asentado con exito');
    }
    public function anotarTransferencia(Request $request)
    {
        $userId = Auth::id();
        $cliente = Cliente::find((int) $request->id_cliente);
        $monto = (float) $request->input('monto');
        $ClienteId = (int) $request->id_cliente;
        //Formateamos la fecha y la hora
        $fechaRecibida = $request->fecha;
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
        return back()->with('success', 'Transferencia anotada con exito');
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
        $clienteId = (int) $request->input('id_cliente');
        $cliente = Cliente::find($clienteId);
        $fechaInicio = Carbon::parse(now()->subDays(7))->startOfDay();
        $fechaFin = Carbon::parse(now())->endOfDay();
        $reportes = Reporte::where('cliente_id', $clienteId)
            ->where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get();
        $notas = Nota::where('cliente_id', $clienteId)
            ->where('fecha', '>=', $fechaInicio)
            ->orderBy('fecha', 'asc')
            ->get();
        $pdf = PDF::loadView('reporte', compact('cliente', 'reportes', 'fechaInicio', 'fechaFin', 'notas'));
        return $pdf->download('Informe ' . $cliente->direccion . ' ' . $fechaInicio->day . '-' . $fechaInicio->month . ' a ' . $fechaFin->day . '-' . $fechaFin->month . ' emitido ' . now() . '.pdf');
    }

    public function generarPDFFecha(Request $request)
    {
        $clienteId = (int) $request->input('id_cliente');
        $cliente = Cliente::find($clienteId);
        $fechaInicio = Carbon::parse($request->input('fecha_inicio'))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha_fin'))->endOfDay();
        $reportes = Reporte::where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        $notas = Nota::where('cliente_id', $clienteId)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->orderBy('fecha', 'asc')
            ->get();
        $pdf = PDF::loadView('reporte', compact('cliente', 'reportes', 'fechaInicio', 'fechaFin', 'notas'));
        return $pdf->download('Informe ' . $cliente->direccion . ' ' . $fechaInicio->day . '-' . $fechaInicio->month . ' a ' . $fechaFin->day . '-' . $fechaFin->month . ' emitido ' . now() . '.pdf');
    }

    public function informeCobros()
    {
        $fechaActual = Carbon::now()->format('Y-m-d');
        $userId = Auth::id();
        $user = User::find($userId);
        $cobros = Cobro::where('usuario_id', $userId)
            ->whereDate('created_at', $fechaActual)
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
        //Ahora busco todas los clientes que tiene segun sus zonas
        //y busco si hay transferencias
        $zonas = $user->zonas;
        $clientesTotales = [];
        foreach ($zonas as $zona) {
            $clientesTotales = array_merge($clientesTotales, $zona->clientes->toArray());
        }
        $transferencias = [];
        foreach ($clientesTotales as $cliente) {
            $transferenciasObtenidas = Nota::where('cliente_id', $cliente['id'])
                ->where('motivo', 'TRANSFERENCIA')
                ->whereDate('created_at', $fechaActual)
                ->orderBy('fecha', 'asc')
                ->get();
            $transferencias = array_merge($transferencias, $transferenciasObtenidas->toArray());
        }
        //dd($zonas);
        //dd($transferencias);
        $fechaActual = Carbon::createFromFormat('Y-m-d', $fechaActual)->format('d-m');
        $pdf = PDF::loadView('informeCobros', compact('fechaActual', 'user', 'cobros', 'clientes', 'transferencias'));
        return $pdf->download('InformeCobro.pdf');
    }
    public function informeCobrosFecha(Request $request)
    {
        $fechaInicio = Carbon::parse($request->input('fecha'))->startOfDay();
        $fechaFin = Carbon::parse($request->input('fecha'))->endOfDay();
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
        $fechaActual = $fechaInicio->format('d-m');
        //dd($fechaActual);
        $pdf = PDF::loadView('informeCobros', compact('fechaActual', 'user', 'cobros', 'clientes', 'transferencias'));
        return $pdf->download('InformeCobro.pdf');
    }
}
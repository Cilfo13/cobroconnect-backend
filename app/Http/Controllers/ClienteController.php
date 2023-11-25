<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Nota;
use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Zona;
use App\Models\Artefacto;
use App\Models\Reporte;
use App\Models\Cobro;
use Carbon\Carbon;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::all();
        $zonas = Zona::all();
        return view('clientes')
            ->with('clientes', $clientes)
            ->with('zonas', $zonas);
    }
    public function store(Request $request)
    {
        $cliente = new Cliente();
        $cliente->id = (int) $request->input('id_cliente');
        $cliente->direccion = $request->input('direccion');
        $cliente->razon_social = $request->input('razon_social');
        $cliente->saldo = (float) $request->input('saldo');
        $cliente->saldoInicial = (float) $request->input('saldo');
        $cliente->limiteTotal = (float) $request->input('limite');
        $cliente->limiteActual = 0;
        $cliente->zona_id = (int) $request->input('zona_id');
        $cliente->save();
        $artefacto = Artefacto::find(1);
        $comision = (float) $request->input('comision');
        $cliente->artefactos()->attach($artefacto, ['comision' => $comision]);
        return back()->with('success', 'Cliente almacenado con exito');
    }

    public function storeNota(Request $request)
    {
        //- Nota de debito: En contra del cliente, se le suma al saldo total
        //- Nota de crédito: A favor del cliente, se le resta al saldo total
        $ClienteId = (int) $request->input('cliente_id');
        $cliente = Cliente::find($ClienteId);
        $nota = new Nota();
        $monto = (float) $request->input('monto');
        $saldoActual = (float) $cliente->saldo;
        $saldoNuevo = 0;
        if ($request->input('tipo') == 'DEBITO') {
            $nota->tipo = 'DEBITO';
        } elseif ($request->input('tipo') == 'CREDITO') {
            $nota->tipo = 'CREDITO';
            $monto = -$monto;
        }
        $saldoNuevo = $saldoActual + $monto;


        $nota->saldo_viejo = $saldoActual;
        $nota->saldo_nuevo = $saldoNuevo;
        $nota->cliente_id = $ClienteId;

        //$nota->fecha = $request->input('fecha');
        //Formateo la fecha
        //Formateamos la fecha y la hora
        $fechaRecibida = $request->input('fecha');
        // Obtener la hora actual en formato "H:i:s" (hora:minuto:segundo)
        $horaActual = Carbon::now()->format('H:i:s');
        // Concatenar la fecha recibida con la hora actual
        $fechaHoraCompleta = $fechaRecibida . ' ' . $horaActual;
        // Crear un objeto Carbon con la fecha y hora combinadas
        $fechaHoraCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fechaHoraCompleta);
        // Ahora puedes guardar $fechaHoraCarbon en el campo 'fecha' del modelo $cobro.
        $nota->fecha = $fechaHoraCarbon;

        //Logica de reportes
        $reporte = Reporte::where('fecha', $fechaRecibida)
            ->where('cliente_id', $ClienteId)
            ->first();
        //Validamos si hay un reporte con esa fecha, si lo hay lo actualizamos, sino, lo creamos
        if ($reporte) {
            //Si tiene notasTotal, se lo sumamos, sino le asignamos ese valor
            if ($reporte->notasTotal) {
                $notasTotalActual = $reporte->notasTotal;
                $reporte->notasTotal = $notasTotalActual + $monto;
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
            $reporte->notasTotal = $monto;
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
                $reporte->saldo_nuevo = $saldoActual + $monto;
                $reporte->save();
            }
        }
        $nota->motivo = $request->input('motivo');
        $nota->monto = $monto;
        $nota->artefacto_id = 1;
        $nota->save();
        $cliente->saldo = $saldoNuevo;
        $cliente->save();
        return back()->with('success', 'Nota almacenada con exito');
    }
    public function cobros(Request $request, $id_cliente)
    {
        $cliente = Cliente::find($id_cliente);
        $cobros = Cobro::where("cliente_id", $id_cliente)
            ->orderByDesc('created_at')
            ->take(100)
            ->get();
        return view('cobros')
            ->with('cliente', $cliente)
            ->with('cobros', $cobros);
    }
    public function modificarCobro(Request $request)
    {
        $id_cliente = $request->input('id_cliente');
        $cliente = Cliente::find($id_cliente);
        $cobro = Cobro::find($request->input('id_cobro'));
        $montoModificado = (float) $request->input('monto');
        $montoAnterior = $cobro->monto;
        //Asi la diferencia solamente la sumo, si queda negativo se resta
        $diferencia = $montoModificado - $montoAnterior;
        //Hay que modificar el saldo del cliente, los reportes, y el cobro
        $fechaRecibida = Carbon::parse($cobro->fecha)->toDateString();
        $reporte = Reporte::where("cliente_id", $id_cliente)
            ->where('fecha', '=', $fechaRecibida)
            ->first();
        //dd($diferencia);
        $cobro->monto = $cobro->monto + $diferencia;
        $cobro->save();
        $cliente->saldo = $cliente->saldo - $diferencia;
        $cliente->save();
        $reporte->cobrosTotal = $reporte->cobrosTotal + $diferencia;
        //
        $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
            ->where('cliente_id', $id_cliente)
            ->orderBy('fecha', 'desc')
            ->first();
        //Si hay algun reporte anterior usa su saldo_nuevo como saldo_viejo, sino usa el saldo Actual
        if ($reporteAnterior) {
            $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
        } else {
            $reporte->saldo_viejo = $cliente->saldoInicial;
        }
        $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
            ->where('cliente_id', $id_cliente)
            ->orderBy('fecha', 'asc')
            ->first();
        //Si hay un reporte posterior debo ordenar todos los que haya, sino vuelvo a calcular el saldo_nuevo y guardo
        if ($primerReportePosterior) {
            $reporte->save();
            $this->acomodarSaldoNotas($fechaRecibida, $id_cliente, $reporte->saldo_viejo);
        } else {
            //dd("Calcular saldo nuevo");
            //$reporte->saldo_nuevo = $saldoActual + $monto;
            $saldoNuevo = $this->calcularSaldoNuevo($reporte, $reporte->saldo_viejo);
            $reporte->saldo_nuevo = $saldoNuevo;
            $reporte->save();
        }
        return back()->with('success', 'Cobro modificado con exito');
    }

    public function eliminarCobro(Request $request)
    {
        $id_cliente = $request->input('id_cliente');
        $cliente = Cliente::find($id_cliente);
        $cobro = Cobro::find($request->input('id_cobro'));
        //Aca la diferencia le pongo el contrario del monto, osea lo que se cobro
        //Para que me lo reste
        $diferencia = -$cobro->monto;
        //Hay que modificar el saldo del cliente, los reportes, y el cobro
        $fechaRecibida = Carbon::parse($cobro->fecha)->toDateString();
        $reporte = Reporte::where("cliente_id", $id_cliente)
            ->where('fecha', '=', $fechaRecibida)
            ->first();
        //No hace falta modificar el cobro xq lo elimino
        //$cobro->monto = $cobro->monto + $diferencia;
        $cobro->delete();
        $cliente->saldo = $cliente->saldo - $diferencia;
        $cliente->save();
        $reporte->cobrosTotal = $reporte->cobrosTotal + $diferencia;
        if ($reporte->cobrosTotal == 0) {
            $reporte->cobrosTotal = null;
        }
        //
        $reporteAnterior = Reporte::where('fecha', '<', $fechaRecibida)
            ->where('cliente_id', $id_cliente)
            ->orderBy('fecha', 'desc')
            ->first();
        //Si hay algun reporte anterior usa su saldo_nuevo como saldo_viejo, sino usa el saldo Actual
        if ($reporteAnterior) {
            $reporte->saldo_viejo = $reporteAnterior->saldo_nuevo;
        } else {
            $reporte->saldo_viejo = $cliente->saldoInicial;
        }
        $primerReportePosterior = Reporte::where('fecha', '>', $fechaRecibida)
            ->where('cliente_id', $id_cliente)
            ->orderBy('fecha', 'asc')
            ->first();
        //Si hay un reporte posterior debo ordenar todos los que haya, sino vuelvo a calcular el saldo_nuevo y guardo
        if ($primerReportePosterior) {
            $reporte->save();
            $this->acomodarSaldoNotas($fechaRecibida, $id_cliente, $reporte->saldo_viejo);
        } else {
            //dd("Calcular saldo nuevo");
            //$reporte->saldo_nuevo = $saldoActual + $monto;
            $saldoNuevo = $this->calcularSaldoNuevo($reporte, $reporte->saldo_viejo);
            $reporte->saldo_nuevo = $saldoNuevo;
            $reporte->save();
        }
        return back()->with('success', 'Cobro modificado con exito');
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
}
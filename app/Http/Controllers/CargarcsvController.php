<?php

namespace App\Http\Controllers;

use App\Models\Carga;
use App\Models\cargaComision;
use App\Models\reporteComision;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use League\Csv\Reader;
use App\Models\Cliente;
use App\Models\Notificacion;
use App\Models\Reporte;
use App\Models\clienteComision;

class CargarcsvController extends Controller
{
    public function index()
    {
        return view('cargarcsv');
    }
    public function indexComision()
    {
        $reportes = reporteComision::all();
        return view('cargarcsvcomision', compact('reportes'));
    }
    public function store(Request $request)
    {
        if ($request->hasFile('csv_files')) {
            $csvFiles = $request->file('csv_files');
            $id_artefacto = (int) $request->input('id_artefacto');
            if ($id_artefacto == 2) {
                $this->storeCargaVirtual($csvFiles);
                return back()->with('success', 'Archivos de carga virtual leidos y datos guardados con exito');
            }
            $data = [];
            foreach ($csvFiles as $csvFile) {
                $csv = Reader::createFromPath($csvFile->getPathname(), 'r');
                $csv->setHeaderOffset(0);
                $firstRow = true;
                if (($handle = fopen($csvFile->getPathname(), 'r')) !== false) {
                    while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                        // Verifica si es la primera fila
                        //Porque la primera fila solo tiene los nombres de las filas
                        if ($firstRow) {
                            $firstRow = false;
                            continue; // Ignora la primera fila
                        }
                        $id_cliente = $row[2];
                        $id_usuario = $row[4]; //idDistribuidorSuperior
                        $fecha = $row[1];
                        $importeRecargas = $row[20];
                        $importeRecargasAnuladas = $row[22];
                        $importeRecargasNoAplicadas = $row[24];
                        $data[] = [
                            'csv_file' => $csvFile->getClientOriginalName(),
                            'id_cliente' => $id_cliente,
                            'id_usuario' => $id_usuario,
                            'fecha' => $fecha,
                            'importeRecargas' => $importeRecargas,
                            'importeRecargasAnuladas' => $importeRecargasAnuladas,
                            'importeRecargasNoAplicadas' => $importeRecargasNoAplicadas,
                        ];
                    }
                    fclose($handle);
                }
            }
            //dd($data);
            //Con este for recorro todos los datos procesados y los guardo
            foreach ($data as $campo) {
                $id_cliente = (int) $campo['id_cliente'];
                $cliente = Cliente::find($id_cliente);
                if ($cliente) {
                    //dd($cliente);
                    //Formateo de fecha
                    $fecha = $campo['fecha'];
                    $fechaFormateada = $fecha;
                    $id_usuario = (int) $campo['id_usuario'];
                    $importeRecargas = (float) str_replace(',', '.', $campo['importeRecargas']);
                    $importeRecargasAnuladas = (float) str_replace(',', '.', $campo['importeRecargasAnuladas']);
                    $importeRecargasNoAplicadas = (float) str_replace(',', '.', $campo['importeRecargasNoAplicadas']);

                    $saldoViejo = (float) $cliente->saldo;
                    $importeAAgregar = $importeRecargas - $importeRecargasAnuladas - $importeRecargasNoAplicadas;
                    $saldoNuevo = ($saldoViejo + $importeAAgregar);

                    $carga = new Carga();
                    $carga->cliente_id = $id_cliente;
                    $carga->monto = $importeRecargas;
                    $carga->anulacion = $importeRecargasAnuladas;
                    $carga->importe_cargas_no_aplicadas = $importeRecargasNoAplicadas;
                    $carga->artefacto_id = 1;
                    $carga->fecha = $fechaFormateada;
                    $carga->saldo_nuevo = $saldoNuevo;
                    $carga->saldo_viejo = $saldoViejo;
                    //Valido que no exista ya esa carga primero
                    $cargasCumplenRequisitos = Carga::where('fecha', '=', $fechaFormateada)
                        ->where('monto', '=', $importeRecargas)
                        ->where('anulacion', '=', $importeRecargasAnuladas)
                        ->where('importe_cargas_no_aplicadas', '=', $importeRecargasNoAplicadas)
                        ->where('cliente_id', '=', $id_cliente)
                        ->get();
                    //dd($cargasCumplenRequisitos);
                    if ($cargasCumplenRequisitos->count() > 0) {
                        //Si hay al menos un resultado continua e ignora a esta carga
                        continue;
                    }
                    //
                    $cliente->saldo = $saldoNuevo;
                    $cliente->save();

                    $carga->save();
                    // Crear un objeto Carbon con la fecha completa
                    $fechaCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fecha);
                    // Formatear la fecha para obtener solo la parte de la fecha (Año-Mes-Día)
                    $fechaRecibida = $fechaCarbon->format('Y-m-d');
                    $saldo_viejo = $saldoViejo;
                    $monto = $importeRecargas - $importeRecargasAnuladas - $importeRecargasNoAplicadas;
                    $ClienteId = $id_cliente;
                    //Logica de reportes
                    $reporte = Reporte::where('fecha', $fechaRecibida)
                        ->where('cliente_id', $ClienteId)
                        ->first();
                    //Validamos si hay un reporte con esa fecha, si lo hay lo actualizamos, sino, lo creamos
                    if ($reporte) {
                        //Si tiene cargasTotal, se lo sumamos, sino le asignamos ese valor
                        if ($reporte->cargasTotal) {
                            $cargasTotalActual = $reporte->cargasTotal;
                            $reporte->cargasTotal = $cargasTotalActual + $monto;
                        } else {
                            $reporte->cargasTotal = $monto;
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
                        $reporte->cargasTotal = $monto;
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
                            $reporte->saldo_nuevo = $saldo_viejo + $monto;
                            $reporte->save();
                        }
                    }
                }
            }
            $primerElemento = reset($data);
            $fechaArray = $primerElemento['fecha'];
            $fechaCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fechaArray);
            // Formatear la fecha para obtener solo la parte de la fecha (Año-Mes-Día)
            $fechaRecibida = $fechaCarbon->format('Y-m-d');
            //dd($fechaRecibida);
            $this->calcularNotificaciones($fechaRecibida);
            //Revisar la fecha y hacer un metodo que busque los reportes
            return back()->with('success', 'Archivos leidos y datos guardados con exito');
        } else {
            return redirect()->route('home.index');
        }
    }
    public function calcularNotificaciones($fecha)
    {
        $reportes = Reporte::where('fecha', $fecha)->get();
        //dd('Llego a noti');
        //Logica para las notificaciones
        foreach ($reportes as $reporte) {
            $cliente = $reporte->cliente;
            $cliente->limiteActual = $cliente->limiteActual - $reporte->cargasTotal;
            if ($cliente->limiteActual <= 0) {
                $limiteSobrepasado = -$cliente->limiteActual;
                $mensaje = "El cliente " . $cliente->direccion . " con id: " . $cliente->id . " ha sobrepasado su limite: " . number_format($cliente->limiteTotal, 2, ',', '.') . " por " . number_format($limiteSobrepasado, 2, ',', '.') . " ";
                $noti = new Notificacion;
                $noti->detalles = $mensaje;
                $noti->leido = false;
                $noti->save();
            } else {
                if ($cliente->limiteActual >= ($cliente->limiteTotal * 0.9)) {
                    $mensaje = "El cliente " . $cliente->direccion . " con id: " . $cliente->id . " ha llegado al 90% de su limite: " . number_format($cliente->limiteTotal, 2, ',', '.') . " ";
                    $noti = new Notificacion;
                    $noti->detalles = $mensaje;
                    $noti->leido = false;
                    $noti->save();
                }
            }
            $cliente->save();
        }
    }
    public function storeCargaVirtual($csvFiles)
    {
        $data = [];
        foreach ($csvFiles as $csvFile) {
            $csv = Reader::createFromPath($csvFile->getPathname(), 'r');
            $csv->setHeaderOffset(0);
            $firstRow = true;
            if (($handle = fopen($csvFile->getPathname(), 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                    if ($firstRow) {
                        $firstRow = false;
                        continue;
                    }
                    $fecha = $row[1];
                    $id_cliente = $row[2];
                    $id_usuario = $row[4]; //idDistribuidorSuperior
                    $importe = $row[11];
                    $id_producto = $row[7];
                    $data[] = [
                        'fecha' => $fecha,
                        'csv_file' => $csvFile->getClientOriginalName(),
                        'id_cliente' => $id_cliente,
                        'id_usuario' => $id_usuario,
                        'importe' => $importe,
                        'id_producto' => $id_producto
                    ];
                }
                fclose($handle);
            }
        }
        //Procesamiento de datos
        foreach ($data as $campo) {
            $id_cliente = (int) $campo['id_cliente'];
            $cliente = Cliente::find($id_cliente);
            if ($cliente) {
                //Formateo de fecha
                $fecha = $campo['fecha'];
                $fechaFormateada = $fecha;
                $id_usuario = (int) $campo['id_usuario'];
                $importe = (float) str_replace(',', '.', $campo['importe']);
                $id_producto = (int) $campo['id_producto'];

                $saldoViejo = (float) $cliente->saldo;
                $saldoNuevo = ($saldoViejo + $importe);

                $carga = new Carga();
                $carga->cliente_id = $id_cliente;
                $carga->monto = $importe;
                $carga->anulacion = 0;
                $carga->importe_cargas_no_aplicadas = 0;
                $carga->artefacto_id = 2;
                $carga->fecha = $fechaFormateada;
                $carga->saldo_nuevo = $saldoNuevo;
                $carga->saldo_viejo = $saldoViejo;
                //Valido que no exista ya esa carga primero
                //dd($fechaFormateada);
                $cargasCumplenRequisitos = Carga::where('fecha', '=', $fechaFormateada)
                    ->where('monto', '=', $importe)
                    ->where('cliente_id', '=', $id_cliente)
                    ->get();
                //dd($cargasCumplenRequisitos);
                if ($cargasCumplenRequisitos->count() > 0) {
                    //Si hay al menos un resultado continua e ignora a esta carga
                    continue;
                }
                if ($id_producto == 0 || $id_producto == 37) {
                    continue;
                }
                //
                $cliente->saldo = $saldoNuevo;
                $cliente->save();

                $carga->save();
                // Crear un objeto Carbon con la fecha completa
                $fechaCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $fecha);
                // Formatear la fecha para obtener solo la parte de la fecha (Año-Mes-Día)
                $fechaRecibida = $fechaCarbon->format('Y-m-d');
                $saldo_viejo = $saldoViejo;
                $monto = $importe;
                $ClienteId = $id_cliente;
                //Logica de reportes
                $reporte = Reporte::where('fecha', $fechaRecibida)
                    ->where('cliente_id', $ClienteId)
                    ->first();
                //Validamos si hay un reporte con esa fecha, si lo hay lo actualizamos, sino, lo creamos
                if ($reporte) {
                    //Si tiene cargasTotal, se lo sumamos, sino le asignamos ese valor
                    if ($reporte->cargaVirtualTotal) {
                        $cargasTotalActual = $reporte->cargaVirtualTotal;
                        $reporte->cargaVirtualTotal = $cargasTotalActual + $monto;
                    } else {
                        $reporte->cargaVirtualTotal = $monto;
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
                    $reporte->cargaVirtualTotal = $monto;
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
                        $reporte->saldo_nuevo = $saldo_viejo + $monto;
                        $reporte->save();
                    }
                }


            }
        }
    }
    public function storeComision(Request $request)
    {
        set_time_limit(12000);
        if ($request->hasFile('csv_files')) {
            $csvFiles = $request->file('csv_files');
            $data = []; //Aca se va a guardar acumulado todos los datos que haya
            //Aca va buscando todos los datos y los guarda en data
            foreach ($csvFiles as $csvFile) {
                $csv = Reader::createFromPath($csvFile->getPathname(), 'r');
                $csv->setHeaderOffset(0);
                $firstRow = true;
                if (($handle = fopen($csvFile->getPathname(), 'r')) !== false) {
                    while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                        // Verifica si es la primera fila
                        //Porque la primera fila solo tiene los nombres de las filas
                        if ($firstRow) {
                            $firstRow = false;
                            continue; // Ignora la primera fila
                        }
                        $id_cliente = $row[2];
                        $id_usuario = $row[4]; //idDistribuidorSuperior
                        $fecha = $row[1];
                        $nombreCliente = $row[3];
                        $importeRecargas = $row[20];
                        $importeRecargasAnuladas = $row[22];
                        $importeRecargasNoAplicadas = $row[24];
                        $data[] = [
                            'csv_file' => $csvFile->getClientOriginalName(),
                            'id_cliente' => $id_cliente,
                            'id_usuario' => $id_usuario,
                            'nombreCliente' => $nombreCliente,
                            'fecha' => $fecha,
                            'importeRecargas' => $importeRecargas,
                            'importeRecargasAnuladas' => $importeRecargasAnuladas,
                            'importeRecargasNoAplicadas' => $importeRecargasNoAplicadas,
                        ];
                    }
                    fclose($handle);
                }
            }
            //
            foreach ($data as $campo) {
                $id_cliente = (int) $campo['id_cliente'];
                $cliente = clienteComision::find($id_cliente);
                //Si no existe el cliente que lo cree
                if (!$cliente) {
                    $cliente = new clienteComision();
                    $cliente->id = $id_cliente;
                    $cliente->nombre = $campo['nombreCliente'];
                    $cliente->save();
                }
                $fecha = $campo['fecha'];
                $importeRecargas = (float) str_replace(',', '.', $campo['importeRecargas']);
                $importeRecargasAnuladas = (float) str_replace(',', '.', $campo['importeRecargasAnuladas']);
                $importeRecargasNoAplicadas = (float) str_replace(',', '.', $campo['importeRecargasNoAplicadas']);
                $saldoNuevo = ($importeRecargas - $importeRecargasAnuladas - $importeRecargasNoAplicadas);
                $carga = new cargaComision();
                $carga->cliente_id = $id_cliente;
                $carga->monto = $importeRecargas;
                $carga->anulacion = $importeRecargasAnuladas;
                $carga->importe_cargas_no_aplicadas = $importeRecargasNoAplicadas;
                $carga->fecha = $fecha;
                $carga->save();
                $reporte = reporteComision::where('cliente_id', $id_cliente)
                    ->first();
                //Si hay un reporte lo actualizamos, sino lo creamos
                if ($reporte) {
                    $monto = $importeRecargas - $importeRecargasAnuladas - $importeRecargasNoAplicadas;
                    $comision = $monto * 0.0015;
                    $reporte->cargasTotal = $reporte->cargasTotal + $monto;
                    $reporte->comision = $reporte->comision + $comision;
                    $reporte->save();
                } else {
                    $reporte = new reporteComision();
                    $reporte->cliente_id = $id_cliente;
                    $reporte->fecha = $fecha;
                    $monto = $importeRecargas - $importeRecargasAnuladas - $importeRecargasNoAplicadas;
                    $comision = $monto * 0.0015;
                    $reporte->cargasTotal = $reporte->cargasTotal + $monto;
                    $reporte->comision = $reporte->comision + $comision;
                    $reporte->save();
                }
            }
            set_time_limit(0);
            return back()->with('success', 'Archivos leidos y datos guardados con exito');
        }
        set_time_limit(0);
        return redirect()->route('home.index');
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
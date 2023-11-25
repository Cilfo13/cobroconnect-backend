<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    
</head>
<body>  
        <h1>Cliente: {{$cliente->direccion}} - {{$cliente->id}}</h1>
        <h2>Reporte desde {{$fechaInicio->format('d-m-Y')}} a {{$fechaFin->format('d-m-Y')}}</h2>
        <p>Fecha emitido: {{ now()->format('d-m-Y H:i:s') }}</p>
        @if (!empty($reportes))
            @if($reportes->first())
                <h3>Saldo viejo:{{ number_format($reportes->first()->saldo_viejo, 2, ',', '.')}}</h3>
                @php
                    $saldo = (float)$reportes->first()->saldo_viejo;
                @endphp
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Fecha</th>
                        <th scope="col">SUBE</th>
                        <th scope="col">Carga Virtual</th>
                        <th scope="col">Pagos</th>
                        <th scope="col">(credito/debito)</th>
                        <th scope="col">Saldo Nuevo</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportes as $reporte)
                        <tr>
                            <th scope="row">{{ \Carbon\Carbon::parse($reporte->fecha)->format('d-m') }}</th>

                            @if ($reporte->cargasTotal)
                                <td>{{number_format($reporte->cargasTotal, 2, ',', '.')}}</td>
                            @else
                                <td></td>
                            @endif
                            @if ($reporte->cargaVirtualTotal)
                                <td>{{number_format($reporte->cargaVirtualTotal, 2, ',', '.')}}</td>
                            @else
                                <td></td>
                            @endif
                            @if ($reporte->cobrosTotal)
                                <td>{{number_format($reporte->cobrosTotal, 2, ',', '.')}}</td>
                            @else
                                <td></td>
                            @endif
                            @if ($reporte->notasTotal)
                               
                                <td>{{number_format($reporte->notasTotal, 2, ',', '.')}}</td>
                               
                            @else
                                <td></td>
                            @endif

                            <td>{{number_format($reporte->saldo_nuevo, 2, ',', '.')}}</td>
                            @php
                                $saldo = (float)$reporte->saldo_nuevo;
                            @endphp
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <h3>Deuda total: {{number_format($saldo, 2, ',', '.')}}</h3>
                <h2>Notas de debito/credito:</h2>
                @foreach ($notas as $nota)
                <p>
                    {{ \Carbon\Carbon::parse($nota->fecha)->format('d-m-Y') }} : {{number_format($nota->monto, 2, ',', '.')}} - {{$nota->tipo}} 
                        @if ($nota->motivo !== null)
                        - Motivo: {{ $nota->motivo }}
                        @endif
                </p>
                @endforeach
            @else
            <h2>No hubo cobros, ventas o notas en el periodo seleccionado. Saldo viejo: {{$cliente->saldo}}</h2>
            @endif
        @else
            <h2>No hubo cobros, ventas o notas en el periodo seleccionado. Saldo viejo: {{$cliente->saldo}}</h2>
        @endif
</body>
</html>
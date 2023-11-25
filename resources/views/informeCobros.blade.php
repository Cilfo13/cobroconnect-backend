<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .fila-separada {
            border-bottom: 1px solid #000; /* Borde inferior de 1 píxel de grosor y color negro */
        }

        .fila-separada:last-child {
            border-bottom: none; /* Elimina el borde inferior de la última fila */
        }
    </style>
</head>
<body>  
        <h1>Cobrador: {{$user->id}} - {{$user->name}}</h1>
        <h2>Reporte del {{$fechaActual}}</h2>
        <p>Fecha emitido: {{ now()->format('d-m-Y H:i:s') }}</p>
        @php
            $totalCobrado = 0;   
        @endphp
        @if (!empty($clientes))
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Id</th>
                    <th scope="col">Direccion</th>
                    <th scope="col">Cobro</th>
                </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $arreglo)
                    <tr class="fila-separada">
                        <td>{{$arreglo['cliente']->id}}</td>
                        <td>{{$arreglo['cliente']->direccion}}</td>
                        <td>{{number_format($arreglo['total_cobros'], 2, ',', '.')}}</td>
                        @php
                            $totalCobrado = $totalCobrado + $arreglo['total_cobros']   
                        @endphp
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <h3>Total: {{number_format($totalCobrado, 2, ',', '.')}}</h3>
        @else
            <h2>No hubo cobros en el periodo seleccionado</h2>
        @endif
        @if (!empty($transferencias))
            <h2>Transfencias durante el periodo</h2>
            @foreach ($transferencias as $transferencia)
                <h3>Id {{$transferencia['cliente_id']}} : {{number_format($transferencia['monto'], 2, ',', '.')}}</h3>
            @endforeach
        @endif
</body>
</html>
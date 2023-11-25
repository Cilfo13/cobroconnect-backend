<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>COMISIONES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .fila-separada {
            border-bottom: 1px solid #000; /* Borde inferior de 1 píxel de grosor y color negro */
        }

        .fila-separada:last-child {
            border-bottom: none; /* Elimina el borde inferior de la última fila */
        }
        .custom-table {
        font-size: 10px; /* Ajusta el tamaño de fuente según sea necesario */
        }

        .custom-table th,
        .custom-table td {
            font-size: 10px; /* Ajusta el tamaño de fuente según sea necesario */
        }
    </style>
</head>
<body>  
        <h1>Comisiones y ventas de todas las zonas</h1>
       
        @if (!empty($datosAcumuladosZonas))
            @foreach ($datosAcumuladosZonas as $datosZona)
                <h3>Zona: {{ $datosZona['zona']->nombre }}</h3>
                @if($datosZona['clientes'])
                <table class="table table-bordered custom-table">
                    <thead>
                        <tr>
                            <th class="border " scope="col">Id</th>
                            <th class="border " scope="col">Direccion</th>
                            <th class="border " scope="col">Venta SUBE</th>
                            <th class="border" scope="col">Venta Carga Virtual</th>
                            <th class="border " scope="col">Venta Total</th>
                            <th class="border " scope="col">comision SUBE</th>
                            <th class="border " scope="col">comision Carga Virtual</th>
                            <th class="border " scope="col">comision Total</th>
                        </tr>
                    </thead>
                    @php
                        $totalSUBE = 0;
                        $totalCargaVirtual = 0;
                        $totalVentas = 0;
                        $totalComisionSUBE = 0;
                        $totalComisionCargaVirtual = 0;
                        $totalComision = 0;
                    @endphp
                    <tbody class="fila-separada">
                        @foreach($datosZona['clientes'] as $datos)
                            <tr class="fila-separada">
                                <td class="border">{{$datos['cliente']['id']}}</td>
                                <td class="border">{{$datos['cliente']['direccion']}}</td>
                                <td class="border">{{number_format($datos['subeTotal'], 2, ',', '.')}}</td>
                                <td class="border">{{number_format($datos['cargaVirtualTotal'], 2, ',', '.')}}</td>
                                <td class="border">{{number_format($datos['ventasTotales'], 2, ',', '.')}}</td>
                                <td class="border">{{number_format($datos['comisionSUBE'], 2, ',', '.')}}</td>
                                <td class="border">{{number_format($datos['comisionCARGA'], 2, ',', '.')}}</td>
                                <td class="border">{{number_format($datos['totalComision'], 2, ',', '.')}}</td>
                            </tr>
                            @php
                                $totalSUBE += $datos['subeTotal'];
                                $totalCargaVirtual += $datos['cargaVirtualTotal'];
                                $totalVentas += $datos['ventasTotales'];
                                $totalComisionSUBE += $datos['comisionSUBE'];
                                $totalComisionCargaVirtual += $datos['comisionCARGA'];
                                $totalComision += $datos['totalComision'];
                            @endphp
                        @endforeach
                        <tr class="fila-separada">
                            <td>*</td>
                            <td class="border"><b>TOTALES:</b></td>
                            <td class="border">{{number_format($totalSUBE, 2, ',', '.')}}</td>
                            <td class="border">{{number_format($totalCargaVirtual, 2, ',', '.')}}</td>
                            <td class="border">{{number_format($totalVentas, 2, ',', '.')}}</td>
                            <td class="border">{{number_format($totalComisionSUBE, 2, ',', '.')}}</td>
                            <td class="border">{{number_format($totalComisionCargaVirtual, 2, ',', '.')}}</td>
                            <td class="border">{{number_format($totalComision, 2, ',', '.')}}</td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p>No hay datos acumulados para clientes en esta zona.</p>
            @endif
                
            @endforeach
        @else
            <h2>No hubo ventas en el periodo seleccionado</h2>
        @endif
</body>
</html>
<?php
    date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria a Argentina
?>
@extends('layouts.app-master')
@section('content')
<main style="margin-top: 40px" class="container table-responsive">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle flex-shrink-0 me-2" width="24" height="24"></i>
        <strong>{{session('success')}}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <h1 class="text-center mb-10">Comisiones</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#buscarModal">
        <i class="bi bi-search"></i> CALCULAR X ZONA
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#calcularTodasLasZonas">
        <i class="bi bi-search"></i> CALCULAR TODAS LAS ZONAS
    </button>
</main>
@if ($datosAcumulados)
    <table id="tabla" class="table table-striped table-hover">
        <thead>
        <tr>
            <th scope="col">Id</th>
            <th scope="col">Direccion</th>
            <th scope="col">Venta SUBE</th>
            <th scope="col">Venta Carga Virtual</th>
            <th scope="col">Venta Total</th>
            <th scope="col">comision SUBE</th>
            <th scope="col">comision Carga Virtual</th>
            <th scope="col">comision Total</th>
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
        <tbody>
            @foreach ($datosAcumulados as $datos)
                <tr>
                    <td>{{$datos['cliente']['id']}}</td>
                    <td>{{$datos['cliente']['direccion']}}</td>
                    <td>{{number_format($datos['subeTotal'], 2, ',', '.')}}</td>
                    <td>{{number_format($datos['cargaVirtualTotal'], 2, ',', '.')}}</td>
                    <td>{{number_format($datos['ventasTotales'], 2, ',', '.')}}</td>
                    <td>{{number_format($datos['comisionSUBE'], 2, ',', '.')}}</td>
                    <td>{{number_format($datos['comisionCARGA'], 2, ',', '.')}}</td>
                    <td>{{number_format($datos['totalComision'], 2, ',', '.')}}</td>
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
            <tr>
                <td></td>
                <td>TOTALES:</td>
                <td>{{number_format($totalSUBE, 2, ',', '.')}}</td>
                <td>{{number_format($totalCargaVirtual, 2, ',', '.')}}</td>
                <td>{{number_format($totalVentas, 2, ',', '.')}}</td>
                <td>{{number_format($totalComisionSUBE, 2, ',', '.')}}</td>
                <td>{{number_format($totalComisionCargaVirtual, 2, ',', '.')}}</td>
                <td>{{number_format($totalComision, 2, ',', '.')}}</td>
            </tr>
        </tbody>
    </table>
@else
    <h4>Todavia no se ha seleccionado ningun cliente</h4>
@endif

{{-- BUSCAR MODAL --}}
<div class="modal fade" id="buscarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Buscar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('comisiones.buscar') }}">
                @csrf
                <div class="input-group mb-4 mt-2">
                  <span class="input-group-text">FECHA INICIO</span>
                  <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="input-group mb-4 mt-2">
                  <span class="input-group-text">FECHA FIN</span>
                  <input type="date" value="<?php echo date('Y-m-d'); ?>" name="fecha_fin" class="form-control" required>
                </div>
                <div class="input-group mb-3 mt-4">
                    <span class="input-group-text">ZONA</span>
                    <select style="text-transform: uppercase;" name="zona_id" required="required" class="select2 form-select select2-hidden-accessible">
                      @foreach ($zonas as $zona)
                        <option style="text-transform: uppercase;" value="{{$zona->id}}"" >Zona {{$zona->nombre}} </option>
                      @endforeach
                    </select>
                </div>
                <div class="input-group mb-2 mt-4">
                    <span class="input-group-text">COMISION SUBE</span>
                    <input type="number" name="comisionSUBE" step="any" class="form-control" required>
                </div>
                <div class="input-group mb-2 mt-4">
                    <span class="input-group-text">COMISION CARGA VIRTUAL</span>
                    <input type="number" name="comisionCARGA" step="any" class="form-control" required>
                </div>
                <div class="mb-3 modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Buscar</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- /BUSCAR MODAL --}}


{{-- CALCULAR TODAS LAS ZONAS MODAL --}}
<div class="modal fade" id="calcularTodasLasZonas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Buscar de todas las zonas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('comisiones.buscarTodas') }}">
                @csrf
                <div class="input-group mb-4 mt-2">
                  <span class="input-group-text">FECHA INICIO</span>
                  <input type="date" name="fecha_inicio" class="form-control" required>
                </div>
                <div class="input-group mb-4 mt-2">
                  <span class="input-group-text">FECHA FIN</span>
                  <input type="date" value="<?php echo date('Y-m-d'); ?>" name="fecha_fin" class="form-control" required>
                </div>
                <div class="input-group mb-2 mt-4">
                    <span class="input-group-text">COMISION SUBE</span>
                    <input type="number" name="comisionSUBE" step="any" class="form-control" required>
                </div>
                <div class="input-group mb-2 mt-4">
                    <span class="input-group-text">COMISION CARGA VIRTUAL</span>
                    <input type="number" name="comisionCARGA" step="any" class="form-control" required>
                </div>
                <div class="mb-3 modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success">Buscar</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- /CALCULAR TODAS LAS ZONAS MODAL --}}
<script>
    $(document).ready(function () {
      $('#tabla').DataTable({
        //scrollX: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="bi bi-file-earmark-pdf-fill mb-1"></i> PDF',
                className: 'btn btn-outline-danger',
                orientation: 'landscape',
                pageSize: 'LEGAL'
            },
            {
                extend: 'excelHtml5',
                text:'<i class="bi bi-file-earmark-spreadsheet mb-1"></i> EXCEL',
                className: 'btn btn-outline-success',
                autoFilter: true,
                sheetName: 'Exported data'
            }
        ]
      });
    });
    </script>
@endsection
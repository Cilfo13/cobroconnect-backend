@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-5 rounded">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle flex-shrink-0 me-2" width="24" height="24"></i>
            <strong>{{session('success')}}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <h1 class="h1 text-center mb-4">Cargar archivos para calcular la comision</h1>
        <form method="POST" action="{{ route('cargarcsvcomision.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <input type="file" class="form-control" name="csv_files[]" multiple>
              <div id="emailHelp" class="form-text">Seleccione los archivos .csv que queres subir</div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-outline-primary">Subir</button>
            </div>
        </form>
    </div>


    <table id="tabla" class="table table-striped table-hover">
        <thead>
          <tr>
            <th scope="col">Id</th>
            <th scope="col">Direccion</th>
            <th scope="col">Cargas totales</th>
            <th scope="col">Comision</th>
          </tr>
        </thead>
        <tbody>
          <?php
            date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria a Argentina
          ?>
            @foreach ($reportes as $reporte)
            <tr>
                <td>{{$reporte->cliente->id}}</td>
                <td>{{$reporte->cliente->nombre}}</td>
                <td>{{number_format($reporte->cargasTotal, 2, ',', '.')}}</td>
                <td>{{number_format($reporte->comision, 2, ',', '.')}}</td>
            </tr>
            @endforeach
        </tbody>
      </table>




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
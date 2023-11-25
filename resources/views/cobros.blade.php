
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
  <h1 class="text-center mb-10"><strong>Cobros</strong></h1>
  <h5 class="text-center mb-10">Direccion: <strong>{{$cliente->direccion}}</strong> - Id: <strong>{{$cliente->id}}</strong></h5>
  <h5 class="text-center mb-10">Saldo Actual: <strong>{{$cliente->saldo}}</strong></h5>
    <table id="tabla" class="table table-striped table-hover">
        <thead>
          <tr>
            <th scope="col">Creado</th>
            <th scope="col">Fecha asignada</th>
            <th scope="col">Monto</th>
            <th scope="col">Modificar</th>
            <th scope="col">Eliminar</th>
          </tr>
        </thead>
        <?php
        date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria a Argentina
        ?>
        <tbody>
            @foreach ($cobros as $cobro)
            <tr>
                <td>{{$cobro->created_at}}</td>
                <td>{{$cobro->fecha}}</td>
                <td>{{$cobro->monto}}</td>
                <td>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificar{{$cobro->id}}">
                      <i class="bi bi-pencil-square"></i> MODIFICAR 
                    </button>
                </td>
                <td>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminar{{$cobro->id}}">
                      <i class="bi bi-trash"></i> ELIMINAR 
                    </button>
                </td>
              </tr>

            {{-- //MODAL MODIFICAR--}}

            <div class="modal fade" id="modificar{{$cobro->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Cliente {{$cliente->id}} - {{$cliente->direccion}} - Saldo: {{$cliente->saldo}}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{ route('clientes.cobros.modificar') }}">
                            @csrf
                            <div class="input-group mt-2">
                                <span>Creado el: {{$cobro->created_at}}</span>
                            </div>
                            <div class="input-group mt-2">
                                <span>Fecha asignada: {{$cobro->fecha}}</span>
                            </div>
                            <div class="input-group mt-2 mb-2">
                                <span>Monto: {{$cobro->monto}}</span>
                            </div>
                            <div class="input-group mb-4 mt-2">
                            <span class="input-group-text">MONTO NUEVO</span>
                                <input type="number" step="any" name="monto" class="form-control" required>
                                <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                                <input type="text" hidden name="id_cobro" value="{{$cobro->id}}">
                            </div>
                            <div class="mb-3 modal-footer">
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i>MODIFICAR</button>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
              </div>
            {{-- //FINMODAL MODIFICAR--}}

            {{-- //MODAL ELIMINAR--}}
            <div class="modal fade" id="eliminar{{$cobro->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Cliente {{$cliente->id}} - {{$cliente->direccion}} - Saldo: {{$cliente->saldo}}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{ route('clientes.cobros.eliminar') }}">
                            @csrf
                            <h3>Seguro que desea eliminarlo?</h3>
                            <div class="input-group mt-2">
                                <span>Creado el: {{$cobro->created_at}}</span>
                            </div>
                            <div class="input-group mt-2">
                                <span>Fecha asignada: {{$cobro->fecha}}</span>
                            </div>
                            <div class="input-group mt-2 mb-2">
                                <span>Monto: {{$cobro->monto}}</span>
                            </div>
                            <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                            <input type="text" hidden name="id_cobro" value="{{$cobro->id}}">
                            <div class="mb-3 modal-footer">
                            <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i>ELIMINAR</button>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
              </div>
            {{-- //FINMODAL ELIMINAR--}}
            @endforeach
        </tbody>
      </table>
    
</main>

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
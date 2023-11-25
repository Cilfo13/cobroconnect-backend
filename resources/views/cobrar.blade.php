
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
  <h1 class="text-center mb-10"><strong>Clientes para cobrar</strong></h1>
    <div class="mb-3 d-flex justify-content-evenly">
      <a type="button" class="btn btn-success" href="{{route('generar.informe.cobros')}}">
          <i class="bi bi-file-earmark-pdf"></i> GENERAR INFORME DE COBROS (HOY)
      </a>
      <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#informeCobroElegirDia">
        <i class="bi bi-file-earmark-pdf"></i> GENERAR INFORME DE COBROS (ELEGIR DIA)
      </button>
    </div>
    <table id="tabla" class="table table-striped table-hover">
        <thead>
          <tr>
            <th scope="col">Cobrar</th>
            <th scope="col">Id</th>
            <th scope="col">Direccion</th>
            <th scope="col">Saldo</th>
            <th scope="col">Informes</th>
            <th scope="col">Zona</th>
            <th scope="col">Transferencia</th>
          </tr>
        </thead>
        <tbody>
          <?php
            date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria a Argentina
          ?>
            @foreach ($clientes as $cliente)
            <tr>
                <td>
                  <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal{{$cliente->id}}">
                    <i class="bi bi-database-add"></i> COBRAR 
                  </button>
                </td>
                <td>{{$cliente->id}}</td>
                <td>{{$cliente->direccion}}</td>
                <td>{{number_format($cliente->saldo, 2, ',', '.')}}</td>
                
                
                <td>
                  <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-file-earmark-pdf"></i> GENERAR
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="{{route('generar.pdf.rapido', ['id_cliente' => $cliente->id])}}">Rapido (ultimos 7 dias)</a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#fechaModal{{$cliente->id}}">Elegir fecha</a></li>
                    </ul>
                  </div>
                </td>
                <td>{{$cliente->zona->nombre}}</td>
                <td>
                  <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#transferencia{{$cliente->id}}">
                     ANOTAR TRANSFERENCIA
                  </button>
                </td>
            </tr>
            {{-- //MODAL FECHA --}}
            <div class="modal fade" id="fechaModal{{$cliente->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"> Cliente {{$cliente->id}} - {{$cliente->direccion}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <form method="post" action="{{ route('generar.pdf.fecha') }}">
                          @csrf
                          <div class="input-group mb-4 mt-2">
                              <b><span>Elegir fechas</span></b>
                          </div>
                          <div class="input-group mb-4 mt-2">
                            <span class="input-group-text">FECHA INICIO</span>
                            <input type="date" name="fecha_inicio" class="form-control" required>
                            <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                          </div>
                          <div class="input-group mb-4 mt-2">
                            <span class="input-group-text">FECHA FIN</span>
                            <input type="date" value="<?php echo date('Y-m-d'); ?>" name="fecha_fin" class="form-control" required>
                          </div>
                          <div class="mb-3 modal-footer">
                          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success">Generar</button>
                          </div>
                      </form>
                  </div>
                </div>
              </div>
            </div>
            {{-- //MODAL FECHA --}}

            {{-- //MODAL COBRAR--}}

            <div style="position: relative;" class="modal fade" id="exampleModal{{$cliente->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="exampleModalLabel">Cliente {{$cliente->id}} - {{$cliente->direccion}}</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="post" action="{{ route('cobros.store') }}">
                            @csrf
                            <div class="input-group mb-4 mt-2">
                                <b><span>Saldo actual: {{$cliente->saldo}}</span></b>
                            </div>
                            <div class="input-group mb-4 mt-2">
                            <span class="input-group-text">MONTO A COBRAR</span>
                            <input type="number" step="any" name="monto" class="form-control" required>
                            <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                            </div>
                            <div class="input-group mb-4 mt-2">
                              <span class="input-group-text">FECHA</span>
                              <input type="date" value="<?php echo date('Y-m-d'); ?>" name="fecha" class="form-control" required>
                            </div>
                            <div class="mb-3 modal-footer">
                            <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Cobrar</button>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
              </div>
                
            {{-- //FINMODAL --}}

            {{-- //MODAL TRANSFERENCIA--}}

            <div class="modal fade" id="transferencia{{$cliente->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cliente {{$cliente->id}} - {{$cliente->direccion}}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                      <form method="post" action="{{ route('cobros.anotar') }}">
                          @csrf
                          <div class="input-group mb-4 mt-2">
                              <b><span>Saldo actual: {{$cliente->saldo}}</span></b>
                          </div>
                          <div class="input-group mb-4 mt-2">
                          <span class="input-group-text">MONTO A ANOTAR</span>
                          <input type="number" step="any" name="monto" class="form-control" required>
                          <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                          </div>
                          <div class="input-group mb-4 mt-2">
                            <span class="input-group-text">FECHA</span>
                            <input type="date" value="<?php echo date('Y-m-d'); ?>" name="fecha" class="form-control" required>
                          </div>
                          <div class="input-group mb-4 mt-2">
                            <h4>*Atencion se esta por anotar una transferencia y no un cobro fisico</h4>
                          </div>
                          <div class="mb-3 modal-footer">
                          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success">ANOTAR</button>
                          </div>
                      </form>
                  </div>
                </div>
              </div>
            </div>
              
          {{-- //FINMODAL TRANSFERENCIA--}}
            @endforeach
        </tbody>
      </table>
      {{-- //MODAL FECHA INFORME COBRO--}}
      <div class="modal fade" id="informeCobroElegirDia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Generar informe de cobro</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('generar.informe.cobros.fecha') }}">
                    @csrf
                    <div class="input-group mb-4 mt-2">
                        <b><span>Elegir fecha</span></b>
                    </div>
                    <div class="input-group mb-4 mt-2">
                      <span class="input-group-text">FECHA</span>
                      <input type="date" name="fecha" class="form-control" required>
                    </div>
                    <div class="mb-3 modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Generar</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>
      {{-- //MODAL FECHA INFORME COBRO--}}
      

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
  <script>
    $(document).on('touchstart', '.modal', function (event) {
      event.stopPropagation();
    });
  </script>
@endsection
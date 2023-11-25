
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
      <h1 class="text-center mb-10"><strong>Clientes</strong></h1>
        <div class="mb-3 d-flex justify-content-evenly">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="bi bi-database-add"></i> CREAR CLIENTES 
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notaModal">
              <i class="bi bi-database-add"></i> CREAR NOTAS 
            </button>
        </div>
        <table id="tabla" class="table table-striped table-hover">
            <thead>
              <tr>
                <th scope="col">Id</th>
                <th scope="col">Direccion</th>
                <th scope="col">Razon social</th>
                <th scope="col">Zona</th>
                <th scope="col">Saldo</th>
                <th scope="col">Cobros</th>
                <th scope="col">Detalles</th>
                <th scope="col">Cobrador/es asignado/s</th>
              </tr>
            </thead>
            <?php
            date_default_timezone_set('America/Argentina/Buenos_Aires'); // Establecer la zona horaria a Argentina
            ?>
            <tbody>
                @foreach ($clientes as $cliente)
                <tr>
                    <td>{{$cliente->id}}</td>
                    <td>{{$cliente->direccion}}</td>
                    <td>{{$cliente->razon_social}}</td>
                    <td>{{$cliente->zona->nombre}}</td>
                    <td>{{$cliente->saldo}}</td>
                    <td><a class="btn btn-primary" href="{{ route('clientes.cobros', ['id_cliente' => $cliente->id]) }}">Cobros</a></td>
                    <td><a class="btn btn-primary" href="{{ route('cliente.detalles', ['id_cliente' => $cliente->id]) }}">Detalles</a></td>
                    <td>
                        @foreach ($cliente->zona->users as $cob)
                            - {{$cob->name}} -
                        @endforeach
                    </td>
                  </tr>
                @endforeach
            </tbody>
          </table>
          {{-- CLIENTE MODAL --}}
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Crear cliente</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('clientes.store') }}">
                        @csrf
                        <div class="input-group mb-2">
                          <span class="input-group-text">ID</span>
                          <input type="number" name="id_cliente" class="form-control" required>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">DIRECCION</span>
                          <input type="text" name="direccion" class="form-control" required>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">RAZON SOCIAL</span>
                          <input type="text" name="razon_social" class="form-control" required>
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
                          <span class="input-group-text">SALDO</span>
                          <input type="number" name="saldo" step="any" class="form-control" required>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">LIMITE</span>
                          <input type="number" name="limite" step="any" class="form-control" required>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">COMISION (SUBE)</span>
                          <input type="number" name="comision" step="any" class="form-control" required>
                        </div>
                        <div id="emailHelp" class="form-text mb-2" >En porcentaje: ej: 10 seria un 10%</div>
                        <div class="mb-3 modal-footer">
                          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success">Crear</button>
                        </div>
                    </form>
                </div>
              </div>
            </div>
          </div>
          {{-- /CLIENTE MODAL--}}

          {{-- NOTA MODAL --}}
          <div class="modal fade" id="notaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Crear Nota</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('notas.store') }}">
                        @csrf
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">Fecha</span>
                          <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                        </div>
                        <div class="input-group mb-3 mt-4">
                          <span class="input-group-text">Cliente</span>
                          <select style="text-transform: uppercase;" name="cliente_id" required="required" class="select2 form-select select2-hidden-accessible">
                            @foreach ($clientes as $cliente)
                              <option style="text-transform: uppercase;" value="{{$cliente->id}}" >Cliente {{$cliente->direccion}} - {{$cliente->id}}</option>
                            @endforeach
                          </select>
                        </div>
                        <div class="input-group mb-3 mt-4">
                          <span class="input-group-text">TIPO</span>
                          <select style="text-transform: uppercase;" name="tipo" required="required" class="select2 form-select select2-hidden-accessible">
                              <option value="DEBITO" >DEBITO</option>
                              <option value="CREDITO" >CREDITO</option>
                          </select>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">Monto</span>
                          <input type="number" step="any" name="monto" class="form-control" required>
                        </div>
                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">Motivo</span>
                          <input type="text" name="motivo" class="form-control">
                        </div>
                        <div class="mb-3 modal-footer">
                          <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                          <button type="submit" class="btn btn-success">Crear</button>
                        </div>
                    </form>
                </div>
              </div>
            </div>
          </div>
          {{-- /NOTA MODAL --}}



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
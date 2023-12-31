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
      <h1 class="text-center mb-10"><strong>Zonas</strong></h1>
      <div class="mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="bi bi-database-add"></i> CREAR ZONAS 
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#informeModal">
          <i class="bi bi-file-earmark-pdf"></i> GENERAR INFORME DE COBROS DE TODAS LAS ZONAS 
      </button>
      </div>
        <table id="tabla" class="table table-striped table-hover">
            <thead>
              <tr>
                <th scope="col">Nombre - Cobrador</th>
              </tr>
            </thead>
            <tbody>
                @php
                  $saldoTotal = 0;
                @endphp
                @foreach ($zonas as $zona)
                <tr>
                    <td>
                        <div class="accordion" id="accordionExample">
                            @php
                                  $saldoTotalZona = 0;
                            @endphp
                            @foreach ($zona->clientes as $cliente)
                              @php
                                  $saldoTotalZona = $saldoTotalZona + $cliente->saldo;
                                  $saldoTotal = $saldoTotal + $cliente->saldo
                              @endphp
                            @endforeach
                            <div class="accordion-item">
                              <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo{{$zona->id}}" aria-expanded="false" aria-controls="collapseTwo{{$zona->id}}">
                                  <b>Zona {{$zona->nombre}}</b>
                                  @foreach ($zona->users as $user)
                                    <span class="badge bg-primary mr-2 ml-2"> {{$user->id}} - {{$user->name}} </span>
                                  @endforeach
                                  <b>Saldo Total de Zona: {{number_format($saldoTotalZona, 2, ',', '.')}}</b>
                                </button>
                              </h2>
                              
                              <div id="collapseTwo{{$zona->id}}" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                  
                                    <table class="table">
                                      <thead>
                                        <tr>
                                          <th scope="col">Id Cliente</th>
                                          <th scope="col">Direccion</th>
                                          <th scope="col">Razon Social</th>
                                          <th scope="col">Saldo actual</th>
                                     
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @foreach ($zona->clientes as $cliente)
                                        <tr>
                                          <td>{{$cliente->id}}</td>
                                          <td>{{$cliente->direccion}}</td>
                                          <td>{{$cliente->razon_social}}</td>
                                          <td>{{number_format($cliente->saldo, 2, ',', '.')}}</td>
                                        </tr>
                                        @endforeach
                                      </tbody>
                                    </table>
                                </div>
                              </div>
                            </div>
                          </div>
                    </td>
                  </tr>
                @endforeach
            </tbody>
          </table>

          <h1>Total de todas las zonas: {{number_format($saldoTotal, 2, ',', '.')}}</h1>

          {{-- Crear Zona modal --}}
          <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Crear zonas</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('zona.store') }}">
                        @csrf

                        <div class="input-group mb-2 mt-4">
                          <span class="input-group-text">NOMBRE (sin el Zona)</span>
                          <input type="text" placeholder="Ej: A, B, C, etc" name="nombre" class="form-control" required>
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
          {{-- /Crear Zona modal --}}

          {{-- Crear Infome modal --}}
          <div class="modal fade" id="informeModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Generar informe de cobro global</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('zona.generarInforme') }}">
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
          {{-- /Crear Infome modal --}}
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
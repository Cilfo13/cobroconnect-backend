
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
  <h1 class="text-center mb-10"><strong>Usuarios</strong></h1>
    <div class="mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleModal">
            <i class="bi bi-database-add"></i> CREAR USUARIOS 
        </button>
    </div>
    <table id="tabla" class="table table-striped table-hover">
        <thead>
          <tr>
            <th scope="col">Id</th>
            <th scope="col">Nombre</th>
            <th scope="col">Detalles</th>
          </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td><a class="btn btn-primary" href="{{ route('usuario.detalles', ['id_user' => $user->id]) }}">Detalles</a></td>
                </tr>
            @endforeach
        </tbody>
      </table>

      <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Crear usuarios</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('usuarios.store') }}">
                    @csrf
                    <div class="input-group mb-2">
                      <span class="input-group-text">ID</span>
                      <input type="number" name="id_user" class="form-control" required>
                    </div>
                    <div class="input-group mb-2 mt-4">
                      <span class="input-group-text">NOMBRE</span>
                      <input type="text" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="input-group mb-3 mt-4">
                        <span class="input-group-text">ZONA</span>
                        <select style="text-transform: uppercase;" name="zona_id" required="required" class="select2 form-select select2-hidden-accessible">
                          @foreach ($zonas as $zona)
                            <option style="text-transform: uppercase;" value="{{$zona->id}}"" >Zona {{$zona->nombre}} </option>
                          @endforeach
                        </select>
                    </div>

                    <div class="input-group mb-3 mt-4">
                      <span class="input-group-text">ROL</span>
                      <select style="text-transform: uppercase;" name="rol" required="required" class="select2 form-select select2-hidden-accessible">
                          <option style="text-transform: uppercase;" value="cobrador" >COBRADOR </option>
                          <option style="text-transform: uppercase;" value="admin" >ADMIN </option>
                      </select>
                  </div>

                    <div class="input-group mb-2 mt-4">
                        <span class="input-group-text">NOMBRE DE USUARIO</span>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="input-group mb-2 mt-4">
                        <span for="exampleInputPassword1" class="input-group-text">CONTRASEÑA</span>
                        <input type="password" name="password" class="form-control" required id="exampleInputPassword1">
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
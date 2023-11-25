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

        <h1 class="h1 text-center mb-4">Cargar archivos</h1>
        <button type="button" class="btn btn-primary mb-5" data-bs-toggle="modal" data-bs-target="#notaModal">
            <i class="bi bi-database-add"></i> REINICIAR LIMITES DE LOS CLIENTES 
          </button>
        <form method="POST" action="{{ route('cargarcsv.store') }}" enctype="multipart/form-data">
            @csrf
            <select name="id_artefacto" class="form-select" aria-label="form-select">
                <option selected value="1">SUBE</option>
                <option value="2">Carga Virtual</option>
            </select>
            <div id="emailHelp" class="form-text mb-3">Seleccione el tipo que desea cargar (por el momento solo SUBE)</div>
            <div class="mb-3">
              <input type="file" class="form-control" name="csv_files[]" multiple>
              <div id="emailHelp" class="form-text">Seleccione los archivos .csv que queres subir</div>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-outline-primary">Subir</button>
            </div>
        </form>
    </div>


    {{-- NOTA MODAL --}}
    <div class="modal fade" id="notaModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Reiniciar Limites</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('notificaciones.reiniciarLimites') }}">
                    @csrf
                    <h4>Seguro que desea reiniciar los limites de los clientes?</h4>
                    <h5>(Tambien se borraran las notificaciones viejas)</h5>
                    <div class="mb-3 modal-footer">
                      <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-primary">Reiniciar</button>
                    </div>
                </form>
            </div>
          </div>
        </div>
      </div>
      {{-- /NOTA MODAL --}}
@endsection
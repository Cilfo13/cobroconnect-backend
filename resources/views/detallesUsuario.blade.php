
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
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificarUsuario">
        <i class="bi bi-pencil-square"></i> MODIFICAR DATOS DEL USUARIO
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cambiarContra">
        <i class="bi bi-pencil-square"></i> CAMBIAR CONTRASEÑA
    </button>
    <h3 class="text-center mb-10">Detalles del usuario:</h3>
    <h1 class="text-center mb-10"><strong>{{$user->name}}</strong> - usuario: <strong>{{$user->email}}</strong></h1>
    <h5 class="text-center mb-10"><strong>Zonas</strong></h5>
    <div class="d-flex flex-column justify-content-between">
        @foreach ($user->zonas as $zon)
            <div class="d-flex flex-row justify-content-between mb-50">
                <p class="text-center mb-10 mr-10">Zona {{$zon->nombre}}</p>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#quitarZona{{$zon->id}}">
                    <i class="bi bi-pencil-square"></i> QUITAR ZONA
                </button>
            </div>
            {{-- //CONFIRMAR SACAR ZONA--}}
            <div class="modal fade" id="quitarZona{{$zon->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Quitar Zona</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="post" action="{{ route('user.modificar.quitarZona') }}">
                                @csrf
                                <h5>Seguro que desea quitar la zona {{$zon->nombre}} del usuario {{$user->email}}?</h5>
                                <input type="text" hidden name="id_user" value="{{$user->id}}">
                                <input type="text" hidden name="id_zona" value="{{$zon->id}}">
                                <div class="mb-3 modal-footer">
                                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger"><i class="bi bi-pencil-square"></i>Quitar zona</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            {{-- //CONFIRMAR SACAR ZONA--}}
        @endforeach
    </div>
    <button style="margin-top:50" type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#asignarZona">
        <i class="bi bi-pencil-square"></i> Asignar Zona
    </button>
</main>

{{-- //MODAL MODIFICAR USUARIO--}}
<div class="modal fade" id="modificarUsuario" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">MODIFICAR USUARIO</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('user.modificar.datos') }}">
                @csrf
                    <div class="input-group mb-4 mt-2">
                        <span class="input-group-text">NOMBRE</span>
                        <input value="{{$user->name}}" type="text" name="nombre" class="form-control">
                    </div>
                    <div class="input-group mb-4 mt-2">
                        <span class="input-group-text">NOMBRE DE USUARIO</span>
                        <input value="{{$user->email}}" type="text" name="nombre_usuario" class="form-control">
                    </div>
                    <span>*El nombre de usuario se usará para loguearse</span>
                    <input type="text" hidden name="id_user" value="{{$user->id}}">
                    <div class="mb-3 modal-footer">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i>MODIFICAR</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- //FINMODAL MODIFICAR USUARIO--}}

{{-- //CAMBIAR CONTRASEÑA--}}
<div class="modal fade" id="cambiarContra" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">CAMBIAR CONTRASEÑA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('user.modificar.contra') }}">
                @csrf
                <div class="input-group mb-4 mt-2">
                <span class="input-group-text">Nueva Contraseña</span>
                    <input type="text" name="nuevaContra" class="form-control">
                </div>
                <input type="text" hidden name="id_user" value="{{$user->id}}">
                <div class="mb-3 modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i>MODIFICAR</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- //CAMBIAR CONTRASEÑA--}}


{{-- //MODAL ASIGNAR ZONA--}}
<div class="modal fade" id="asignarZona" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">ASIGNAR ZONA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('user.modificar.asignarZona') }}">
                @csrf
                <div class="input-group mb-3 mt-4">
                    <span class="input-group-text">ELIGE LA ZONA PARA ASIGNAR</span>
                    <select style="text-transform: uppercase;" name="id_zona" required="required" class="select2 form-select select2-hidden-accessible">
                      @foreach ($zonas as $zona)
                        <option style="text-transform: uppercase;" value="{{$zona->id}}"" >Zona {{$zona->nombre}} </option>
                      @endforeach
                    </select>
                  </div>
                <input type="text" hidden name="id_user" value="{{$user->id}}">
                <div class="mb-3 modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-pencil-square"></i>ASIGNAR ZONA</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- //ASIGNAR ZONA--}}


@endsection
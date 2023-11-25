
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
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modificarCliente">
        <i class="bi bi-pencil-square"></i> MODIFICAR DATOS DEL CLIENTE
    </button>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#cambiarZona">
        <i class="bi bi-pencil-square"></i> CAMBIAR ZONA DEL CLIENTE
    </button>
    <h3 class="text-center mb-10">Detalles del cliente:</h3>
    <h1 class="text-center mb-10"><strong>{{$cliente->direccion}}</strong> - Id: <strong>{{$cliente->id}}</strong></h1>

    <h5 class="text-center mb-10">Saldo Actual: <strong>{{$cliente->saldo}}</strong></h5>
    <h5 class="text-center mb-10">Razon Social: <strong>{{$cliente->razon_social}}</strong></h5>
    <h5 class="text-center mb-10">Zona: <strong>{{$cliente->zona->nombre}}</strong></h5>
    <h5 class="text-center mb-10">Limite Total: <strong>{{$cliente->limiteTotal}}</strong></h5>
    <h5 class="text-center mb-10">Limite actual (lo que le queda): <strong>{{$cliente->limiteActual}}</strong></h5>
    <h5 class="text-center mb-10"><strong>Cobradores: </strong></h5>
    @foreach ($cliente->zona->users as $cob)
        <h5 class="text-center mb-10">{{$cob->name}}</h5>
    @endforeach

</main>

{{-- //MODAL MODIFICAR--}}

<div class="modal fade" id="modificarCliente" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">MODIFICAR CLIENTE</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('cliente.modificar.datos') }}">
                @csrf
                <div class="input-group mb-4 mt-2">
                <span class="input-group-text">DIRECCION</span>
                    <input value="{{$cliente->direccion}}" type="text" name="direccion" class="form-control">
                </div>
                <div class="input-group mb-4 mt-2">
                <span class="input-group-text">RAZON SOCIAL</span>
                    <input value="{{$cliente->razon_social}}"  type="text" name="razon_social" class="form-control">
                </div>
                <div class="input-group mb-4 mt-2">
                <span class="input-group-text">LIMITE MAXIMO (TOTAL)</span>
                    <input value={{$cliente->limiteTotal}} type="number" step="any" name="limite" class="form-control">
                </div>
                <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
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

{{-- //MODAL CAMBIAR ZONA--}}

<div class="modal fade" id="cambiarZona" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">CAMBIAR ZONA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <form method="post" action="{{ route('cliente.modificar.zona') }}">
                @csrf
                <div class="input-group mb-3 mt-4">
                    <span class="input-group-text">ELIGE LA ZONA PARA CAMBIAR</span>
                    <select style="text-transform: uppercase;" name="zona_id" required="required" class="select2 form-select select2-hidden-accessible">
                      @foreach ($zonas as $zona)
                        <option style="text-transform: uppercase;" value="{{$zona->id}}"" >Zona {{$zona->nombre}} </option>
                      @endforeach
                    </select>
                  </div>
                <input type="text" hidden name="id_cliente" value="{{$cliente->id}}">
                <div class="mb-3 modal-footer">
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-pencil-square"></i>CAMBIAR ZONA</button>
                </div>
            </form>
        </div>
      </div>
    </div>
  </div>
{{-- //CAMBIAR ZONA--}}
@endsection
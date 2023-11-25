@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-5 rounded">
        @auth
        <h1>Bienvenido</h1>
        <p class="lead">Solo podes ver esto si estas logueado</p>
        <a class="btn btn-lg btn-primary" href="#" role="button">Iniciá por acá &raquo;</a>
        @endauth
    </div>
@endsection
@extends('layouts.app-master')

@section('content')
    <div class="bg-light p-5 rounded">
        @auth
        <h1>Bienvenido {{auth()->user()->name}}</h1>
        <a class="btn btn-lg btn-primary" href="{{route('cobros.index')}}" role="button">Ir a cobros &raquo;</a>
        @endauth
    </div>
@endsection
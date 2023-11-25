
@extends('layouts.app-master')
@section('content')
<main style="margin-top: 40px" class="container table-responsive">
   
    <h1 class="text-center mb-10">Historial de notificaciones</h1>
    <!-- Section: Timeline -->
    <section class="py-5">
        <ul class="timeline">
            @foreach ($notificaciones as $noti)
                <li class="timeline-item mb-5">
                    <h5 class="fw-bold">{{$noti->detalles}}</h5>
                    <p class="text-muted mb-2 fw-bold">{{$noti->created_at}}</p>
                </li>
            @endforeach
        </ul>
    </section>
    @if ($notificaciones->isEmpty())
            <h5>No hay notificaciones</h5>
        @endif
    <!-- Section: Timeline -->
</main>
@endsection
<header class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="{{ route('home.index') }}"><b>Cobroconnect</b></a>
    <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        @role('admin')
        <li class="nav-item">
          <a class="nav-link" href="{{route('cargarcsv.index')}}"><i class="bi bi-file-earmark-arrow-up"></i> Cargar Archivos</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-eye"></i> Visualizar
          </a>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="{{route('zona.index')}}"><i class="bi bi-geo-alt"></i> Zonas</a>
            </li>
            <li>
              <a class="dropdown-item" href="{{route('clientes.index')}}"><i class="bi bi-building"></i> Clientes</a>
            </li>
            <li>
              <a class="dropdown-item" href="{{route('usuarios.index')}}"><i class="bi bi-file-earmark-person"></i> Usuarios</a>
            </li>
          </ul>
        </li>

        {{-- <li class="nav-item">
          <a class="nav-link" href="{{route('zona.index')}}"><i class="bi bi-geo-alt"></i> Zonas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('clientes.index')}}"><i class="bi bi-building"></i> Clientes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('usuarios.index')}}"><i class="bi bi-file-earmark-person"></i> Usuarios</a>
        </li> --}}
        {{-- <li class="nav-item">
          <a class="nav-link" href="{{route('cargarcsvcomision.index')}}"><i class="bi bi-file-earmark-person"></i> Comision CSV</a>
        </li> --}}
        <li class="nav-item">
          <a class="nav-link" href="{{route('notificaciones.index')}}"><i class="bi bi-bell"></i> Notificaciones</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{route('comisiones.index')}}"><i class="bi bi-clipboard-data"></i> Comisiones</a>
        </li>
        @endrole
        <li class="nav-item">
          <a class="nav-link" href="{{route('cobros.index')}}"><i class="bi bi-arrow-down-up"></i> Cobrar</a>
        </li>
      </ul>
      <form class="d-flex align-items-center justify-content-center">
        @auth
            <h4 class="text-center my-auto font-weight-bold badge bg-primary" style="color:white;  margin-right:20px">{{auth()->user()->name}}</h4>
            <a href="{{ route('logout.perform') }}" class="btn btn-outline-danger ml-auto">Logout <i class="bi bi-box-arrow-right"></i></a>
        @endauth
      </form>
    </div>
  </div>
</header>
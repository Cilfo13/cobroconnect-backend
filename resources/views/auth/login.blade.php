@extends('layouts.auth-master')

@section('content')

<form method="post" action="{{ route('login.perform') }}" class="container w-75 mt-auto">
        <h1 class="h3 mb-3 fw-normal mt-5"> <b>LOGIN</b> </h1>
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        @include('layouts.partials.messages')
        <div class="form-group form-floating mb-3">
            <input type="text" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" required="required" autofocus>
            <label for="floatingName">Usuario</label>
            @if ($errors->has('email'))
                <span class="text-danger text-left">{{ $errors->first('email') }}</span>
            @endif
        </div>
        <div class="form-group form-floating mb-3">
            <input type="password" class="form-control" name="password" value="{{ old('password') }}" placeholder="Password" required="required">
            <label for="floatingPassword">Contraseña</label>
            @if ($errors->has('password'))
                <span class="text-danger text-left">{{ $errors->first('password') }}</span>
            @endif
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit">Iniciar sesion</button>
        
        @include('auth.partials.copy')
    </form>



@endsection
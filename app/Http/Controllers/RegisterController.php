<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    //

    public function index()
    {
        $users = User::all();
        $zonas = Zona::all();
        return view('usuarios')
            ->with('users', $users)
            ->with('zonas', $zonas);
    }
    public function store(Request $request)
    {
        $user = new User;
        $user->id = $request->id_user;
        $user->name = $request->nombre;
        $user->email = $request->username;
        $user->password = $request->password;
        $user->save();
        $user->assignRole($request->rol);
        $zona = Zona::find((int) $request->zona_id);
        $user->zonas()->attach($zona);
        return back()->with('success', 'Usuario almacenado con exito');
    }
    public function show()
    {
        if (Auth::check()) {
            return redirect()->route('home.index');
        }
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {

        $user = User::create($request->validated());
        auth()->login($user);
        return redirect('/home')->with('success', "Account successfully registered.");
        /*
        $user = new User;
         $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->setPassword($request->password);
        $user->save();
        return redirect('/asdasd')->with('success', "Account successfully registered."); */

    }
}
<?php

use App\Models\Cobro;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ZonaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    /**
     * Home Routes
     */
    /**
     * Login Routes
     */
    Route::get('/login', 'LoginController@show')->name('login');
    Route::post('/login', 'LoginController@login')->name('login.perform');

    Route::group(['middleware' => ['auth']], function () {
        Route::group(['middleware' => ['role:admin']], function () {
            //CSV
            Route::get('/cargarcsv', 'CargarcsvController@index')->name('cargarcsv.index');
            Route::post('/cargarcsv', 'CargarcsvController@store')->name('cargarcsv.store');
            //Zonas
            Route::get('/zonas', 'ZonaController@index')->name('zona.index');
            Route::post('/zonas', 'ZonaController@store')->name('zona.store');
            Route::post('/informeGlobal', 'ZonaController@informeGlobal')->name('zona.generarInforme');
            //Clientes
            Route::get('/clientes', 'ClienteController@index')->name('clientes.index');
            Route::post('/clientes', 'ClienteController@store')->name('clientes.store');
            Route::get('/cobros/{id_cliente}', 'ClienteController@cobros')->name('clientes.cobros');
            Route::post('/cobros/modificar', 'ClienteController@modificarCobro')->name('clientes.cobros.modificar');
            Route::post('/cobros/eliminar', 'ClienteController@eliminarCobro')->name('clientes.cobros.eliminar');
            //Detalles Cliente
            Route::get('/cliente/{id_cliente}', 'DetallesClienteController@index')->name('cliente.detalles');
            Route::post('/clienteModificarDatos', 'DetallesClienteController@modificarDatos')->name('cliente.modificar.datos');
            Route::post('/clienteCambiarZona', 'DetallesClienteController@cambiarZona')->name('cliente.modificar.zona');
            //Usuarios
            Route::get('/usuarios', 'RegisterController@index')->name('usuarios.index');
            Route::post('/usuarios', 'RegisterController@store')->name('usuarios.store');
            //Detalles Usuario
            Route::get('/user/{id_user}', 'UserController@index')->name('usuario.detalles');
            Route::post('/usuarioModificarDatos', 'UserController@modificarDatos')->name('user.modificar.datos');
            Route::post('/usuarioCambiarContra', 'UserController@cambiarContra')->name('user.modificar.contra');
            Route::post('/usuarioQuitarZona', 'UserController@quitarZona')->name('user.modificar.quitarZona');
            Route::post('/usuarioAsignarZona', 'UserController@asignarZona')->name('user.modificar.asignarZona');
            //Notificaciones
            Route::get('/notificaciones', 'NotificacionesController@index')->name('notificaciones.index');
            Route::post('/reiniciarLimites', 'NotificacionesController@reiniciarLimites')->name('notificaciones.reiniciarLimites');
            Route::get('/notificacionesHistorial', 'NotificacionesController@historialNotificaciones')->name('notificaciones.historial');
            //Comisiones Controller
            Route::get('/comisiones', 'ComisionesController@index')->name('comisiones.index');
            Route::post('/comisiones', 'ComisionesController@buscar')->name('comisiones.buscar');
            Route::post('/comisionesTodas', 'ComisionesController@buscarTodas')->name('comisiones.buscarTodas');
        });
        Route::get('/', 'HomeController@index')->name('home.index');
        //CSVComision
        // Route::get('/cargarcsvcomision', 'CargarcsvController@indexComision')->name('cargarcsvcomision.index');
        // Route::post('/cargarcsvcomision', 'CargarcsvController@storeComision')->name('cargarcsvcomision.store');

        //RUTAS ACCESIBLES POR LOS COBRADORES
        //Cobros
        Route::get('/cobros', 'CobroController@index')->name('cobros.index');
        Route::post('/cobros', 'CobroController@store')->name('cobros.store');
        Route::post('/cobros/anotar', 'CobroController@anotarTransferencia')->name('cobros.anotar');
        Route::get('/informe', 'CobroController@informeCobros')->name('generar.informe.cobros');
        Route::post('/informe', 'CobroController@informeCobrosFecha')->name('generar.informe.cobros.fecha');

        //
        Route::get('/generar-pdf', 'CobroController@generarPDFRapido')->name('generar.pdf.rapido');
        Route::post('/generar-pdf-fecha', 'CobroController@generarPDFFecha')->name('generar.pdf.fecha');

        Route::post('/nota', 'ClienteController@storeNota')->name('notas.store');
        Route::get('/phpinfo', function () {
            if (extension_loaded('zip')) {
                echo 'La extensión ZIP está habilitada.';
            } else {
                echo 'La extensión ZIP no está habilitada.';
            }
            return phpinfo();
        });
        /**
         * Logout Routes
         */
        Route::get('/logout', 'LogoutController@perform')->name('logout.perform');
    });
});
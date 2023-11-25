<?php

use App\Models\Artefacto;
use App\Models\Cliente;
use App\Models\Zona;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Carbon\Carbon;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'cobrador']);
        Permission::create(['name' => 'login_web'])->syncRoles([$role1, $role2]);

        $admin = new User();
        $admin->id = 1;
        $admin->name = 'admin_prueba';
        $admin->email = 'admin';
        $admin->password = 'admin';
        $admin->assignRole($role1);
        $admin->save();

        $Marce = new User();
        $Marce->id = 19720;
        $Marce->name = 'Marcelo';
        $Marce->email = 'marce';
        $Marce->password = 'admin';
        $Marce->assignRole($role2);
        $Marce->save();

        $cobrador = new User();
        $cobrador->id = 2;
        $cobrador->name = 'cobrador_prueba';
        $cobrador->email = 'cobrador';
        $cobrador->password = 'cobrador';
        $cobrador->assignRole($role2);
        $cobrador->save();

        $zonaD = new Zona();
        $zonaD->nombre = 'D';
        $zonaD->save();
        $Marce->zonas()->attach($zonaD);

        $zonaX = new Zona();
        $zonaX->nombre = 'X';
        $zonaX->save();

        //Asocio el cobrador a la zona
        $cobrador->zonas()->attach($zonaX);
        //
        //Si quisiera deshacer la relacion:
        //$usuario->zonas()->detach($zona);
        //

        $artefactoSube = new Artefacto();
        $artefactoSube->nombre = 'SUBE';
        $artefactoSube->save();

        $artefactoCargaVirtual = new Artefacto();
        $artefactoCargaVirtual->nombre = 'Carga Virtual';
        $artefactoCargaVirtual->save();

        $clienteX = new Cliente();
        $clienteX->id = 1;
        $clienteX->direccion = 'España 3301';
        $clienteX->razon_social = 'Juan';
        $clienteX->saldo = 250.30;
        $clienteX->saldoInicial = 250.30;
        $clienteX->limiteTotal = 1000000;
        $clienteX->limiteActual = 1000000;
        //Vinculo a la zona 1 el cliente
        $clienteX->zona_id = 2;
        $clienteX->save();

        //Vinculo el cliente con el artefacto y agrego la comision
        $comision = 0;
        $clienteX->artefactos()->attach($artefactoSube, ['comision' => $comision]);
        // Cuando quiera buscar la comision:
        // $cliente = Cliente::find(1);
        // $artefacto = Artefacto::find(1);
        // $comision = $cliente->artefactos->find($artefacto->id)->pivot->comision;
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personalizado');
    }
};
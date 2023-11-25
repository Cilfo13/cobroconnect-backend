<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('artefacto_id');
            $table->date('fecha');
            $table->decimal('saldo_viejo', 12, 2);
            $table->decimal('notasTotal', 12, 2)->nullable();
            $table->decimal('cargasTotal', 12, 2)->nullable();
            $table->decimal('cobrosTotal', 12, 2)->nullable();
            $table->decimal('cargaVirtualTotal', 12, 2)->nullable();
            $table->decimal('comision', 12, 2)->nullable();
            $table->decimal('saldo_nuevo', 12, 2);
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('artefacto_id')->references('id')->on('artefactos')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('saldo_clientes');
    }
};
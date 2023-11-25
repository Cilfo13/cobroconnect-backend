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
        Schema::create('cargas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('artefacto_id');
            $table->dateTime('fecha');
            $table->decimal('monto', 12, 2);
            $table->decimal('anulacion', 12, 2);
            $table->decimal('importe_cargas_no_aplicadas', 12, 2);
            $table->decimal('saldo_nuevo', 12, 2);
            $table->decimal('saldo_viejo', 12, 2);

            // Otros campos relevantes para la carga
            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('artefacto_id')->references('id')->on('artefactos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cargas');
    }
};
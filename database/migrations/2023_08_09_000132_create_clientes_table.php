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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zona_id')->nullable();
            $table->string('direccion');
            $table->string('razon_social');
            $table->decimal('saldo', 12, 2);
            $table->decimal('saldoInicial', 12, 2);
            $table->decimal('limiteTotal', 12, 2);
            $table->decimal('limiteActual', 12, 2);
            $table->timestamps();
            $table->foreign('zona_id')->references('id')->on('zonas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
};
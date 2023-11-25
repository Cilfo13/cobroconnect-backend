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
        Schema::create('cliente_artefacto', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('artefacto_id');
            $table->decimal('comision', 12, 2);

            $table->foreign('cliente_id')->references('id')
                ->on('clientes')
                ->onDelete('cascade');
            $table->foreign('artefacto_id')->references('id')
                ->on('artefactos')
                ->onDelete('cascade');

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
        Schema::dropIfExists('cliente_artefactos');
    }
};
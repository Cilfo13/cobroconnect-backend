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
        Schema::create('carga_comisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->dateTime('fecha');
            $table->decimal('monto', 12, 2);
            $table->decimal('anulacion', 12, 2);
            $table->decimal('importe_cargas_no_aplicadas', 12, 2);
            $table->timestamps();
            $table->foreign('cliente_id')->references('id')->on('cliente_comisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carga_comisions');
    }
};
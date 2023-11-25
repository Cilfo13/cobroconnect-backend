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
        Schema::create('reporte_comisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id');
            $table->decimal('cargasTotal', 14, 4)->nullable();
            $table->decimal('comision', 14, 4)->nullable();
            $table->date('fecha');
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
        Schema::dropIfExists('reporte_comisions');
    }
};
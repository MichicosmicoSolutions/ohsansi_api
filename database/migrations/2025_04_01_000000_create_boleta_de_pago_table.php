<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('boleta_de_pago', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden_de_pago')->nullable()->unique();
            $table->string('status')->nullable();
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->integer('cantidad')->nullable();
            $table->string('concepto')->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->decimal('importe', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('boleta_de_pago');
    }
};

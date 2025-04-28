<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlimpycAndCategoriasTable extends Migration
{
    public function up()
    {
        Schema::create('olimpyc_and_categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olympic_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('olimpyc_and_categorias');
    }
}

<?php

use App\Enums\RangeCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalDataTable extends Migration
{
    public function up()
    {
        Schema::create('personal_data', function (Blueprint $table) {
            $table->id();
            $table->integer('ci')->unique();
            $table->string('ci_expedition');
            $table->string('names');
            $table->string('last_names');
            $table->date('birthdate');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('gender');
        });

        Schema::create('accountables', function (Blueprint $table) {
            $table->foreignId('personal_data_id')->constrained('personal_data')->onDelete('cascade');
            $table->primary(['personal_data_id']);
        });

        Schema::create('legal_tutors', function (Blueprint $table) {
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
            $table->primary(['personal_data_id']);
        });

        Schema::create('teachers', function (Blueprint $table) {
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
            $table->primary(['personal_data_id']);
        });
    }

    public function down()
    {
        // Eliminar primero las tablas con dependencias
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('legal_tutors');
        Schema::dropIfExists('accountables');
        Schema::dropIfExists('personal_data');
    }
}

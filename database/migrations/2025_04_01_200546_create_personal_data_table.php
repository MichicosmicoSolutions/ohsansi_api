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
        });

        Schema::create('responsables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_data_id')->constrained('personal_data')->onDelete('cascade');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('legal_tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('legal_tutor_id')->constrained('legal_tutors')->onDelete('cascade');
            $table->foreignId('responsable_id')->constrained('responsables')->onDelete('cascade');
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
            $table->enum('course', RangeCourse::getValues());
            $table->timestamps();
        });
    }

    public function down()
    {
        // Eliminar primero las tablas con dependencias
        Schema::dropIfExists('competitors');
        Schema::dropIfExists('legal_tutors');
        Schema::dropIfExists('responsables');
        Schema::dropIfExists('personal_data');
    }
}

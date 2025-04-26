<?php

use App\Enums\RangeCourse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
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


        Schema::create('legal_tutors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->enum('course', RangeCourse::getValues());
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('legal_tutor_id')->constrained('legal_tutors')->onDelete('cascade');
            $table->foreignId('personal_data_id')->unique()->constrained('personal_data')->onDelete('cascade');
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
    Schema::dropIfExists('competitors');
    Schema::dropIfExists('legal_tutors');
    Schema::dropIfExists('personal_data');
}

}

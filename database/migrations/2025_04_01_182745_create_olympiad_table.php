<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlympiadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olympiads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('status');
            $table->text('description')->nullable();
            $table->integer('price')->nullable();
            $table->text('presentation')->nullable();
            $table->text('requirements')->nullable();
            $table->text('awards')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('contacts')->nullable();
            $table->timestamps();
        });

        Schema::create('olympiad_areas', function (Blueprint $table) {
            $table->foreignId('olympiad_id')->constrained('olympiads')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->primary(['olympiad_id', 'area_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('olympiads');
        Schema::dropIfExists('olympiad_areas');
    }
}

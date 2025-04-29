<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOlympicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olympics', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('price')->nullable();
            $table->string('status');
            $table->text('Presentation')->nullable();
            $table->text('Requirements')->nullable();
            $table->text('awards')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('Contacts')->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
        *
        * @return void
        */
        public function down()
        {
            Schema::dropIfExists('olympics');
        }
    }

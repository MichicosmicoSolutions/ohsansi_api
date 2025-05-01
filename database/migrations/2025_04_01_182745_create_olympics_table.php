<?php

use App\Enums\Publish;
use App\Enums\PublishStatus;
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
            $table->enum('publish', Publish::getValues())->nullable();
            $table->text('Presentation')->nullable();
            $table->text('Requirements')->nullable();
            $table->text('awards')->nullable();
            $table->string('Contacts')->nullable();
            $table->timestamp('start_date')->nullable();  // Cambiado a timestamp
            $table->timestamp('end_date')->nullable();    // Cambiado a timestamp
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

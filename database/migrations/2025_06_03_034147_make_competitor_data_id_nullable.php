<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeCompetitorDataIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            // 1. Eliminar la foreign key actual
            $table->dropForeign(['competitor_data_id']);

            // 2. Hacer que la columna sea nullable
            $table->unsignedBigInteger('competitor_data_id')->nullable()->change();

            // 3. Volver a aplicar la foreign key
            $table->foreign('competitor_data_id')
                ->references('id')
                ->on('personal_data')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inscriptions', function (Blueprint $table) {
            $table->dropForeign(['competitor_data_id']);

            $table->unsignedBigInteger('competitor_data_id')->nullable(false)->change();

            $table->foreign('competitor_data_id')
                ->references('id')
                ->on('personal_data')
                ->onDelete('cascade');
        });
    }
}

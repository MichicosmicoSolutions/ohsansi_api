<?php

use App\Enums\InscriptionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('status', InscriptionStatus::getValues());
            $table->string('drive_url')->nullable();
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('competitor_data_id')->constrained('personal_data', 'id')->onDelete('cascade');
            $table->foreignId('accountable_id')->nullable()->constrained('accountables', 'personal_data_id')->onDelete('cascade');
            $table->foreignId('legal_tutor_id')->nullable()->constrained('legal_tutors', 'personal_data_id')->onDelete('cascade');
            $table->foreignId('olympiad_id')->constrained('olympiads')->onDelete('cascade');
            $table->unique(['competitor_data_id', 'olympiad_id']);
            $table->foreignId('boleta_de_pago_id')->nullable()->constrained('boleta_de_pago')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('selected_areas', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('inscription_id')->constrained('inscriptions')->onDelete('cascade');
            $table->foreignId('area_id')->constrained('areas')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('teacher_id')->nullable()->constrained('teachers', 'personal_data_id')->onDelete('cascade');
            $table->primary(['inscription_id', 'area_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inscriptions');
        Schema::dropIfExists('selected_areas');
    }
}

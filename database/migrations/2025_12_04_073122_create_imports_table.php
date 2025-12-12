<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->json('column_matching')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->integer('total_patient')->nullable()->default(0);
            $table->integer('total_new_patient')->nullable()->default(0);
            $table->integer('total_update_patient')->nullable()->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->uuid('clinic_id')->nullable()->comment('Id of clinic who uploaded.');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};

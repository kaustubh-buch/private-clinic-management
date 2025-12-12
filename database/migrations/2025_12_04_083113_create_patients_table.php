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
        Schema::create('patients', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('patient_code',100)->nullable();
            $table->string('first_name',50)->nullable();
            $table->string('last_name',50)->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('mobile_number',20)->nullable();
            $table->enum('status', ['booked', 'cancelled', 'rescheduled', 'completed', 'no_future_appointment', 'opted_out', 'deactivated'])->default('booked');
            $table->timestamp('status_updated_at')->nullable();
            $table->tinyInteger('is_opted_out')->default(0);
            $table->tinyInteger('is_deactivated')->default(0);
            $table->uuid('clinic_id')->nullable()->comment('Id of clinic patient belongs to.');
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
        Schema::dropIfExists('patients');
    }
};

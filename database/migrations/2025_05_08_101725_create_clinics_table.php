<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('address', 191);
            $table->string('other_software', 191)->nullable();
            $table->string('contact_no', 20);
            $table->string('mobile_no', 20)->nullable();
            $table->boolean('is_approved')->default(0);
            $table->text('disapproval_reason')->nullable();
            $table->boolean('is_online_booking')->default(0);
            $table->string('booking_link', 2083)->nullable();
            $table->float('average_checkup_fee')->nullable();
            $table->boolean('six_month_recall_sms')->default(0);
            $table->foreignId('timezone_id')->nullable()->constrained();
            $table->foreignId('software_id')->nullable()->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};

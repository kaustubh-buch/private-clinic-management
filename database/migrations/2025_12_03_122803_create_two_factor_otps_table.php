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
        Schema::create('two_factor_otps', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('otp_code',255)->nullable();
            $table->integer('resend_count')->default(0);
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->uuid('user_id')->nullable()->comment('Id of user to whom otp is sent.');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_otps');
    }
};

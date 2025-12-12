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
        Schema::create('email_invites', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->string('token',255)->nullable();
            $table->uuid('user_id')->nullable()->comment('Id of user to whom invite is sent.');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('sent_at')->nullable();
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
        Schema::dropIfExists('email_invites');
    }
};

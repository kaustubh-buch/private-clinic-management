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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name',20)->nullable()->after('name');
            $table->string('last_name',20)->nullable()->after('first_name');
            $table->enum('status', ['invited', 'verified', 'inactive'])->default('inactive')->after('last_name');
            $table->string('phone_number',20)->nullable()->after('email');
            $table->timestamp('last_verification_sent_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_logged_in_at')->nullable()->after('last_verification_sent_at');
            $table->timestamp('deleted_at')->nullable()->after('last_logged_in_at');
            $table->tinyInteger('attempt_count')->default(0)->after('remember_token');
            $table->timestamp('blocked_until')->nullable()->after('attempt_count');
            $table->uuid('user_added_by')->nullable()->after('id')->comment('Id of user who created current user.');
            $table->foreign('user_added_by')->references('id')->on('users')->onDelete('set null');
            $table->uuid('clinic_id')->nullable()->after('user_added_by')->comment('Id of clinic when user belongs to clinic.');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'clinic_id',
                'user_added_by',
                'first_name',
                'last_name',
                'status',
                'phone_number',
                'email_verified_at',
                'last_verification_sent_at',
                'last_logged_in_at',
                'deleted_at',
                'attempt_count',
                'blocked_until',
            ]);
            $table->dropForeign('clinic_id');
            $table->dropForeign('user_added_by');
            $table->string('name')->after('id');
        });
    }
};

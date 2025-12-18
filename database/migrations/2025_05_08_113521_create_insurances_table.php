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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('abbreviation', 20);
            $table->string('common_name', 50);
            $table->enum('status', ['approved', 'disapprove', 'pending']);
            $table->foreignId('clinic_id')->nullable()->constrained();
            $table->foreignId('template_category_id')->nullable()->constrained();
            $table->foreignId('software_id')->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};

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
        Schema::create('import_data', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->text('patient_code')->nullable();
            $table->string('first_name',50)->nullable();
            $table->string('last_name',50)->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('mobile_number',20)->nullable();
            $table->string('invalid_columns',255)->nullable();
            $table->uuid('import_id')->nullable()->comment('Id of import from where data is generated.');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('import_id')->references('id')->on('imports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_data');
    }
};

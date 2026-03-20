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
        Schema::create('kyc', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('f_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('dob')->nullable();
            $table->string('com_name')->nullable();
            $table->text('com_address')->nullable();
            $table->string('com_gst')->nullable();
            $table->string('com_pan')->nullable();
            $table->text('file')->nullable();
            $table->text('signature')->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->integer('c_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc');
    }
};

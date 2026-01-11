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
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['farmer', 'party','emp']);
            $table->unsignedBigInteger('f_id')->nullable();
            $table->string('acc_type',30)->nullable();
            $table->string('b_name',100)->nullable();
            $table->string('acc_name',100)->nullable();
            $table->string('acc_no',100)->nullable();
            $table->string('ifsc',30)->nullable();
            $table->string('upi',50)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('c_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_details');
    }
};

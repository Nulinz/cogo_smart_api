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
         Schema::create('m_farmer', function (Blueprint $table) {
            $table->id();
            $table->string('farm_seq',100)->nullable();
            $table->string('farm_en',255)->nullable();
            $table->string('farm_nick_en',255)->nullable();
            $table->string('location',30)->nullable();
            $table->string('ph_no',15)->nullable();
            $table->string('wp_no',15)->nullable();
            $table->enum('open_type', ['give', 'get'])->nullable();
            $table->unsignedBigInteger('open_bal')->nullable();
            $table->unsignedBigInteger('adv_prime')->nullable();
            $table->string('acc_type',30)->nullable();
            $table->string('b_name',100)->nullable();
            $table->string('acc_name',100)->nullable();
            $table->string('acc_no',50)->nullable();
            $table->string('ifsc',30)->nullable();
            $table->string('upi',50)->nullable();
            $table->unsignedInteger('fav')->default(0); // if count > 0 then favorite
            $table->unsignedBigInteger('c_by')->nullable(); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_farmer');
    }
};

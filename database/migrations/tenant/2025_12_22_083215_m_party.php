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
        Schema::create('m_party', function (Blueprint $table) {
            $table->id();
            $table->string('party_seq',100)->nullable();
            $table->string('party_en',255)->nullable();
            $table->string('party_nick_en',255)->nullable();
            $table->string('com_name',200)->nullable();
            $table->text('com_add')->nullable();
            $table->string('party_location',30)->nullable();
            $table->string('party_ph_no',15)->nullable();
            $table->string('party_wp_no',15)->nullable();
            $table->enum('party_open_type', ['give', 'get'])->nullable();
            $table->unsignedBigInteger('party_open_bal')->nullable();
            $table->string('party_acc_type',30)->nullable();
            $table->string('party_b_name',100)->nullable();
            $table->string('party_acc_name',100)->nullable();
            $table->string('party_acc_no',50)->nullable();
            $table->string('party_ifsc',30)->nullable();
            $table->string('party_upi',50)->nullable();
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
        Schema::dropIfExists('m_party');
    }
};

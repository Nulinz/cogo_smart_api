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
         Schema::create('m_sequence', function (Blueprint $table) {
            $table->id();
            $table->string('load_pref',10)->nullable();
            $table->string('load_suf',10)->nullable();
            $table->string('farmer_pref',10)->nullable();
            $table->string('farmer_suf',10)->nullable();
            $table->string('party_pref',10)->nullable();
            $table->string('party_suf',10)->nullable();
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
        Schema::dropIfExists('m_sequence');
    }
};

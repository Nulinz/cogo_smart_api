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
        Schema::create('m_expense', function (Blueprint $table) {
            $table->id();
            $table->string('title',200)->nullable();
            $table->unsignedBigInteger('exp_cat')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved','rejected'])->default('pending');
            $table->unsignedBigInteger('c_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_expense');
    }
};

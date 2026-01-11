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
        Schema::create('m_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('load_id')->nullable();
            $table->unsignedBigInteger('ext_piece')->nullable();
            $table->decimal('ext_amount', 10, 2)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->json('charges')->nullable();
            $table->text('description')->nullable();
            $table->text('file')->nullable();
            $table->decimal('product_profit', 10, 2)->nullable();
            $table->decimal('shift_loss', 10, 2)->nullable();
            $table->decimal('loading', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->json('final_loss')->nullable();
            $table->decimal('profit_loss', 10, 2)->nullable();
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
        Schema::dropIfExists('m_invoice');
    }
};

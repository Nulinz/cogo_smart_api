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
        Schema::create('stock_out', function (Blueprint $table) {
            $table->id();
            $table->enum('cat', ['load', 'sales'])->default('load');
            $table->unsignedBigInteger('load_id')->nullable();
            $table->unsignedBigInteger('farm_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('total_piece')->nullable();
            $table->decimal('grace_per', 10, 2)->default(0);
            $table->unsignedBigInteger('bill_piece')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedBigInteger('commission')->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
            $table->unsignedBigInteger('adv')->nullable();
            $table->unsignedBigInteger('quality')->nullable();
            $table->decimal('total_amt', 15, 2)->nullable();
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
        Schema::dropIfExists('stock_out');
    }
};

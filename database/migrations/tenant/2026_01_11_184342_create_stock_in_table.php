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
        Schema::create('stock_in', function (Blueprint $table) {
            $table->id();
            $table->enum('cat', ['load', 'purchase','filter'])->default('load');
            $table->integer('load_id')->nullable();
            $table->integer('farm_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('total_piece')->nullable();
            $table->integer('grace_piece')->nullable();
            $table->decimal('grace_per', 10, 2)->default(0);
            $table->integer('bill_piece')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('commission')->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
            $table->integer('adv')->nullable();
            $table->integer('quality')->nullable();
            $table->decimal('total_amt', 15, 2)->nullable();
            $table->enum('status', ['active', 'inactive','clear'])->default('active');
            $table->enum('clear_status', ['clear', 'not_clear'])->default('not_clear');
            $table->integer('c_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in');
    }
};

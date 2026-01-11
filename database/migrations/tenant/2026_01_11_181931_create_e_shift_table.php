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
        Schema::create('e_shift', function (Blueprint $table) {
            $table->id();
            $table->enum('cat', ['load', 'others','stock'])->default('load');
            $table->unsignedBigInteger('load_id')->nullable();
            $table->unsignedBigInteger('to_load')->nullable();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('total_piece')->nullable();
            $table->unsignedBigInteger('grace_piece')->nullable();
            $table->decimal('grace_per', 10, 1)->nullable();
            $table->unsignedBigInteger('bill_piece')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
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
        Schema::dropIfExists('e_shift');
    }
};

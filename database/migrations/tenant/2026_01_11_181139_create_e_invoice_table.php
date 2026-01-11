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
        Schema::create('e_invoice', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inv_id')->nullable();
            $table->unsignedBigInteger('load_id')->nullable();
            $table->unsignedBigInteger('product')->nullable();
            $table->decimal('total', 10, 2)->nullable()->default(0);
            $table->decimal('grace',10, 2)->nullable()->default(0);
            $table->decimal('price',10, 2)->nullable()->default(0);
            $table->decimal('bill_amt',10, 2)->nullable()->default(0);
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
        Schema::dropIfExists('e_invoice');
    }
};

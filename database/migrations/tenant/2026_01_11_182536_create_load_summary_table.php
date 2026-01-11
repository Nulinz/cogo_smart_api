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
        Schema::create('load_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('load_id')->nullable();
            $table->unsignedBigInteger('filter_total')->nullable();
            $table->unsignedBigInteger('filter_billing')->nullable();
            $table->decimal('filter_price', 10, 2)->nullable();
            $table->decimal('filter_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('exp_loading')->nullable();
            $table->unsignedBigInteger('exp_misc')->nullable();
            $table->unsignedBigInteger('exp_rmc')->nullable();
            $table->unsignedBigInteger('total')->nullable();
            $table->unsignedBigInteger('grace')->nullable();
            $table->decimal('grace_per', 10, 2)->nullable();
            $table->decimal('billing_amt', 10, 2)->nullable()->default(0);
            $table->decimal('avg_price', 10, 2)->nullable()->default(0);
            $table->unsignedBigInteger('total_weight')->nullable();
            $table->unsignedBigInteger('empty_weight')->nullable();
            $table->unsignedBigInteger('net_weight')->nullable();
            $table->decimal('avg_per_weight', 10, 2)->nullable();
            $table->decimal('shift_loss', 10, 2)->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');  
            $table->unsignedBigInteger('c_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_summary');
    }
};

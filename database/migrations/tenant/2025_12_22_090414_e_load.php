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
         Schema::create('e_load', function (Blueprint $table) {
            $table->id();
            $table->enum('cat', ['add','load','stock'])->default('add');
            $table->string('load_id',100)->nullable();
            $table->integer('shift_id')->nullable();
            $table->integer('farmer_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('total_piece')->nullable();
            $table->integer('grace_piece')->nullable();
            $table->decimal('grace_per',10,1)->nullable();
            $table->integer('bill_piece')->nullable();
            $table->decimal('price',10,2)->nullable();
            $table->integer('commission')->nullable();
            $table->decimal('bill_amount',15,2)->nullable();
            $table->integer('adv')->nullable();
            $table->integer('quality')->nullable();
            $table->decimal('total_amt',10,2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('c_by')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_load');
    }
};

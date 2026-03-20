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
        Schema::create('m_load', function (Blueprint $table) {
            $table->id();
            $table->string('load_seq',50)->nullable();
            $table->string('market',200)->nullable();
            $table->integer('product_id')->nullable();
            $table->integer('party_id')->nullable();
            $table->string('empty_weight',10)->nullable();
            $table->date('load_date')->nullable();
            $table->string('veh_no',20)->nullable();
            $table->string('dr_no',20)->nullable();
            $table->integer('transporter')->nullable();
            $table->decimal('quality_price',10,2)->nullable();
            $table->decimal('filter_price',10,2)->nullable();
            $table->unsignedInteger('req_qty')->nullable();
            $table->unsignedInteger('truck_capacity')->nullable();
            $table->json('team')->nullable();
            $table->integer('c_by')->nullable(); 
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('load_status', ['sum_draft', 'sum_completed','inv_draft','inv_completed'])->default('sum_draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_load');
    }
};

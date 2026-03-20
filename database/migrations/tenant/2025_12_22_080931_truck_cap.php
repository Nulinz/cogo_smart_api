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
        Schema::create('truck_cap', function (Blueprint $table) {
            $table->id();
            $table->string('capacity');
            $table->string('charge',10)->nullable();
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
        Schema::dropIfExists('truck_cap');
    }
};

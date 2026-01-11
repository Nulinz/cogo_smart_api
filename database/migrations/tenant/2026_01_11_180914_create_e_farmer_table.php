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
        Schema::create('e_farmer', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('farm_id')->nullable();
            $table->unsignedBigInteger('load_id')->nullable();
            $table->enum('type', ['advance', 'purchase','advance_deduct'])->nullable()->default('advance');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('method',100)->nullable();
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
        Schema::dropIfExists('e_farmer');
    }
};

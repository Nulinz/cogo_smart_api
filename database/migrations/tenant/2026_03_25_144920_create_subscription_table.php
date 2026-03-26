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
        Schema::create('subscription', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['silver', 'gold','platinum'])->nullable();
            $table->integer('duration')->nullable();
            $table->decimal('amount',10,2)->nullable();
            $table->string('t_id')->nullable();
            $table->string('pay_status')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription');
    }
};

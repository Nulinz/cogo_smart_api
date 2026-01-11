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
        Schema::create('e_party', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_id')->nullable();
            $table->enum('type', ['pay_in', 'pay_out'])->default('pay_in');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('method', 100)->nullable();
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
        Schema::dropIfExists('e_party');
    }
};

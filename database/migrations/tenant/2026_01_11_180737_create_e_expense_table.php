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
        Schema::create('e_expense', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable()->default(0);
            $table->string('method',100)->nullable();
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
        Schema::dropIfExists('e_expense');
    }
};

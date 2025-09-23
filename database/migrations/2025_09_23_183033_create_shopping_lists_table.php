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
        Schema::create('shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supermarket_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('status', ['draft', 'active', 'completed', 'archived'])->default('draft');
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('estimated_total', 10, 2)->nullable();
            $table->timestamp('planned_for')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shopping_lists');
    }
};

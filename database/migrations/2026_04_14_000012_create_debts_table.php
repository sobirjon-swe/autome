<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('debtor_name');
            $table->string('phone', 20)->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('given_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['open', 'partial', 'paid'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};

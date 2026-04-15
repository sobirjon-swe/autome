<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('closing_shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('opening_shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->decimal('declared_amount', 12, 2);
            $table->decimal('confirmed_amount', 12, 2);
            $table->decimal('difference', 12, 2)->default(0);
            $table->enum('status', ['matched', 'mismatched']);
            $table->text('notes')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_handovers');
    }
};

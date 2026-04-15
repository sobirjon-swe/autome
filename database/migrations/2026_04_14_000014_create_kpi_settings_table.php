<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('shift_type', ['morning', 'day', 'night']);
            $table->enum('day_type', ['weekday', 'weekend']);
            $table->decimal('target_amount', 12, 2);
            $table->jsonb('bonus_rules');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_settings');
    }
};

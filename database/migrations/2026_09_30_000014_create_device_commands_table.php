<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->json('payload')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('result')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_commands');
    }
};

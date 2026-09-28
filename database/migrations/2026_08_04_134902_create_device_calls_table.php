<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['incoming', 'outgoing', 'missed'])->default('incoming');
            $table->string('contact_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->dateTime('started_at')->index();
            $table->timestamps();

            $table->index(['device_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_calls');
    }
};
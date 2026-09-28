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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('os')->default('android');
            $table->string('os_version')->nullable();
            $table->string('phone_number')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended'])->default('pending');
            $table->boolean('consent_recorded')->default(false);
            $table->timestamp('consented_at')->nullable();
            $table->string('agent_token', 64)->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
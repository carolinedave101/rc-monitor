<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->default('sms');
            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');
            $table->string('contact_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('body');
            $table->boolean('was_deleted')->default(false);
            $table->dateTime('sent_at')->index();
            $table->timestamps();

            $table->index(['device_id', 'platform', 'sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_messages');
    }
};
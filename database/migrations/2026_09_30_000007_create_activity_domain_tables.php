<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_app_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('app_name');
            $table->string('package')->nullable();
            $table->string('category')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamp('launched_at')->index();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'launched_at']);
        });

        Schema::create('device_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'name']);
        });

        Schema::create('device_diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('battery_percent')->nullable();
            $table->boolean('is_charging')->default(false);
            $table->unsignedInteger('storage_used_mb')->nullable();
            $table->unsignedInteger('storage_total_mb')->nullable();
            $table->string('network')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'recorded_at']);
        });

        Schema::create('device_browser_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->string('domain')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('visited_at')->index();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'visited_at']);
        });

        Schema::create('device_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('direction', ['incoming', 'outgoing'])->default('incoming');
            $table->string('address');
            $table->string('subject')->nullable();
            $table->text('snippet')->nullable();
            $table->timestamp('sent_at')->index();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'sent_at']);
        });

        Schema::create('device_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('filename');
            $table->decimal('size_mb', 8, 2)->nullable();
            $table->timestamp('taken_at')->index();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'taken_at']);
        });

        Schema::create('device_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'updated_at']);
        });

        Schema::create('device_calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('location')->nullable();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->nullable();
            $table->string('source')->default('agent');
            $table->timestamps();
            $table->index(['device_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        foreach ([
            'device_calendar_events',
            'device_notes',
            'device_media',
            'device_emails',
            'device_browser_histories',
            'device_diagnostics',
            'device_contacts',
            'device_app_activities',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};

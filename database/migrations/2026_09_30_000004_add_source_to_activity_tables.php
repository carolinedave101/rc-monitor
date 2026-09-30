<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['devices', 'device_calls', 'device_messages', 'device_locations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('source')->default('agent');
            });
        }
    }

    public function down(): void
    {
        foreach (['devices', 'device_calls', 'device_messages', 'device_locations'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};

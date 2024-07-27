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
        Schema::table('stream_handles', function (Blueprint $table) {
            $table->string('channel_id')->after('name')->nullable();
            $table->string('channel_url')->after('channel_id')->nullable();
            // Change column
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stream_handles', function (Blueprint $table) {
            $table->dropColumn('channel_id');
            $table->dropColumn('channel_url');
            // Change column
            $table->string('name')->nullable(false)->change();
        });
    }
};

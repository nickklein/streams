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
            $table->mediumInteger('streamer_id');
        });

        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->renameColumn('stream_handle_id', 'streamer_id');
            $table->string('preferred_platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stream_handles', function (Blueprint $table) {
            $table->dropColumn('streamer_id');
        });

        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->renameColumn('streamer_id', 'stream_handle_id');
            $table->dropColumn('preferred_platform');
        });
    }
};

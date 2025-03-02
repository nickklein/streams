<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScrapeTrackingToUserStreamHandlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_live')->default(false);
            $table->boolean('queued')->default(true);
        });

        Schema::table('stream_handles', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->dropColumn('last_synced_at');
            $table->dropColumn('is_live');
            $table->dropColumn('queued');
        });

        Schema::table('stream_handles', function(Blueprint $table) {
            $table->string('name')->nullable();
        });
    }
}

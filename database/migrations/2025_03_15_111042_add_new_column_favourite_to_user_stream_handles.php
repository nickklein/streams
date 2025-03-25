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
        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->boolean('favourite')->default(0)->after('queued');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_stream_handles', function (Blueprint $table) {
            $table->dropColumn('favourite');
        });
    }
};
;

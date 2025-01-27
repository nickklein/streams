<?php

namespace NickKlein\Streams\Seeders;

use NickKlein\Streams\Models\Streamer;

class StreamerSeeder
{
    /**
     *  Seed Streamer data
     *
     * @return void
     */
    public function run()
    {
        Streamer::unguard();
        Streamer::insert([[
            'name' => 'ludwig',
        ], [
            'name' => 'summit1g'
        ], [
            'name' => 'starsmitten'
        ], [
            'name' => 'HAchubby',
        ]]);
        Streamer::reguard();
    }
}

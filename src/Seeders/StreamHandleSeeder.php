<?php

namespace NickKlein\Streams\Seeders;

use NickKlein\Streams\Models\StreamHandle;

class StreamHandleSeeder
{
    /**
     *  Seed Stream Handle data
     *
     * @return void
     */
    public function run()
    {
        StreamHandle::unguard();
        StreamHandle::insert([[
            'streamer_id' => 1,
            'channel_id' => 'ludwig',
            'channel_url' => 'https://www.twitch.tv/ludwig',
            'platform' => 'twitch',
        ], [
            'streamer_id' => 2,
            'channel_id' => 'summit1g',
            'channel_url' => 'https://www.twitch.tv/summit1g',
            'platform' => 'twitch',
        ], [
            'streamer_id' => 3,
            'channel_id' => 'starsmitten',
            'channel_url' => 'https://www.twitch.tv/starsmitten',
            'platform' => 'twitch',
        ], [
            'streamer_id' => 4,
            'channel_id' => 'UCMiTiaj5wsJoVjrd-V_IFVQ',
            'channel_url' => 'https://www.youtube.com/@HAchubby/live',
            'platform' => 'youtube',
        ]]);
        StreamHandle::reguard();
    }
}

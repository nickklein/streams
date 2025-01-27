<?php

namespace NickKlein\Streams\Seeders;

use App\Models\User;
use NickKlein\Streams\Models\Streamer;
use NickKlein\Streams\Models\UserStreamHandle;

class UserStreamHandleSeeder
{
    /**
     *  Seed User Stream Handle data
     *
     * @return void
     */
    public function run()
    {
        UserStreamHandle::unguard();
        $users = User::all();
        foreach($users as $user) {
            $streamers = Streamer::get();
            foreach($streamers as $streamer) {
                UserStreamHandle::create([
                    'user_id' => $user->id,
                    'streamer_id' => $streamer->id,
                    'preferred_platform' => 'twitch',
                ]);
            }
        }
        UserStreamHandle::reguard();
    }
}

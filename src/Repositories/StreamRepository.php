<?php

namespace NickKlein\Streams\Repositories;

use NickKlein\Streams\Models\UserStreamHandle;

class StreamRepository
{
    public function getUsersStreamHandles(int $userId, string $platform)
    {
        return UserStreamHandle::where('user_id', $userId)
            ->with(['streamer.streamHandles' => function ($query) use ($platform) {
                $query->where('platform', $platform);
            }])
            ->whereHas('streamer.streamHandles', function ($query) use ($platform) {
                $query->where('platform', $platform);
            })
            ->get();
    }


    public function getUsersStreamHandle(int $userId, string $userStreamId)
    {
        return UserStreamHandle::where('user_id', $userId)
            ->where('id', $userStreamId)
            ->with(['streamer.streamHandles'])
            ->get();
    }
}

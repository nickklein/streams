<?php

namespace NickKlein\Streams\Repositories;

use Illuminate\Database\Eloquent\Collection;
use NickKlein\Streams\Models\UserStreamHandle;

class StreamRepository
{
    public function getUsersGroupedStreamerHandles(int $userId, int $favourites = 0): Collection
    {
        $base = UserStreamHandle::query()
            ->where('user_id', $userId)
            ->when($favourites, fn ($q) => $q->where('favourite', $favourites));

        return UserStreamHandle::query()
            ->whereIn('id', (clone $base)
                ->selectRaw('MIN(id) as id')
                ->groupBy('streamer_id')
            )
            ->with(['streamer.streamHandles'])
            ->get();
    }

    public function getUsersStreamHandles(int $userId, int $userStreamId, int $favourites = 0)
    {
        return UserStreamHandle::where('user_id', $userId)
            ->where('id', $userStreamId)
            ->when($favourites, function($query) use ($favourites) {
                    return $query->where('favourite', $favourites);
            })
            ->first();
    }


    public function getUsersStreamHandle(int $userId, string $userStreamId): Collection
    {
        return UserStreamHandle::where('user_id', $userId)
            ->where('id', $userStreamId)
            ->with(['streamer.streamHandles'])
            ->get();
    }

    public function isLastSyncExpired(int $userId, int $userStreamId, int $minutes)
    {
        return UserStreamHandle::where('user_id', $userId)
            ->where('id', $userStreamId)
            ->where(function($query) use ($minutes) {
                $datetime = now()->subMinutes($minutes);
                $query->whereNull('last_synced_at')
                    ->orWhere('last_synced_at', '<', $datetime);
            })
            ->exists();
    }
}

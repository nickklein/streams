<?php

namespace NickKlein\Streams\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use NickKlein\Streams\Models\Streamer;
use NickKlein\Streams\Models\StreamHandle;
use NickKlein\Streams\Models\UserStreamHandle;
use NickKlein\Streams\Repositories\StreamRepository;
use NickKlein\Streams\Services\Stream\Twitch;
use NickKlein\Streams\Services\Stream\YouTube;

class StreamService
{
    private $streams;

    public function __construct(Twitch $twitch, YouTube $youTube, public StreamRepository $streamRepository)
    {
        $this->streams = [$twitch, $youTube];
    }

    public function getAllHandleIds(int $userId): array
    {
        $collection = collect([]);
        foreach ($this->streams as $stream) {
            $collection = $collection->merge(collect($stream->getProfileIds($userId)));
        }
        $sortedCollection = $this->sortCollectionByLiveAndName($collection);

        return $sortedCollection->toArray();
    }

    public function getProfile(int $userId, int $userStreamId)
    {
        foreach ($this->streams as $stream) {
            if ($profile = $stream->getProfileById($userId, $userStreamId)) {
                return $profile;
            }
        }

        return [];
    }

    public function storeStreamer(int $userId, string $platform, string $name, string $channelId, string $channelUrl): bool
    {
        try {
            // First check if this streamer is already associated with the user
            $existingStreamer = Streamer::where('name', $name)->first();
            
            if ($existingStreamer) {
                $existingUserStreamHandle = UserStreamHandle::where([
                    'user_id' => $userId,
                    'streamer_id' => $existingStreamer->id,
                ])->first();
                
                if ($existingUserStreamHandle) {
                    // The user already has this streamer associated
                    return false;
                }
            }
            
            DB::beginTransaction();
            
            // Find or create streamer
            $streamer = Streamer::firstOrCreate([
                'name' => $name,
            ]);
            
            // Find or create stream handle
            $streamHandle = StreamHandle::firstOrCreate([
                'platform' => $platform,
                'channel_id' => $channelId,
                'streamer_id' => $streamer->id,
            ], [
                'channel_url' => $channelUrl,
            ]);
            
            // Create user-streamer relationship
            $userStreamHandle = UserStreamHandle::create([
                'user_id' => $userId,
                'streamer_id' => $streamer->id,
                'preferred_platform' => $platform,
                'is_live' => 0,
                'queued' => 0,
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    private function sortCollectionByLiveAndName(Collection $collection): Collection
    {
        return $collection->sortBy([
            ['is_live', 'desc'],
            ['name', 'asc'],
        ])->values();
    }
}

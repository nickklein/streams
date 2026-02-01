<?php

namespace NickKlein\Streams\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use NickKlein\Streams\Models\Streamer;
use NickKlein\Streams\Models\StreamHandle;
use NickKlein\Streams\Models\UserStreamHandle;
use NickKlein\Streams\Repositories\StreamRepository;
use NickKlein\Streams\Services\Stream\Kick;
use NickKlein\Streams\Services\Stream\Twitch;
use NickKlein\Streams\Services\Stream\YouTube;

class StreamService
{
    private $streams;

    public function __construct(Kick $kick, Twitch $twitch, YouTube $youTube, public StreamRepository $streamRepository)
    {
        $this->streams = [$kick, $twitch, $youTube];
    }

    public function getAllHandleIds(int $userId, int $favourites = 0): array
    {
        $userStreamHandles = $this->streamRepository->getUsersStreamers($userId, $favourites);
        $response = [];

        foreach ($userStreamHandles as $item) {
            $response[] = [
                'id' => $item->id,
                'name' => $item->streamer->name,
                'is_live' => $item->is_live,
            ];
        }

        return $this->sortCollectionByLiveAndName(collect($response))->toArray();
    }

    public function getProfile(int $userId, int $userStreamId)
    {
        $ush = $this->streamRepository->getUsersStreamHandle($userId, $userStreamId)->first();
        if (!$ush) {
            return [];
        }

        $cacheMinutes = 15;
        $isCacheExpired = $this->streamRepository->isLastSyncExpired($userId, $userStreamId, $cacheMinutes);

        $isLive = $ush->is_live;
        $url = $ush->streamer->streamHandles->first()?->channel_url;

        if ($isCacheExpired) {
            $isLive = false;
            foreach ($ush->streamer->streamHandles as $handle) {
                $service = $this->getServiceByPlatform($handle->platform);
                if ($service && $service->isChannelLive($handle->channel_id)) {
                    $isLive = true;
                    $url = $handle->channel_url;
                    break;
                }
            }

            $ush->is_live = $isLive;
            $ush->last_synced_at = now();
            $ush->save();
        }

        $platforms = $ush->streamer->streamHandles->map(function ($handle) {
            return [
                'name' => $handle->platform,
                'url' => $handle->channel_url,
            ];
        })->values()->toArray();

        return [
            'id' => $ush->id,
            'name' => $ush->streamer->name,
            'url' => $url,
            'isLive' => $isLive,
            'platforms' => $platforms,
        ];
    }

    private function getServiceByPlatform(string $platform)
    {
        foreach ($this->streams as $service) {
            if ($service::NAME === $platform) {
                return $service;
            }
        }
        return null;
    }

    /**
     * Filter out live statuses using the profile array
     **/
    public function filterIsLiveStatus(array $profiles): Collection
    {
        $collection = collect($profiles);
        $filtered = $collection->filter(function ($item) {
            return $item['isLive'];
        });

        return $filtered;
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

<?php

namespace NickKlein\Streams\Services;

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

    // @DEPRECATED Moving to ajax call loading
    public function getAllProfiles(int $userId)
    {
        // Create laravel collection
        $collection = collect([]);
        foreach ($this->streams as $stream) {
            $collection = $collection->merge(collect($stream->getProfiles($userId)));
        }

        return $collection->sortBy(function ($profile) {
            // Convert 'isLive' to integer (true to 1, false to 0) and sort by 'isLive' descending
            // Then sort by 'name' ascending
            return [!$profile['isLive'], $profile['name']];
        })->values()->toArray();
    }

    public function getAllHandleIds(int $userId): array
    {
        $collection = collect([]);
        foreach ($this->streams as $stream) {
            $collection = $collection->merge(collect($stream->getProfileIds($userId)));
        }

        return $collection->toArray();
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
}

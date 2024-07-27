<?php

namespace NickKlein\Stream\Services;

use NickKlein\Stream\Services\Stream\Twitch;
use NickKlein\Stream\Services\Stream\YouTube;

class StreamService
{
    private $streams;

    public function __construct(Twitch $twitch, YouTube $youTube)
    {
        $this->streams = [$twitch, $youTube];
    }

    public function getHandles(int $userId)
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
}

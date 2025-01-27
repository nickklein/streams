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

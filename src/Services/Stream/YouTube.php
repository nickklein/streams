<?php

namespace NickKlein\Streams\Services\Stream;

use Exception;
use NickKlein\Streams\Interfaces\StreamServiceInterface;
use GuzzleHttp\Client;
use NickKlein\Streams\Repositories\StreamRepository;

class YouTube implements StreamServiceInterface
{
    const CACHE_MINUTES = 30;
    const NAME = 'youtube';

    public function __construct(public StreamRepository $streamRepository)
    {
        //
    }

    public function getLimitedProfile(int $userId, int $favourites = 0): array
    {
        $response = [];
        $streamHandles = $this->streamRepository->getUsersStreamHandles($userId, self::NAME, $favourites);

        foreach ($streamHandles as $stream) {
            $response[] = [
                'id' => $stream->id,
                'name' => $stream->streamer->name,
                'is_live' => $stream->is_live,
            ];
        }

        return $response;
    }


    public function getProfileById(int $userId, int $userStreamId): array
    {
        $streamHandles = $this->streamRepository->getUsersStreamHandles($userId, self::NAME);

        $targetStream = null;
        foreach ($streamHandles as $stream) {
            if ($stream->id === $userStreamId) {
                $targetStream = $stream;
                break;
            }
        }

        // Return empty if no matching stream found
        if (!$targetStream) {
            return [];
        }
        
        // Get the first handle from the streamer
        $handle = $targetStream->streamer->streamHandles->first();
        if (!$handle) {
            return [];
        }

        // Determine if we need fresh live status
        $isCacheExpired = $this->streamRepository->isLastSyncExpired($userId, $userStreamId, self::CACHE_MINUTES);


        $isLive = $targetStream->is_live;
        // Create a new cache if the cache is expired and queued is 0
        // 
        if ($isCacheExpired) {
            // TODO: Code could be improved.
            $liveLoopCount = 0;
            $isLive = false;
            for($i = 0; $i < 5; $i++) {
                $isLive = $this->isChannelLive($handle->channel_id); // NOTE: Inaccurate way to check live status
                if ($isLive) {
                    $liveLoopCount++;
                    if ($liveLoopCount > 3) {
                        break;
                    }
                }
                sleep(1);
            }
            $targetStream->is_live = $isLive;
            $targetStream->last_synced_at = now();
            $targetStream->save();
        }

        return [
            'id' => $targetStream->id,
            'name' => $targetStream->streamer->name,
            'url' => $handle->channel_url,
            'isLive' => $isLive,
        ];
    }

    private function isChannelLive($channelId): bool
    {
        $client = new Client([
            'timeout' => 30,
            'http_errors' => false,
            'headers' => [
                'Accept-Encoding' => 'identity'
            ]
        ]);
        
        try {
            $channelUrl = "https://www.youtube.com/channel/{$channelId}/live";
            $response = $client->get($channelUrl);
            $html = $response->getBody()->getContents();
            
            if (strpos($html, '{"accessibilityData":{"label":"LIVE"}}},"style":"LIVE","icon":{"iconType":"LIVE"}') !== false) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}

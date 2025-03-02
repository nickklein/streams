<?php

namespace NickKlein\Streams\Services\Stream;

use NickKlein\Streams\Interfaces\StreamServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use NickKlein\Streams\Jobs\ProcessDuskJob;
use NickKlein\Streams\Repositories\StreamRepository;

class YouTube implements StreamServiceInterface
{
    const THRESHOLD = 855941;
    const CACHE_MINUTES = 30;
    const NAME = 'youtube';

    public function __construct(public StreamRepository $streamRepository)
    {
        //
    }

    public function getProfileIds(int $userId): array
    {
        $response = [];
        $streamHandles = $this->streamRepository->getUsersStreamHandles($userId, self::NAME);

        foreach ($streamHandles as $stream) {
            $response[] = [
                'id' => $stream->id,
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
        if ($isCacheExpired && !$targetStream->queued) {
        /*if (true) {*/
            $isLive = $this->isAccountLive($handle->channel_id); // NOTE: Inaccurate way to check live status
            $targetStream->is_live = $isLive;
            $targetStream->queued = 1;
            $targetStream->save();

            // TODO: Scraper
            /*ProcessDuskJob::dispatch($targetStream->id);*/
        }

        return [
            'id' => $targetStream->id,
            'name' => $targetStream->streamer->name,
            'url' => $handle->channel_url,
            'isLive' => $isLive,
        ];
    }


    /**
     * Fetch status of a youtube channel via HEAD
     *
     * NOTE: Note: I initially attempted this using the YouTube API, but retrieving live videos is currently not reliable. 
     * I tried using the Search API, but it doesn’t consistently return live streams and consumes a lot of API tokens hitting 
     * the limit within a few refreshes.
     **/
    private function isAccountLive(string $channel): bool
    {
        $url = "https://www.youtube.com/channel/{$channel}/live";
        $client = new Client();

        try {
            $response = $client->head($url, [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3'
                ]
            ]);

            $contentLength = $response->getHeader('Content-Length');

            if ($contentLength && $contentLength[0] > self::THRESHOLD) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            // Handle any errors here
            Log::error($e->getMessage());
            return false;
        }
    }
}

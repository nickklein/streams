<?php

namespace NickKlein\Streams\Services\Stream;

use NickKlein\Streams\Interfaces\StreamServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NickKlein\Streams\Repositories\StreamRepository;

class YouTube implements StreamServiceInterface
{
    const NAME = 'youtube';

    public function __construct(public StreamRepository $streamRepository)
    {
        //
    }

    public function getProfileIds(int $userId): array
    {
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

        foreach ($streamHandles as $stream) {
            if ($stream->id === $userStreamId) {
                $streamHandles = $stream->streamer->streamHandles;
                foreach($streamHandles as $handle) {
                    return [
                        'id' => $stream->id,
                        'name' => $stream->streamer->name,
                        'url' => $handle->channel_url,
                        'isLive' => $this->isAccountLive($handle->channel_id)
                    ];
                } 
            }
        }

        return [];
    }

    public function isAccountLive(string $channel): bool
    {
        $retryCount = 3;
        $apiKey = config('services.google.youtube_key');
        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel}&type=video&eventType=live&key={$apiKey}";

        try {
            // Retry needed because search youtube api isn't consistent
            return retry($retryCount, function () use ($url) {
                $response = Http::get($url);

                if ($response->failed()) {
                    Log::error("YouTube API request failed: " . $response->body());
                    throw new \Exception("YouTube API request failed"); // Forces retry
                }

                $data = $response->json();

                if (empty($data['items'])) {
                    Log::warning("YouTube API returned empty response. Retrying...");
                    throw new \Exception("No live videos found. Retrying..."); // Forces retry
                }

                return true;
            }, 2000); 
        } catch(\Exception $e) {
            return false;
        }

        return false;
    }
}

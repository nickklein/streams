<?php

namespace NickKlein\Streams\Services\Stream;

use NickKlein\Streams\Interfaces\StreamServiceInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use NickKlein\Streams\Repositories\StreamRepository;

class YouTube implements StreamServiceInterface
{
    const THRESHOLD = 950000;
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
            } else {
                return false;
            }
        } catch (\Exception $e) {
            // Handle any errors here
            Log::error($e->getMessage());
            return false;
        }
    }
}

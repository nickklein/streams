<?php

namespace NickKlein\Stream\Services\Stream;

use NickKlein\Stream\Interfaces\StreamServiceInterface;
use NickKlein\Stream\Models\UserStreamHandles;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class YouTube implements StreamServiceInterface
{

    const THRESHOLD = 700000;

    public function getProfiles(int $userId)
    {
        $streamHandles = UserStreamHandles::where('user_id', $userId)
            ->streamHandleFilterPlatform('youtube')
            ->get();

        $response = [];
        foreach ($streamHandles as $stream) {
            $response[] = [
                'id' => $stream->id,
                'name' => $stream->streamHandle->name,
                'url' => $stream->streamHandle->channel_url,
                'isLive' => $this->isAccountLive($stream->streamHandle->channel_id)
            ];
        }

        return $response;
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

<?php

namespace NickKlein\Streams\Services\Stream;

use App\Models\AccessToken;
use Carbon\Carbon;
use Exception;
use NickKlein\Streams\Interfaces\StreamServiceInterface;
use GuzzleHttp\Client;
use NickKlein\Streams\Repositories\StreamRepository;

class Kick implements StreamServiceInterface
{
    const CACHE_MINUTES = 15;
    const NAME = 'kick';

    public function __construct(public StreamRepository $streamRepository)
    {
        //
    }

    public function getUsersGroupedStreamerHandles(int $userId, int $favourites = 0): array
    {
        $response = [];
        $streamHandles = $this->streamRepository->getUsersGroupedStreamerHandles($userId, self::NAME, $favourites);

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
        $targetStream = $this->streamRepository->getUsersStreamHandles($userId, $userStreamId);

        if (!$targetStream) {
            return [];
        }

        $handle = $targetStream->streamer->streamHandles->first();
        if (!$handle) {
            return [];
        }

        $isCacheExpired = $this->streamRepository->isLastSyncExpired($userId, $userStreamId, self::CACHE_MINUTES);

        $isLive = $targetStream->is_live;
        if ($isCacheExpired) {
            $isLive = $this->isChannelLive($handle->channel_id);
            $targetStream->is_live = $isLive;
            $targetStream->last_synced_at = now();
            $targetStream->save();
        }

        $platforms = $targetStream->streamer->streamHandles->map(function ($handle) {
            return [
                'name' => $handle->platform,
                'url' => $handle->channel_url,
            ];
        })->values()->toArray();

        return [
            'id' => $targetStream->id,
            'name' => $targetStream->streamer->name,
            'url' => $handle->channel_url,
            'isLive' => $isLive,
            'platforms' => $platforms,
        ];
    }

    private function getAccessToken(): array
    {
        if ($this->isAccessTokenValid()) {
            $token = AccessToken::where('name', self::NAME)->first();
            return [
                'access_token' => $token->token,
                'expires_in' => Carbon::parse($token->expires_at)->diffInSeconds(Carbon::now())
            ];
        }

        return $this->generateAccessToken();
    }

    private function isAccessTokenValid(): bool
    {
        return AccessToken::where('name', self::NAME)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    private function generateAccessToken(): array
    {
        $client = new Client();

        $response = $client->post('https://id.kick.com/oauth/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.kick.client_id'),
                'client_secret' => config('services.kick.client_secret'),
            ]
        ]);

        $body = $response->getBody();
        $json = json_decode($body, true);

        AccessToken::updateOrCreate(
            ['name' => self::NAME],
            [
                'token' => $json['access_token'],
                'expires_at' => Carbon::now()->addSeconds($json['expires_in'])
            ]
        );

        return [
            'access_token' => $json['access_token'],
            'expires_at' => Carbon::now()->addSeconds($json['expires_in']),
        ];
    }

    public function isChannelLive(string $channelSlug): bool
    {
        $client = new Client([
            'timeout' => 30,
            'http_errors' => false,
        ]);

        try {
            $token = $this->getAccessToken();

            \Log::info('Kick API: Checking live status', ['slug' => $channelSlug, 'token' => substr($token['access_token'], 0, 10) . '...']);

            $response = $client->get('https://api.kick.com/public/v1/channels', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token['access_token'],
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'slug' => $channelSlug,
                ]
            ]);

            \Log::info('Kick API: Response status', ['status' => $response->getStatusCode()]);

            if ($response->getStatusCode() !== 200) {
                \Log::error('Kick API: Non-200 response', ['status' => $response->getStatusCode(), 'body' => $response->getBody()->getContents()]);
                return false;
            }

            $data = json_decode($response->getBody()->getContents(), true);

            \Log::info('Kick API: Response data', ['data' => $data]);

            // Check if we have data and the stream is live
            if (!empty($data['data'][0]['stream'])) {
                $isLive = $data['data'][0]['stream']['is_live'] === true;
                \Log::info('Kick API: is_live result', ['is_live' => $isLive]);
                return $isLive;
            }

            \Log::info('Kick API: No stream data found');
            return false;
        } catch (Exception $e) {
            \Log::error('Kick API: Exception', ['error' => $e->getMessage()]);
            return false;
        }
    }
}

<?php

namespace NickKlein\Streams\Services\Stream;

use App\Models\AccessToken;
use NickKlein\Streams\Interfaces\StreamServiceInterface;
use Carbon\Carbon;
use NickKlein\Streams\Repositories\StreamRepository;

class Twitch implements StreamServiceInterface
{
    const NAME = 'twitch';
    const CACHE_MINUTES = 15;

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
        $targetStream = $this->streamRepository->getUsersStreamHandles($userId, $userStreamId, self::NAME);

        if (!$targetStream) {
            return [];
        }

        $handle = $targetStream->streamer->streamHandles
            ->where('platform', self::NAME)
            ->first();
        if (!$handle) {
            return [];
        }
        
        // Determine if we need fresh live status
        $isCacheExpired = $this->streamRepository->isLastSyncExpired($userId, $userStreamId, self::CACHE_MINUTES);
        
        $isLive = $targetStream->is_live;
        if ($isCacheExpired) {
            // get the access token and the live status
            $token = $this->getAccessToken();
            $isLive = $this->isAccountLive($handle->channel_id, $token);

            // Update user stream handle row
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
        
        // Return the profile data
        return [
            'id' => $targetStream->id,
            'name' => $targetStream->streamer->name,
            'url' => $handle->channel_url,
            'isLive' => $isLive,
            'platforms' => $platforms,
        ];
    }

    private function getAccessToken()
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

    public function generateAccessToken()
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->post('https://id.twitch.tv/oauth2/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'client_id' => config('services.twitch.client_id'), // Replace with your client ID
                'client_secret' => config('services.twitch.client_secret'), // Replace with your client secret
                'grant_type' => 'client_credentials'
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

    private function isAccountLive(string $channel, array $token): bool
    {

        $client = new \GuzzleHttp\Client();

        $response = $client->get("https://api.twitch.tv/helix/streams", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Client-ID' => config('services.twitch.client_id')
            ],
            'query' => [
                'user_login' => $channel
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        return !empty($body['data']);
    }
}

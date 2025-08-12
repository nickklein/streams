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
    const CACHE_MINUTES = 0;
    const NAME = 'kick';

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
        if ($isCacheExpired) {
            // get the access token and the live status
            $token = $this->getAccessToken();
            $isLive = $this->isAccountLive($handle->channel_id, $token);

            // Update user stream handle row
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

        $response = $client->post('https://id.kick.com/oauth/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.kick.client_id'),
                'client_secret' => config('services.kick.client_secret')
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

        $response = $client->get("https://kick.com/api/v1/channels/{$channel}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token['access_token'],
                'Accept' => 'application/json'
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        return isset($body['livestream']) && 
               $body['livestream'] !== null && 
               isset($body['livestream']['is_live']) && 
               $body['livestream']['is_live'] === true;
    }
}

<?php

namespace NickKlein\Streams\Services\Stream;

use App\Models\AccessToken;
use NickKlein\Streams\Interfaces\StreamServiceInterface;
use Carbon\Carbon;
use NickKlein\Streams\Repositories\StreamRepository;

class Twitch implements StreamServiceInterface
{
    const NAME = 'twitch';

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
                $token = $this->getAccessToken();
                foreach($streamHandles as $handle) {
                    return [
                        'id' => $stream->id,
                        'name' => $stream->streamer->name,
                        'url' => $handle->channel_url,
                        'isLive' => $this->isAccountLive($handle->channel_id, $token)
                    ];
                } 
            }
        }

        return [];
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

<?php

namespace NickKlein\Stream\Services\Stream;

use NickKlein\Stream\Interfaces\StreamServiceInterface;
use App\Models\AccessToken;
use NickKlein\Stream\Models\UserStreamHandles;
use Carbon\Carbon;

class Twitch implements StreamServiceInterface
{
    const NAME = 'twitch';

    public function getProfiles(int $userId)
    {
        $token  = $this->getAccessToken();
        $streamHandles = UserStreamHandles::where('user_id', $userId)
            ->streamHandleFilterPlatform('twitch')
            ->get();

        $response = [];

        foreach ($streamHandles as $stream) {
            $response[] = [
                'id' => $stream->id,
                'name' => $stream->streamHandle->name,
                'url' => $stream->streamHandle->channel_url,
                'isLive' => $this->isAccountLive($stream->streamHandle->channel_id, $token)
            ];
        }

        return $response;
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

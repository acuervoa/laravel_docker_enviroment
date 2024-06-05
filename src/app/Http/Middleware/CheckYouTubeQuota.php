<?php

namespace App\Http\Middleware;

use Closure;
use Google_Client;
use Google_Service_YouTube;
use Illuminate\Support\Facades\Storage;

class CheckYouTubeQuota
{
    public function handle($request, Closure $next)
    {
        if (app()->environment('testing')) {
            // Durante los tests, no verificar la cuota
            return $next($request);
        }

        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);

        if (Storage::exists('youtube_token.json')) {
            $token = json_decode(Storage::get('youtube_token.json'), true);
            $client->setAccessToken($token);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                Storage::put('youtube_token.json', json_encode($client->getAccessToken()));
            }
        }

        try {
            $youtube = new Google_Service_YouTube($client);
            $youtube->channels->listChannels('snippet', ['mine' => true, 'maxResults' => 1]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'YouTube API quota exceeded or another error occurred'], 429);
        }

        return $next($request);
    }
}


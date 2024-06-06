<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function notifyNewVideos()
    {
        $users = User::with('subscriptions.channel.videos')->get();

        foreach ($users as $user) {
            $newVideos = $this->getNewVideosForUser($user);

            if (!empty($newVideos)) {
                // Aquí puedes enviar una notificación al usuario, por ejemplo, por correo electrónico
                // Mail::to($user->email)->send(new NewVideosNotification($newVideos));
                Log::info('New videos for user ' . $user->id, $newVideos);
            }
        }

        return response()->json(['message' => 'Notifications sent']);
    }

    private function getNewVideosForUser($user)
    {
        $newVideos = [];

        foreach ($user->subscriptions as $subscription) {
            $channel = $subscription->channel;
            $latestVideo = $channel->videos()->latest()->first();

            if ($latestVideo && $latestVideo->created_at > now()->subDay()) {
                $newVideos[] = $latestVideo;
            }
        }

        return $newVideos;
    }
}


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Channel;
use App\Models\Video;

class NotificationController extends Controller
{
    public function notifyNewVideos()
    {
        $users = User::with('subscriptions.channel.videos')->get();

        foreach ($users as $user) {
            $newVideos = [];

            foreach ($user->subscriptions as $subscription) {
                $channel = $subscription->channel;
                $latestVideo = $channel->videos()->latest()->first();

                if ($latestVideo && $latestVideo->created_at > now()->subDay()) {
                    $newVideos[] = $latestVideo;
                }
            }

            if (!empty($newVideos)) {
                // Aquí puedes enviar una notificación al usuario, por ejemplo, por correo electrónico
                // Mail::to($user->email)->send(new NewVideosNotification($newVideos));
                \Log::info('New videos for user ' . $user->id, $newVideos);
            }
        }

        return response()->json(['message' => 'Notifications sent']);
    }
}


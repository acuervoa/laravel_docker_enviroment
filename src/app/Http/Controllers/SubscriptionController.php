<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Channel;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        try {
            $channel = Channel::where('youtube_id', $request->youtube_id)->first();
            if (!$channel) {
                return response()->json(['error' => 'Channel not found'], 404);
            }

            $subscription = Subscription::create([
                'user_id' => Auth::id(),
                'channel_id' => $channel->id,
            ]);

            $channel->increment('subscriber_count');

            return response()->json($subscription, 201);
        } catch (\Exception $e) {
            \Log::error('Error subscribing to channel: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function unsubscribe($id)
    {
        try {
            $subscription = Subscription::where('user_id', Auth::id())
                ->where('channel_id', $id)
                ->first();

            if (!$subscription) {
                return response()->json(['error' => 'Subscription not found'], 404);
            }

            $subscription->delete();

            $channel = Channel::find($id);
            if ($channel && $channel->subscriber_count > 0) {
                $channel->decrement('subscriber_count');
            }

            return response()->json(null, 204);
        } catch (\Exception $e) {
            \Log::error('Error unsubscribing from channel: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function index()
    {
        try {
            $subscriptions = Auth::user()->subscriptions()->with('channel')->get();

            return response()->json($subscriptions);
        } catch (\Exception $e) {
            \Log::error('Error fetching subscriptions: ' . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}


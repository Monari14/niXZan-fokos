<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Notifications\UserFollowed;

class UserController extends Controller
{
    public function follow(Request $request, $username)
    {
        $userToFollow = User::where('username', $username)->first();

        if (!$userToFollow) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        if ($request->user()->id === $userToFollow->id) {
            return response()->json(['message' => 'Você não pode seguir a si mesmo.'], 400);
        }

        $alreadyFollowing = Follower::where('follower_id', $request->user()->id)
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($alreadyFollowing) {
            return response()->json(['message' => 'Você já está seguindo este usuário.'], 400);
        }

        Follower::create([
            'follower_id' => $request->user()->id,
            'following_id' => $userToFollow->id,
        ]);

        // Chama a notificação de Follow
        $userToFollow->notify(new UserFollowed($request->user()));

        return response()->json(['message' => 'Agora você está seguindo ' . $username]);
    }

    public function unfollow(Request $request, $username)
    {
        $userToUnfollow = User::where('username', $username)->first();

        if (!$userToUnfollow) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        Follower::where('follower_id', $request->user()->id)
            ->where('following_id', $userToUnfollow->id)
            ->delete();

        return response()->json(['message' => 'Você deixou de seguir ' . $username]);
    }

    public function followers($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $followers = $user->followers()->with('follower:id,username')->get()->pluck('follower');

        return response()->json($followers);
    }

    public function following($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        $following = $user->following()->with('following:id,username')->get()->pluck('following');

        return response()->json($following);
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);
        return response()->json([
            'notifications' => [
                'id' => $notifications->pluck('id'),
                'data' => $notifications->pluck('data'),
                'read_at' => $notifications->pluck('read_at'),
            ],
        ]);
    }

    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return response()->json(['message' => 'Notificação marcada como lida']);
        }

        return response()->json(['message' => 'Notificação não encontrada'], 404);
    }

    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'is_private' => 'required|boolean',
        ]);

        $user = $request->user();
        $user->is_private = $request->is_private;
        $user->save();

        return response()->json(['message' => 'Configuração de privacidade atualizada.', 'is_private' => $user->is_private]);
    }
}

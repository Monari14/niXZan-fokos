<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follower;
use Illuminate\Http\Request;
use App\Notifications\UserFollowed;
use App\Http\Resources\FollowersResource;

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

        $alreadyFollowing = Follower::where('id_seguidor', $request->user()->id)
            ->where('id_seguindo', $userToFollow->id)
            ->exists();

        if ($alreadyFollowing) {
            return response()->json(['message' => 'Você já está seguindo este usuário.'], 400);
        }

        Follower::create([
            'id_seguidor' => $request->user()->id,
            'id_seguindo' => $userToFollow->id,
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

        $follower = Follower::where('id_seguidor', $request->user()->id)
            ->where('id_seguindo', $userToUnfollow->id)
            ->first();

        if (!$follower) {
            return response()->json(['message' => 'Você não está seguindo este usuário.'], 400);
        }

        $follower->delete();

        return response()->json(['message' => 'Você deixou de seguir ' . $username]);
    }

    public function followers($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        // Pega os seguidores do usuário
        $followers = Follower::with('seguidor')
            ->where('id_seguindo', $user->id)
            ->get()
            ->pluck('seguidor');

        return FollowersResource::collection($followers);
    }

    public function following($username)
    {
        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        }

        // Pega os usuários que o usuário está seguindo
        $following = Follower::with('seguindo')
            ->where('id_seguidor', $user->id)
            ->get()
            ->pluck('seguindo');

        return FollowersResource::collection($following);
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
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewLiked extends Notification
{
    use Queueable;

    protected $liker;
    protected $momentoId;

    public function __construct($liker, $momentoId)
    {
        $this->liker = $liker;
        $this->momentoId = $momentoId;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->liker->username} curtiu seu post.",
            'momento_id' => $this->momentoId,
        ];
    }
}

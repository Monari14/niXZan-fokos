<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommented extends Notification
{
    use Queueable;

    protected $commenter;
    protected $id_new;

    public function __construct($commenter, $id_new)
    {
        $this->commenter = $commenter;
        $this->id_new = $id_new;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "{$this->commenter->username} comentou seu fok!",
            'id_new' => $this->id_new,
        ];
    }
}

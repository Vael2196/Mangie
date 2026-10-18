<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BoardInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $boardName,
        public readonly string $inviterName,
        public readonly string $role,
        public readonly string $joinUrl,
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject("Invitation to {$this->boardName} on Mangie")
            ->greeting('You’re invited!')
            ->line("{$this->inviterName} invited you to join {$this->boardName} as a {$this->role}.")
            ->line('Sign in or create an account with this email address, verify your email, then choose Join board.')
            ->action('View invitation', $this->joinUrl)
            ->line('This invitation expires in 7 days. If you were not expecting it, you can ignore this email.');
    }
}

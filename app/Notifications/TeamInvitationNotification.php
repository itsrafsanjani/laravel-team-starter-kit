<?php

namespace App\Notifications;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TeamInvitation $invitation
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $invitationUrl = route('team-invitations.show', $this->invitation);

        return (new MailMessage)
            ->subject('You\'re invited to join '.$this->invitation->team->name)
            ->greeting('Hello!')
            ->line('You have been invited to join **'.$this->invitation->team->name.'** as a **'.$this->invitation->role.'**.')
            ->action('Accept Invitation', $invitationUrl)
            ->line('This invitation will expire on '.$this->invitation->expires_at->format('F j, Y \a\t g:i A').'.')
            ->salutation('Thanks, '.config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'team_id' => $this->invitation->team->id,
            'team_name' => $this->invitation->team->name,
            'role' => $this->invitation->role,
            'invitation_id' => $this->invitation->id,
        ];
    }
}

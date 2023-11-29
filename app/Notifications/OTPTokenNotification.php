<?php

namespace App\Notifications;

use App\Mail\OTPTokenMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OTPTokenNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public $OTPToken, public $title)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        return new OTPTokenMessage($notifiable, $this->OTPToken, $this->title);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}

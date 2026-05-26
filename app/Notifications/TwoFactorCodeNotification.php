<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TwoFactorCodeNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Codigo de verificacion - Mi Chuzito')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Tu codigo de verificacion en dos pasos es:')
            ->line('**' . $this->code . '**')
            ->line('Este codigo expira en 10 minutos.')
            ->line('Si no solicitaste este codigo, ignora este mensaje.')
            ->salutation('El equipo de Mi Chuzito');
    }
}
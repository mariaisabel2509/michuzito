<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recuperacion de contrasena - Mi Chuzito')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Recibimos una solicitud para restablecer tu contrasena.')
            ->line('Tu codigo de recuperacion es:')
            ->line('**' . $this->code . '**')
            ->line('Este codigo expira en 15 minutos.')
            ->line('Si no solicitaste esto, ignora este mensaje.')
            ->salutation('El equipo de Mi Chuzito');
    }
}
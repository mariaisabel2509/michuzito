<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenido a Mi Chuzito')
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Tu cuenta ha sido creada exitosamente.')
            ->line('Ya puedes iniciar sesion y comenzar a usar la plataforma.')
            ->action('Iniciar sesion', url('/login'))
            ->line('Si tienes alguna duda, no dudes en contactarnos.')
            ->salutation('El equipo de Mi Chuzito');
    }
}
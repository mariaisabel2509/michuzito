<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentConfirmedNotification extends Notification
{
    public function __construct(
        public Payment $payment,
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pago confirmado - ' . $this->invoice->invoice_number)
            ->greeting('Hola, ' . $notifiable->name)
            ->line('Tu pago ha sido confirmado exitosamente.')
            ->line('**Metodo:** ' . ucfirst($this->payment->method))
            ->line('**Monto:** $' . number_format($this->payment->amount, 2))
            ->line('**Factura:** ' . $this->invoice->invoice_number)
            ->line('**Total con IVA:** $' . number_format($this->invoice->total, 2))
            ->action('Ver factura', url('/payments/invoice/' . $this->invoice->id))
            ->line('Gracias por tu compra.')
            ->salutation('El equipo de Mi Chuzito');
    }
}
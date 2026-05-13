<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Invoice $invoice
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice '.$this->invoice->number.' has been sent')
            ->greeting('Hello'.($notifiable->name ? ' '.$notifiable->name : ''))
            ->line('A new invoice has been issued for your account.')
            ->line('Invoice Number: '.$this->invoice->number)
            ->line('Due Date: '.$this->invoice->due_date)
            ->line('Total: '.$this->invoice->money($this->invoice->total))
            ->line('Please review the invoice and arrange payment by the due date.');
    }
}

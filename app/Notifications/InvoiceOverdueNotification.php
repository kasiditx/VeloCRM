<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceOverdueNotification extends Notification
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
            ->subject('Overdue invoice reminder: '.$this->invoice->number)
            ->greeting('Hello '.$notifiable->name)
            ->line('The following invoice is overdue and still unpaid.')
            ->line('Invoice Number: '.$this->invoice->number)
            ->line('Customer: '.($this->invoice->customer?->name ?? 'Unknown customer'))
            ->line('Due Date: '.$this->invoice->due_date)
            ->line('Balance Due: '.number_format((float) $this->invoice->balance_due, 2))
            ->line('Please follow up with the customer.');
    }
}

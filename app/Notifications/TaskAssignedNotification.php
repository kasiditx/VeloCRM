<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Task assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line(__('A task has been assigned to you in :app.', ['app' => velocrm_app_name()]))
            ->line('Task: ' . $this->task->title)
            ->line('Priority: ' . $this->task->priority)
            ->line('Status: ' . $this->task->status)
            ->line('Due Date: ' . ($this->task->due_date ?: 'Not set'));
    }
}

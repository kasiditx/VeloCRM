@props(['status' => 'default'])

@php
    $baseClasses = 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap ring-1 ring-inset shadow-sm';
    $normalized = str_replace([' ', '-'], '_', strtolower((string) $status));

    $colors = match($normalized) {
        'won', 'paid', 'active', 'done', 'completed', 'success', 'accepted' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20',
        'lost', 'overdue', 'cancelled', 'canceled', 'failed', 'danger', 'declined', 'high', 'urgent' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/20',
        'contacted', 'pending', 'in_progress', 'partially_paid', 'warning', 'medium' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/20',
        'qualified', 'sent', 'open', 'info', 'low' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/20',
        'new', 'draft', 'todo', 'to_do', 'default' => 'bg-gray-50 text-gray-600 ring-gray-500/20 dark:bg-gray-500/10 dark:text-gray-400 dark:ring-gray-500/20',
        default => 'bg-primary-50 text-primary-700 ring-primary-600/20 dark:bg-primary-500/10 dark:text-primary-400 dark:ring-primary-500/20',
    };
@endphp

<span {{ $attributes->merge(['class' => $baseClasses . ' ' . $colors]) }}>
    {{ $slot }}
</span>

@props([
    'icon' => 'spark',
    'title',
    'message' => null,
    'size' => 'default',
])

@php
    $padding = $size === 'compact' ? 'p-5' : 'px-6 py-12';
    $iconSize = $size === 'compact' ? 'h-11 w-11' : 'h-12 w-12';
@endphp

<div {{ $attributes->merge(['class' => "module-empty delight-empty {$padding}"]) }}>
    <span class="delight-icon mx-auto {{ $iconSize }}">
        @switch($icon)
            @case('lead')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a5 5 0 00-10 0v2m10-13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @break
            @case('customer')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 21h18M5 21V7l8-4 6 3v15M9 9h1m-1 4h1m4-4h1m-1 4h1M9 21v-4h6v4"/></svg>
                @break
            @case('invoice')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 14h6m-6 4h6M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>
                @break
            @case('proposal')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7h8M8 11h8m-8 4h5M6 3h9l3 3v15H6V3z"/></svg>
                @break
            @case('task')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 11l2 2 4-4M7 5h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                @break
            @case('calendar')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>
                @break
            @case('report')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 19V5m0 14h16M8 16v-5m4 5V8m4 8v-3"/></svg>
                @break
            @case('file')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01.88-7.903A5 5 0 1116.9 6L17 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/></svg>
                @break
            @case('note')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 10h8m-8 4h5m8-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @break
            @case('user')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 14a4 4 0 10-8 0m8 0a6 6 0 016 6H2a6 6 0 016-6m8 0H8"/></svg>
                @break
            @case('trash')
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 7l-.8 12.1A2 2 0 0116.2 21H7.8a2 2 0 01-2-1.9L5 7m5 4v6m4-6v6M4 7h16m-5 0V4H9v3"/></svg>
                @break
            @default
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>
        @endswitch
    </span>

    <p class="mt-3 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</p>
    @if($message)
        <p class="mx-auto mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">{{ $message }}</p>
    @endif
    @if(! $slot->isEmpty())
        <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

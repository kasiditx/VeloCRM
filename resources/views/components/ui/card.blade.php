<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900']) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800">
            {{ $header }}
        </div>
    @endif

    <div class="{{ isset($noPadding) && $noPadding ? '' : 'p-6' }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950 rounded-b-2xl">
            {{ $footer }}
        </div>
    @endif
</div>
<div class="relative w-full max-w-lg" x-data="{ open: false }" @click.away="open = false">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <input
            x-ref="searchInput"
            @keydown.window.prevent.cmd.k="$refs.searchInput.focus()"
            @keydown.window.prevent.ctrl.k="$refs.searchInput.focus()"
            wire:model.live.debounce.300ms="query"
            @focus="open = true"
            type="text"
            placeholder="{{ __('Search leads or customers... (:shortcut)', ['shortcut' => 'Ctrl/⌘ K']) }}"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg leading-5 bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out"
        >
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
            <kbd class="hidden sm:inline-flex items-center px-1.5 font-sans text-xs font-medium text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded">Ctrl/⌘ K</kbd>
        </div>
    </div>

    <!-- Results Dropdown -->
    <div
        x-show="open && $wire.query.length >= 2"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-900 shadow-xl rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
        style="display: none;"
    >
        <div class="max-h-96 overflow-y-auto">
            <!-- Leads Section -->
            @if(count($results['leads']) > 0)
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Leads') }}</span>
                </div>
                @foreach($results['leads'] as $lead)
                    <a href="{{ route('leads.show', $lead->id) }}" wire:navigate class="block px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900 transition border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-600 dark:text-primary-400 font-semibold text-xs">
                                L
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $lead->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $lead->company }}</div>
                            </div>
                            <div class="ml-auto">
                                <span class="px-2 py-0.5 text-[10px] rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-medium">
                                    {{ __($lead->status) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif

            <!-- Customers Section -->
            @if(count($results['customers']) > 0)
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customers') }}</span>
                </div>
                @foreach($results['customers'] as $customer)
                    <a href="{{ route('customers.show', $customer->id) }}" wire:navigate class="block px-4 py-3 hover:bg-primary-50 dark:hover:bg-primary-900 transition border-b border-gray-100 dark:border-gray-800">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-400 font-semibold text-xs">
                                C
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $customer->company }}</div>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif

            @if(count($results['leads']) == 0 && count($results['customers']) == 0)
                <div class="delight-empty px-4 py-8 text-center">
                    <span class="delight-icon mx-auto h-12 w-12">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10 21l6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </span>
                    <p class="mt-4 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Nothing matched yet.') }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Try a name, company, email, or phone number.') }}</p>
                    <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ __('Search checked leads and customers for ":query".', ['query' => $query]) }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

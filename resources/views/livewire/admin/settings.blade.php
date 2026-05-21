<div>
    <x-slot name="header">
        <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white">
            {{ __('Admin Settings') }}
        </h2>
    </x-slot>

    <div class="module-page">
        <div class="module-container">

            @if (session()->has('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                     class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/40 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 dark:border-red-900/60 dark:bg-red-950/40 px-4 py-3 text-sm text-red-700 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            <div class="module-header">
                <div>
                    <h1 class="module-title">{{ __('Settings') }}</h1>
                    <p class="module-subtitle">{{ __('Configure company profile, branding, email delivery, regional formats, API access, exports, and system health.') }}</p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6">

                {{-- Sidebar Tabs --}}
                <div class="md:w-56 shrink-0">
                    <div class="module-panel p-3 space-y-1">
                        @foreach([
                            ['tab' => 'general',  'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => __('General')],
                            ['tab' => 'branding', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z', 'label' => __('Branding')],
                            ['tab' => 'smtp',     'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'SMTP'],
                            ['tab' => 'regional', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'label' => __('Regional')],
                            ['tab' => 'payments', 'icon' => 'M3 10h18M7 15h.01M11 15h2M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z', 'label' => __('Payment Gateways')],
                            ['tab' => 'templates', 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'label' => __('Email Templates')],
                            ['tab' => 'api',       'icon' => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z', 'label' => __('API Tokens')],
                            ['tab' => 'exports',  'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'label' => __('Data Export')],
                            ['tab' => 'health',   'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => __('Health Check')],
                        ] as $item)
                            <button wire:click="setTab('{{ $item['tab'] }}')"
                                class="w-full text-left flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                                       {{ $activeTab === $item['tab'] ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                                {{ $item['label'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Content Area --}}
                <div class="flex-1 min-w-0">
                    <div class="module-panel p-6">

                        {{-- General --}}
                        <div class="{{ $activeTab === 'general' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('General Settings') }}</h3>
                            <form wire:submit.prevent="saveGeneral" class="space-y-5">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Company Name') }}</label>
                                    <input type="text" wire:model="company_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                    @error('company_name') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Company Address') }}</label>
                                    <textarea wire:model="company_address" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"></textarea>
                                    @error('company_address') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Site Title') }}</label>
                                    <input type="text" wire:model="site_title" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                    @error('site_title') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <hr class="border-gray-200 dark:border-gray-800">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Envato Purchase Code') }}</label>
                                    <input type="text" wire:model="envato_purchase_code" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm font-mono" placeholder="1a2b3c4d-5e6f-7g8h-9i0j-1k2l3m4n5o6p">
                                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 capitalize">Verifying this code ensures you receive updates and support.</p>
                                    @error('envato_purchase_code') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="pt-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="saveGeneral" class="action-button action-button-primary">
                                        <x-ui.loading-label target="saveGeneral" :label="__('Save Changes')" :loading="__('Saving...')" />
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Branding --}}
                        <div class="{{ $activeTab === 'branding' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Branding') }}</h3>
                            <form wire:submit.prevent="saveBranding" class="space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-center">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Logo') }}</label>
                                        <input type="file" wire:model="logo" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Recommended: 200×50px. Max 1MB.') }}</p>
                                        @error('logo') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex justify-center items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 min-h-[4rem]">
                                        @if ($logo)
                                            <img src="{{ $logo->temporaryUrl() }}" class="max-h-12 object-contain">
                                        @elseif($current_logo)
                                            <img src="{{ asset('uploads/' . $current_logo) }}" class="max-h-12 object-contain">
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('No Logo') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-center">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Favicon') }}</label>
                                        <input type="file" wire:model="favicon" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900 dark:file:text-primary-300">
                                        @error('favicon') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="flex justify-center items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 min-h-[4rem]">
                                        @if ($favicon)
                                            <img src="{{ $favicon->temporaryUrl() }}" class="w-8 h-8 object-contain">
                                        @elseif($current_favicon)
                                            <img src="{{ asset('uploads/' . $current_favicon) }}" class="w-8 h-8 object-contain">
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-gray-500">{{ __('No Favicon') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Primary UI Color') }}</label>
                                    <div class="flex items-center gap-4">
                                        <input type="color" wire:model="primary_color" class="h-10 w-20 border-0 p-0 rounded-lg cursor-pointer">
                                        <input type="text" wire:model.live.debounce.300ms="primary_color" placeholder="#4f46e5" maxlength="7" class="w-32 rounded-lg border-gray-300 bg-gray-100 px-3 py-1.5 font-mono text-sm text-gray-700 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    </div>
                                    @error('primary_color') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="pt-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="saveBranding,logo,favicon" class="action-button action-button-primary">
                                        <x-ui.loading-label target="saveBranding,logo,favicon" :label="__('Save Changes')" :loading="__('Saving...')" />
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- SMTP --}}
                        <div class="{{ $activeTab === 'smtp' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('SMTP Configuration') }}</h3>
                            <form wire:submit.prevent="saveSMTP" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Mail Host') }}</label>
                                    <input type="text" wire:model="mail_host" placeholder="smtp.mailtrap.io" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_host') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Mail Port') }}</label>
                                    <input type="text" wire:model="mail_port" placeholder="587" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_port') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Encryption') }}</label>
                                    <select wire:model="mail_encryption" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                    @error('mail_encryption') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Username') }}</label>
                                    <input type="text" wire:model="mail_username" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_username') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Password') }}</label>
                                    <input type="password" wire:model="mail_password" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_password') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Sender Email') }}</label>
                                    <input type="email" wire:model="mail_from_address" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_from_address') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Sender Name') }}</label>
                                    <input type="text" wire:model="mail_from_name" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                    @error('mail_from_name') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="sm:col-span-2 flex flex-wrap justify-between items-center gap-3 pt-2">
                                    <button type="button" wire:click="sendTestEmail" wire:loading.attr="disabled" wire:target="sendTestEmail,saveSMTP" class="action-button action-button-secondary">
                                        <x-ui.loading-label target="sendTestEmail" :label="__('Send Test Email')" :loading="__('Sending...')" />
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled" wire:target="saveSMTP,sendTestEmail" class="action-button action-button-primary">
                                        <x-ui.loading-label target="saveSMTP" :label="__('Save Changes')" :loading="__('Saving...')" />
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Regional --}}
                        <div class="{{ $activeTab === 'regional' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('Regional Settings') }}</h3>
                            <form wire:submit.prevent="saveRegional" class="space-y-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Currency Code') }}</label>
                                        <input type="text" wire:model="currency_code" placeholder="USD" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                        @error('currency_code') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Currency Symbol') }}</label>
                                        <input type="text" wire:model="currency_symbol" placeholder="$" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                        @error('currency_symbol') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Date Format') }}</label>
                                    <select wire:model="date_format" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="d/m/Y">DD/MM/YYYY ({{ now()->format('d/m/Y') }})</option>
                                        <option value="m/d/Y">MM/DD/YYYY ({{ now()->format('m/d/Y') }})</option>
                                        <option value="Y-m-d">YYYY-MM-DD ({{ now()->format('Y-m-d') }})</option>
                                        <option value="M d, Y">Month Day, Year ({{ now()->format('M d, Y') }})</option>
                                    </select>
                                    @error('date_format') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-950/50">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ __('Document Numbering') }}</h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Configure prefixes and the next running number for each document type in the current year.') }}</p>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                            <thead>
                                                <tr>
                                                    <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Type') }}</th>
                                                    <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Prefix') }}</th>
                                                    <th class="pb-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Next No.') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                @foreach($documentTypes as $type => $label)
                                                    <tr>
                                                        <td class="py-3 pr-4 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</td>
                                                        <td class="py-3 pr-4">
                                                            <input type="text" wire:model="document_number_prefixes.{{ $type }}" class="w-28 rounded-lg border-gray-300 uppercase shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm">
                                                            @error('document_number_prefixes.'.$type) <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                                        </td>
                                                        <td class="py-3">
                                                            <input type="number" min="1" max="999999" wire:model="document_number_next.{{ $type }}" class="w-28 rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 text-sm">
                                                            @error('document_number_next.'.$type) <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="saveRegional" class="action-button action-button-primary">
                                        <x-ui.loading-label target="saveRegional" :label="__('Save Changes')" :loading="__('Saving...')" />
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Payment Gateways --}}
                        <div class="{{ $activeTab === 'payments' ? 'block' : 'hidden' }}">
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Payment Gateways') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Choose how customers pay invoices from portal and public share links.') }}</p>
                            </div>

                            <form wire:submit.prevent="savePaymentGateways" class="space-y-6">
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Active Gateway') }}</label>
                                        <select wire:model="payment_driver" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                            @foreach($paymentGateways as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('payment_driver') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Mode') }}</label>
                                        <select wire:model="payment_mode" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                            <option value="test">{{ __('Test') }}</option>
                                            <option value="live">{{ __('Live') }}</option>
                                        </select>
                                        @error('payment_mode') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Payment Currency') }}</label>
                                        <input type="text" wire:model="payment_currency" maxlength="3" placeholder="THB" class="w-full rounded-lg border-gray-300 uppercase dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500">
                                        @error('payment_currency') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-gray-800 dark:bg-gray-950/50">
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ __('Manual / Bank Transfer') }}</h4>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Shown to customers when Manual is selected. Use this for bank account or PromptPay instructions.') }}</p>
                                    <textarea wire:model="payment_bank_transfer_instructions" rows="4" class="mt-3 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="{{ __('Bank name, account number, account holder, and payment confirmation instructions') }}"></textarea>
                                    @error('payment_bank_transfer_instructions') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror

                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('PromptPay ID') }}</label>
                                        <input type="text" wire:model="promptpay_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="{{ __('Mobile number, national ID, or corporate tax ID') }}">
                                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">{{ __('Used to generate PromptPay QR codes for THB invoices with an outstanding balance.') }}</p>
                                        @error('promptpay_id') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Stripe</h4>
                                        <div class="mt-4 space-y-3">
                                            <input type="text" wire:model="payment_stripe_public_key" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Public key">
                                            <input type="password" wire:model="payment_stripe_secret_key" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Secret key">
                                            <input type="password" wire:model="payment_stripe_webhook_secret" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Webhook HMAC secret">
                                        </div>
                                        @error('payment_stripe_public_key') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                        @error('payment_stripe_secret_key') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                        @error('payment_stripe_webhook_secret') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">PayPal</h4>
                                        <div class="mt-4 space-y-3">
                                            <input type="url" wire:model="payment_paypal_checkout_url" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Hosted checkout URL">
                                            <input type="password" wire:model="payment_paypal_webhook_secret" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Webhook HMAC secret">
                                        </div>
                                        @error('payment_paypal_checkout_url') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                        @error('payment_paypal_webhook_secret') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>

                                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-gray-100">Omise</h4>
                                        <div class="mt-4 space-y-3">
                                            <input type="url" wire:model="payment_omise_checkout_url" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Hosted checkout URL">
                                            <input type="password" wire:model="payment_omise_webhook_secret" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Webhook HMAC secret">
                                        </div>
                                        @error('payment_omise_checkout_url') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                        @error('payment_omise_webhook_secret') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
                                    {{ __('Webhook verification uses the X-VeloCRM-Signature header with HMAC-SHA256 over the raw request body. Keep webhook secrets private.') }}
                                </div>

                                <div class="pt-2">
                                    <button type="submit" wire:loading.attr="disabled" wire:target="savePaymentGateways" class="action-button action-button-primary">
                                        <x-ui.loading-label target="savePaymentGateways" :label="__('Save Payment Settings')" :loading="__('Saving...')" />
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Email Templates --}}
                        <div class="{{ $activeTab === 'templates' ? 'block' : 'hidden' }}">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Email Templates') }}</h3>
                                @if($editingTemplate)
                                    <button wire:click="cancelEdit" wire:loading.attr="disabled" wire:target="cancelEdit,saveTemplate" class="text-sm font-medium text-gray-500 transition hover:text-gray-700 disabled:pointer-events-none disabled:opacity-50">{{ __('Cancel') }}</button>
                                @endif
                            </div>

                            @if($editingTemplate)
                                <form wire:submit.prevent="saveTemplate" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Subject') }}</label>
                                        <input type="text" wire:model="template_subject" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        @error('template_subject') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ __('Body') }}</label>
                                        <textarea wire:model="template_body" rows="8" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm font-mono"></textarea>
                                        <p class="mt-2 text-xs text-gray-500">{{ __('Available placeholders') }}: <code>{customer_name}</code>, <code>{company_name}</code>, <code>{invoice_number}</code>, <code>{user_name}</code></p>
                                        @error('template_body') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="pt-2">
                                        <button type="submit" wire:loading.attr="disabled" wire:target="saveTemplate" class="action-button action-button-primary">
                                            <x-ui.loading-label target="saveTemplate" :label="__('Update Template')" :loading="__('Saving...')" />
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="grid gap-4">
                                    @forelse($templates as $template)
                                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $template['name'] }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $template['subject'] }}</p>
                                            </div>
                                            <button wire:click="editTemplate({{ $template['id'] }})" wire:loading.attr="disabled" wire:target="editTemplate({{ $template['id'] }})" class="text-sm font-semibold text-primary-600 transition hover:text-primary-700 disabled:pointer-events-none disabled:opacity-50">
                                                <x-ui.loading-label target="editTemplate({{ $template['id'] }})" :label="__('Edit')" :loading="__('Opening...')" />
                                            </button>
                                        </div>
                                    @empty
                                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
                                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('No email templates found.') }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Run the database seeder to install the default templates.') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        {{-- API Tokens --}}
                        <div class="{{ $activeTab === 'api' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('API Tokens') }}</h3>

                            @if($newApiToken)
                                <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/40">
                                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">{{ __('Copy this token now. It will not be shown again.') }}</p>
                                    <input type="text" readonly value="{{ $newApiToken }}" class="mt-3 w-full rounded-lg border-amber-300 bg-white font-mono text-xs text-gray-900 shadow-sm dark:border-amber-800 dark:bg-gray-950 dark:text-gray-100">
                                </div>
                            @endif

                            <form wire:submit.prevent="createApiToken" class="mb-6 flex flex-col gap-3 sm:flex-row">
                                <div class="min-w-0 flex-1">
                                    <label class="sr-only" for="api_token_name">{{ __('Token Name') }}</label>
                                    <input id="api_token_name" type="text" wire:model="api_token_name" placeholder="{{ __('Token Name') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                    @error('api_token_name') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" wire:loading.attr="disabled" wire:target="createApiToken" class="action-button action-button-primary">
                                    <x-ui.loading-label target="createApiToken" :label="__('Create Token')" :loading="__('Creating...')" />
                                </button>
                            </form>

                            <div class="space-y-3">
                                @forelse($apiTokens as $token)
                                    <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <h4 class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $token['name'] }}</h4>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('Created') }}: {{ $token['created_at'] ?? '-' }} · {{ __('Last used') }}: {{ $token['last_used_at'] ?? '-' }}
                                            </p>
                                        </div>
                                        <button type="button" wire:click="revokeApiToken({{ $token['id'] }})" wire:loading.attr="disabled" wire:target="revokeApiToken({{ $token['id'] }})" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                                            {{ __('Revoke') }}
                                        </button>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center dark:border-gray-700 dark:bg-gray-800">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('No API tokens found.') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Data Exports --}}
                        <div class="{{ $activeTab === 'exports' ? 'block' : 'hidden' }}">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">{{ __('System Backups & Data Export') }}</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ __('Export All Leads') }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Download a complete Excel snapshot of your leads pipeline.') }}</p>
                                    </div>
                                    <a href="{{ route('export.leads') }}" target="_blank" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700 shrink-0">{{ __('Download XLSX') }}</a>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                                    <div>
                                        <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ __('Export All Customers') }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Download a complete Excel snapshot of your active customers.') }}</p>
                                    </div>
                                    <a href="{{ route('export.customers') }}" target="_blank" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 shrink-0">{{ __('Download XLSX') }}</a>
                                </div>
                            </div>
                        </div>

                        {{-- Health Check --}}
                        <div class="{{ $activeTab === 'health' ? 'block' : 'hidden' }}">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('System Health Check') }}</h3>
                                <span class="inline-flex px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-xs font-semibold rounded-full">PHP {{ $health['php_version_text'] }}</span>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">{{ __('Required PHP Extensions') }}</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                        @foreach($health['extensions'] as $ext => $valid)
                                            <div class="flex items-center gap-2 p-2.5 rounded-lg border {{ $valid ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/40' : 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/40' }}">
                                                @if($valid)
                                                    <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                @else
                                                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                                @endif
                                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ strtoupper($ext) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">{{ __('Writable Folders') }}</h4>
                                    <div class="space-y-2">
                                        @foreach($health['permissions'] as $path => $valid)
                                            <div class="flex items-center justify-between p-3 rounded-lg border {{ $valid ? 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-950/40' : 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/40' }}">
                                                <span class="text-xs font-mono text-gray-600 dark:text-gray-300 break-all">{{ $path }}</span>
                                                @if($valid)
                                                    <span class="text-xs font-bold text-green-700 dark:text-green-400 uppercase ml-3 shrink-0">{{ __('Writable') }}</span>
                                                @else
                                                    <span class="text-xs font-bold text-red-700 dark:text-red-400 uppercase ml-3 shrink-0">{{ __('Not Writable') }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

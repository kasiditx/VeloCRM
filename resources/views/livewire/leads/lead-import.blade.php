<div class="py-10">
    <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Import Leads') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Upload a CSV, map the columns, preview the rows, and import leads in bulk.') }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('leads.index') }}" wire:navigate class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                    {{ __('Back to leads') }}
                </a>
                <a href="{{ route('leads.import.template') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">
                    {{ __('Download Sample CSV') }}
                </a>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('1. Upload CSV') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Accepted columns: name, email, phone, company, status, source, value, notes, assigned to.') }}</p>
                </div>
                <div class="space-y-4 p-5">
                    <div>
                        <input wire:model.live="file" type="file" accept=".csv,text/csv" class="block w-full rounded-lg border border-gray-300 text-sm shadow-sm file:mr-4 file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100 dark:file:bg-primary-950 dark:file:text-primary-300">
                        @error('file')
                            <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="rounded-lg border border-dashed border-gray-300 p-4 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                        {{ __('Duplicate handling: existing leads with the same email are skipped; if no email is present, existing phone numbers are skipped instead.') }}
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Import Summary') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('The result appears here after the import finishes.') }}</p>
                </div>
                <div class="space-y-4 p-5">
                    @if ($importSummary)
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-lg bg-emerald-50 p-4 dark:bg-emerald-950/40">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">{{ __('Imported') }}</p>
                                <p class="mt-1 text-2xl font-semibold text-emerald-800 dark:text-emerald-200">{{ $importSummary['imported'] }}</p>
                            </div>
                            <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-950/40">
                                <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">{{ __('Skipped') }}</p>
                                <p class="mt-1 text-2xl font-semibold text-amber-800 dark:text-amber-200">{{ $importSummary['skipped'] }}</p>
                            </div>
                            <div class="rounded-lg bg-rose-50 p-4 dark:bg-rose-950/40">
                                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">{{ __('Failed') }}</p>
                                <p class="mt-1 text-2xl font-semibold text-rose-800 dark:text-rose-200">{{ $importSummary['failed'] }}</p>
                            </div>
                        </div>

                        @if ($importSummary['failures'] !== [])
                            <div class="rounded-lg border border-rose-200 bg-rose-50 p-4 dark:border-rose-900/60 dark:bg-rose-950/30">
                                <h3 class="text-sm font-semibold text-rose-700 dark:text-rose-300">{{ __('Top validation errors') }}</h3>
                                <ul class="mt-3 space-y-2 text-sm text-rose-700 dark:text-rose-300">
                                    @foreach ($importSummary['failures'] as $failure)
                                        <li>
                                            Row {{ $failure['row'] }} / {{ $failure['attribute'] }}:
                                            {{ implode(', ', $failure['errors']) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @else
                        <x-ui.empty-state
                            icon="lead"
                            :title="__('No import has been run yet.')"
                            :message="__('Upload a CSV and preview it before importing leads into the pipeline.')"
                            size="compact"
                        />
                    @endif

                    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('Assignable Users') }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('If you map an `Assigned To` column, values can match a user email, name, or numeric ID.') }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($assignableUsers as $user)
                                <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    {{ $user->name }} ({{ $user->email }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($csvHeaders !== [])
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('2. Map Columns') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Name is required. Leave fields unmapped to ignore them.') }}</p>
                    @error('columnMap')
                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid gap-4 p-5 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($csvHeaders as $index => $header)
                        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $header }}</p>
                            <select wire:model="columnMap.{{ $index }}" class="mt-3 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                                <option value="">{{ __('Ignore this column') }}</option>
                                @foreach ($fieldOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 p-5 dark:border-gray-800">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('3. Preview Rows') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Showing the first :count rows before import.', ['count' => count($previewRows)]) }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-950">
                            <tr>
                                @foreach ($csvHeaders as $header)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $header }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
                            @foreach ($previewRows as $row)
                                <tr>
                                    @foreach ($csvHeaders as $index => $header)
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $row[$index] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-gray-200 p-5 dark:border-gray-800">
                    <button wire:click="import" wire:loading.attr="disabled" wire:target="import" class="action-button action-button-success">
                        <x-ui.loading-label target="import" :label="__('Start Import')" :loading="__('Importing...')" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

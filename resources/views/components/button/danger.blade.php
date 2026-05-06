<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-1.5 justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:pointer-events-none disabled:opacity-55 disabled:cursor-not-allowed disabled:saturate-50']) }}>
    {{ $slot }}
</button>

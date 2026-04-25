<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex items-center justify-center gap-2 px-6 py-2.5
        rounded-lg font-semibold text-sm tracking-wide
        bg-[var(--color-primary)] text-[var(--color-on-primary-container)]
        hover:bg-[var(--color-primary-dim)] hover:shadow-[0_0_20px_rgba(159,167,255,0.35)]
        focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:ring-offset-2 focus:ring-offset-[var(--color-surface)]
        active:scale-[0.98]
        disabled:opacity-50 disabled:cursor-not-allowed
        transition-all duration-200 ease-in-out'
]) }}>
    {{ $slot }}
</button>

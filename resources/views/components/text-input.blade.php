@props(['disabled' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'w-full rounded-lg px-4 py-2.5 text-sm
            bg-[var(--color-surface-container)] text-[var(--color-on-surface)]
            border border-[var(--color-outline-variant)]
            placeholder:text-[var(--color-on-surface-variant)]
            focus:outline-none focus:ring-2 focus:ring-[var(--color-primary)] focus:border-transparent
            disabled:opacity-50 disabled:cursor-not-allowed
            transition duration-150 ease-in-out'
    ]) }}
>

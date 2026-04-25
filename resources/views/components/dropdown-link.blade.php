<button {{ $attributes->merge([
    'class' => 'w-full flex items-center gap-3 px-4 py-2.5 text-sm text-left
        text-[var(--color-on-surface-variant)]
        hover:bg-[var(--color-surface-container-highest)]
        hover:text-[var(--color-on-surface)]
        transition-colors duration-150 cursor-pointer'
]) }}>
    {{ $slot }}
</button>

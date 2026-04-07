@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-semibold uppercase tracking-wider text-[var(--color-on-surface-variant)] mb-1']) }}>
    {{ $value ?? $slot }}
</label>

@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-2 rounded-lg p-3 text-sm font-medium text-[var(--color-primary)] bg-[var(--color-primary)]/10 border border-[var(--color-primary)]/20']) }}>
        <span class="material-symbols-outlined shrink-0" style="font-size:18px;">check_circle</span>
        <span>{{ $status }}</span>
    </div>
@endif

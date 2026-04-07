@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs text-[var(--color-error)] space-y-1 mt-1.5']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 14px;">error</span>
                {{ $message }}
            </li>
        @endforeach
    </ul>
@endif

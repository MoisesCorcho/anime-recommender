@props([
    'label',
    'model',
    'options'     => [],   // associative array: ['value' => 'Label', ...]
    'placeholder' => 'All',
])

<div>
    <label class="block text-label text-xs font-bold text-on-surface-variant uppercase tracking-widest mb-2">
        {{ $label }}
    </label>
    <select
        wire:model.live="{{ $model }}"
        class="w-full bg-surface-container-lowest border-none rounded-xl py-3 px-4
               text-on-surface focus:ring-2 focus:ring-primary transition-all
               cursor-pointer text-sm shadow-inner font-medium"
    >
        @if($placeholder !== '')
            <option value="all">{{ $placeholder }}</option>
        @endif
        @foreach($options as $value => $optionLabel)
            <option value="{{ $value }}">{{ $optionLabel }}</option>
        @endforeach
        {{-- Slot for dynamic @foreach options (e.g. years/genres from DB) --}}
        {{ $slot }}
    </select>
</div>

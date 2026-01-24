@props([
    'label',
    'value',
    'icon',
    'color' => 'indigo',
    'progress' => 0,
    'subtext' => null,
    'alert' => false,
    'unavailable' => false
])

@php
    // Tailwind dynamic class mapping to ensure JIT picks them up
    $colors = [
        'indigo'  => ['text' => 'text-indigo-400',  'bg' => 'bg-indigo-500',  'shadow' => 'shadow-indigo-500/40'],
        'emerald' => ['text' => 'text-emerald-400', 'bg' => 'bg-emerald-500', 'shadow' => 'shadow-emerald-500/40'],
        'amber'   => ['text' => 'text-amber-400',   'bg' => 'bg-amber-500',   'shadow' => 'shadow-amber-500/40'],
        'red'     => ['text' => 'text-red-400',     'bg' => 'bg-red-500',     'shadow' => 'shadow-red-500/40'],
        'zinc'    => ['text' => 'text-zinc-400',    'bg' => 'bg-zinc-500',    'shadow' => 'shadow-zinc-500/40'],
    ];

    $selectedColor = $colors[$color] ?? $colors['indigo'];
@endphp

<div {{ $attributes->merge([
    'class' => "rounded-xl border border-zinc-800 bg-zinc-900/40 p-5 hover:border-zinc-700 transition-all duration-200 "
    . ($alert ? 'ring-2 ring-red-500/50 animate-pulse' : '')
    . ($unavailable ? 'opacity-60' : '')
]) }} role="article">

    <div class="flex items-center justify-between mb-4">
        {{-- Dynamic Flux Icon with mapped color --}}
        <flux:icon :name="$icon" class="h-4 w-4 {{ $selectedColor['text'] }}" />

        <flux:badge size="xs" :color="$unavailable ? 'zinc' : $color" variant="filled" class="uppercase">
            {{ $unavailable ? 'N/A' : 'Live' }}
        </flux:badge>
    </div>

    <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 mb-1">{{ $label }}</p>
    <p class="text-2xl font-semibold text-white">{{ $value }}</p>

    <div class="mt-4 flex items-center gap-3">
        {{-- Smooth Progress Bar --}}
        <div class="h-1.5 flex-1 rounded-full bg-zinc-800 overflow-hidden">
            <div
                class="h-full rounded-full {{ $selectedColor['bg'] }} {{ $selectedColor['shadow'] }} shadow-sm transition-all duration-1000 ease-in-out"
                style="width: {{ min(max($progress, 0), 100) }}%"
                role="progressbar"
            ></div>
        </div>

        @if($subtext)
            <span class="text-[10px] font-medium text-zinc-400 whitespace-nowrap">
                {{ $subtext }}
            </span>
        @endif
    </div>

    @if($alert)
        <div class="mt-3 flex items-center gap-2 text-xs text-red-400">
            <flux:icon name="exclamation-triangle" class="h-3 w-3" />
            <span>High Usage Alert</span>
        </div>
    @endif
</div>

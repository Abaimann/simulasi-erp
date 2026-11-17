@props([
    'classes' => 'bg-slate-100 text-slate-800 border-slate-200',
])

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-md border px-2 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>

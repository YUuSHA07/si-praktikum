@props(['active', 'href'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-4 py-2 text-white bg-indigo-800 rounded-md shadow-sm font-semibold'
            : 'flex items-center px-4 py-2 text-indigo-200 hover:bg-indigo-800 hover:text-white rounded-md transition-all duration-200';
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    <span class="text-sm">{{ $slot }}</span>
</a>
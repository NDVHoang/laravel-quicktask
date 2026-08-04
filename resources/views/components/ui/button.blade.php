@props(['href' => null, 'disabled' => false, 'variant' => 'primary'])

@php
    $baseClasses = 'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    $variants = [
        'primary' => 'text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600',
        'secondary' => 'text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500 border border-gray-300 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700',
        'danger' => 'text-white bg-red-600 hover:bg-red-700 focus:ring-red-500 dark:bg-red-500 dark:hover:bg-red-600',
    ];

    $classes = $baseClasses . ' ' . $variants[$variant];

    if ($disabled) {
        $classes .= ' opacity-50 cursor-not-allowed pointer-events-none';
    }
@endphp

@if ($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@elseif ($href && $disabled)
    <a aria-disabled="true" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button @disabled($disabled) @if($disabled) aria-disabled="true" @endif {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

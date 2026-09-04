<button {{ $attributes->merge(['class' => 'min-h-11 px-4 py-2 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700']) }}>
    {{ $slot }}
</button>
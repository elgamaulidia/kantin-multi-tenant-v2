<x-layouts.customer title="Katalog Menu">
    <livewire:catalog::menu-catalog :canteen-slug="is_string($canteen) ? $canteen : $canteen->slug" />
</x-layouts.customer>
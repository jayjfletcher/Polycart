<x-atrium::card :title="__('polycart::polycart.widget_carts_by_type')">
    <div class="flex flex-wrap gap-2">
        @foreach ($counts as $type => $count)
            <a class="flex items-center gap-2 rounded-radius border border-outline px-3 py-2 transition hover:bg-surface-alt dark:border-outline-dark dark:hover:bg-surface-dark-alt"
               href="{{ route('atrium.polycart.carts.index', ['type' => $type, 'unexpired' => 1]) }}">
                <x-atrium::badge variant="primary">{{ $type }}</x-atrium::badge>
                <span class="text-sm font-semibold tabular-nums">{{ $count }}</span>
            </a>
        @endforeach
    </div>
</x-atrium::card>

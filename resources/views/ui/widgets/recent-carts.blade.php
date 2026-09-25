<x-atrium::card :title="__('polycart::polycart.widget_recent_carts')">
    @if ($carts->isEmpty())
        <x-atrium::empty-state :title="__('polycart::polycart.no_carts')" />
    @else
        <ul class="flex flex-col gap-2">
            @foreach ($carts as $cart)
                <li class="flex items-center justify-between gap-2 text-sm">
                    <span class="flex items-center gap-2">
                        <x-atrium::badge variant="primary">{{ $cart->type }}</x-atrium::badge>
                        <a class="underline-offset-2 hover:underline" href="{{ route('atrium.polycart.carts.show', $cart) }}">{{ $cart->label ?? $cart->id }}</a>
                    </span>
                    <span class="opacity-60">{{ trans_choice('polycart::polycart.line_count', $cart->lines_count) }} · {{ $cart->updated_at?->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-atrium::card>

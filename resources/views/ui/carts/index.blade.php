@use(JayI\Polycart\Atrium\Format)

<x-atrium::layout :title="__('polycart::polycart.carts')">
    <x-atrium::page-header :title="__('polycart::polycart.carts')" />

    <div class="mt-5 flex flex-col gap-4">
        @include('polycart::ui.partials.status')

        {{-- Type options come from the registry, so the filter can never offer a
             type the API would not recognise. --}}
        <x-atrium::card>
            <form method="GET" action="{{ route('atrium.polycart.carts.index') }}" class="flex flex-wrap items-end gap-3">
                <x-atrium::form.select
                    name="type"
                    :label="__('polycart::polycart.type')"
                    :placeholder="__('polycart::polycart.all_types')"
                    :options="collect($types)->mapWithKeys(fn ($type) => [$type => $type])"
                    :selected="$filters['type'] ?? null"
                    wrapper="w-48" />

                <x-atrium::form.input name="status" :label="__('polycart::polycart.status')" :value="$filters['status'] ?? null" wrapper="w-40" />
                <x-atrium::form.input name="source" :label="__('polycart::polycart.source')" :value="$filters['source'] ?? null" wrapper="w-32" />
                <x-atrium::form.input name="search" :label="__('polycart::polycart.search')" :value="$filters['search'] ?? null" wrapper="w-56" />

                <x-atrium::button type="submit" data-testid="filter-carts">{{ __('polycart::polycart.filter') }}</x-atrium::button>
                <x-atrium::button variant="ghost" :href="route('atrium.polycart.carts.index')">{{ __('polycart::polycart.clear_filters') }}</x-atrium::button>
            </form>
        </x-atrium::card>

        @if ($carts->isEmpty())
            <x-atrium::empty-state :title="__('polycart::polycart.no_carts')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.cart') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.type') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.status') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.owner') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.source') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading numeric>{{ __('polycart::polycart.lines') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.updated') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('polycart::polycart.expires') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($carts as $cart)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.polycart.carts.show', $cart) }}">{{ $cart->label ?? $cart->id }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell><x-atrium::badge variant="primary">{{ $cart->type }}</x-atrium::badge></x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @if ($cart->status)
                                <x-atrium::badge :variant="Format::variant($cart)">{{ $cart->status }}</x-atrium::badge>
                            @else
                                <span class="opacity-60">—</span>
                            @endif
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ Format::owner($cart) }}</x-atrium::table.cell>
                        <x-atrium::table.cell><x-atrium::badge>{{ $cart->source ?? '—' }}</x-atrium::badge></x-atrium::table.cell>
                        <x-atrium::table.cell numeric>{{ $cart->lines_count }}</x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $cart->updated_at?->diffForHumans() }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @if ($cart->expires_at)
                                <span @class(['text-warning' => $cart->isExpired()])>{{ $cart->expires_at->diffForHumans() }}</span>
                            @else
                                <span class="opacity-60">{{ __('polycart::polycart.never') }}</span>
                            @endif
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <x-atrium::pagination :paginator="$carts" />
        @endif
    </div>
</x-atrium::layout>

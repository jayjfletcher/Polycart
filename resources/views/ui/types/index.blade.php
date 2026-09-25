<x-atrium::layout :title="__('polycart::polycart.types')">
    <x-atrium::page-header :title="__('polycart::polycart.types')" :description="__('polycart::polycart.types_description')" />

    <div class="mt-5">
        <x-atrium::table striped>
            <x-slot:head>
                <x-atrium::table.row>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.type') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.statuses') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.converts_to') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.parents') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.rules') }}</x-atrium::table.cell>
                    <x-atrium::table.cell heading>{{ __('polycart::polycart.lifetime') }}</x-atrium::table.cell>
                </x-atrium::table.row>
            </x-slot:head>

            @foreach ($types as $type)
                <x-atrium::table.row>
                    <x-atrium::table.cell>
                        <a class="font-medium underline-offset-2 hover:underline"
                           href="{{ route('atrium.polycart.carts.index', ['type' => $type->key()]) }}">{{ $type->key() }}</a>
                        <div class="font-mono text-xs opacity-60">{{ $type::class }}</div>
                    </x-atrium::table.cell>
                    <x-atrium::table.cell>
                        @if ($type->statuses())
                            @foreach ($type->statuses()::cases() as $status)
                                <x-atrium::badge>{{ $status->value }}</x-atrium::badge>
                            @endforeach
                        @else
                            <span class="opacity-60">—</span>
                        @endif
                    </x-atrium::table.cell>
                    <x-atrium::table.cell>{{ implode(', ', $type->convertsTo()) ?: '—' }}</x-atrium::table.cell>
                    <x-atrium::table.cell>
                        @if ($type->parents() === null)
                            {{ __('polycart::polycart.any') }}
                        @elseif ($type->parents() === [])
                            {{ __('polycart::polycart.root_only') }}
                        @else
                            {{ implode(', ', $type->parents()) }}
                        @endif
                    </x-atrium::table.cell>
                    <x-atrium::table.cell>
                        @if ($type->requiresPrice())
                            <x-atrium::badge variant="warning">{{ __('polycart::polycart.requires_price') }}</x-atrium::badge>
                        @endif
                        @if ($type->requiresParent())
                            <x-atrium::badge variant="info">{{ __('polycart::polycart.requires_parent') }}</x-atrium::badge>
                        @endif
                        @unless ($type->mergesLines())
                            <x-atrium::badge>{{ __('polycart::polycart.separate_lines') }}</x-atrium::badge>
                        @endunless
                    </x-atrium::table.cell>
                    <x-atrium::table.cell>{{ $type->lifetime()?->forHumans() ?? __('polycart::polycart.never') }}</x-atrium::table.cell>
                </x-atrium::table.row>
            @endforeach
        </x-atrium::table>
    </div>
</x-atrium::layout>

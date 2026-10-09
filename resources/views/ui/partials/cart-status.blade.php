{{-- A cart's status as Atrium's status dot, coloured by RefactorCircus\Polycart\Atrium\Badges (pending is info). --}}
<x-atrium::status-dot
    :variant="\RefactorCircus\Polycart\Atrium\Badges::forCart($cart)"
    :label="$cart->isExpired() ? __('polycart::polycart.status_expired', ['status' => $cart->status]) : $cart->status"
    data-status="{{ $cart->status }}" />

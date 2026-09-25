<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Polycart\Actions\ClearCartAction;
use JayI\Polycart\Actions\ConvertCartAction;
use JayI\Polycart\Actions\DeleteCartAction;
use JayI\Polycart\Actions\ListActivityAction;
use JayI\Polycart\Actions\ListCartsAction;
use JayI\Polycart\Actions\RemoveLineAction;
use JayI\Polycart\Actions\SetVisibilityAction;
use JayI\Polycart\Actions\ShareCartAction;
use JayI\Polycart\Actions\ShowCartAction;
use JayI\Polycart\Actions\TransitionCartAction;
use JayI\Polycart\Actions\UnshareCartAction;
use JayI\Polycart\Actions\UpdateCartAction;
use JayI\Polycart\Actions\UpdateLineAction;
use JayI\Polycart\Enums\Visibility;
use JayI\Polycart\Exceptions\PolycartException;
use JayI\Polycart\Models\Cart;
use JayI\Polycart\Models\CartLine;
use JayI\Polycart\Models\CartMember;
use JayI\Polycart\Support\Morphs;
use JayI\Polycart\Types\CartTypeRegistry;

/**
 * The dashboard pages. Every change goes through the same Action the JSON
 * API and MCP tools call, so a refusal here is the refusal they would give.
 */
final class CartUiController
{
    public function index(Request $request): View
    {
        // Filters are validated by the Action's own rules, so the page and the
        // JSON API accept exactly the same query.
        $filters = $request->validate(ListCartsAction::rules());

        /** @var view-string $view */
        $view = 'polycart::ui.carts.index';

        return view($view, [
            'carts' => app(ListCartsAction::class)->execute($filters),
            'filters' => $filters,
            'types' => array_keys(app(CartTypeRegistry::class)->all()),
        ]);
    }

    public function show(Cart $cart): View
    {
        $cart = app(ShowCartAction::class)->execute($cart);
        $type = app(CartTypeRegistry::class)->has($cart->type) ? $cart->cartType() : null;

        /** @var view-string $view */
        $view = 'polycart::ui.carts.show';

        return view($view, [
            'cart' => $cart->load(['parent', 'children', 'paths']),
            'activity' => app(ListActivityAction::class)->execute($cart, ['per_page' => 25]),
            'type' => $type,
            'nextStatuses' => $type?->nextStatuses($cart->currentStatus()) ?? [],
            'conversions' => $type?->convertsTo() ?? [],
            'roles' => array_keys($type?->roles() ?? []),
            'memberTypes' => [...array_keys(config()->array('polycart.owners')), ...array_keys(config()->array('polycart.scopes'))],
            'visibilities' => Visibility::cases(),
        ]);
    }

    public function update(Request $request, Cart $cart): RedirectResponse
    {
        $data = $request->validate(['label' => ['nullable', 'string', 'max:191'], 'meta' => ['nullable', 'json']]);

        // Meta arrives from the form as a JSON string.
        $data['meta'] = is_string($data['meta'] ?? null) ? json_decode($data['meta'], true) : null;

        return $this->attempt($cart, fn (): mixed => app(UpdateCartAction::class)->execute($cart, $data), 'cart_updated');
    }

    public function destroy(Cart $cart): RedirectResponse
    {
        app(DeleteCartAction::class)->execute($cart);

        return redirect()
            ->route('atrium.polycart.carts.index')
            ->with('status', __('polycart::polycart.cart_deleted'));
    }

    public function clear(Cart $cart): RedirectResponse
    {
        return $this->attempt($cart, fn (): mixed => app(ClearCartAction::class)->execute($cart), 'cart_cleared');
    }

    public function transition(Request $request, Cart $cart): RedirectResponse
    {
        /** @var array{status: string} $data */
        $data = $request->validate(TransitionCartAction::rules());

        return $this->attempt($cart, fn (): mixed => app(TransitionCartAction::class)->execute($cart, $data['status']), 'status_changed');
    }

    public function convert(Request $request, Cart $cart): RedirectResponse
    {
        /** @var array{to: string} $data */
        $data = $request->validate(ConvertCartAction::rules());

        try {
            $converted = app(ConvertCartAction::class)->execute($cart, $data['to'], $request->boolean('copy', true));
        } catch (PolycartException $e) {
            return back()->withErrors(['polycart' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.polycart.carts.show', $converted)
            ->with('status', __('polycart::polycart.cart_converted'));
    }

    public function updateLine(Request $request, Cart $cart, CartLine $line): RedirectResponse
    {
        /** @var array{quantity: int|string, unit_price?: int|string|null} $data */
        $data = $request->validate(UpdateLineAction::rules());

        return $this->attempt($cart, fn (): mixed => app(UpdateLineAction::class)->execute(
            $line,
            (int) $data['quantity'],
            isset($data['unit_price']) ? (int) $data['unit_price'] : null,
        ), 'line_updated');
    }

    public function removeLine(Cart $cart, CartLine $line): RedirectResponse
    {
        return $this->attempt($cart, function () use ($line): void {
            app(RemoveLineAction::class)->execute($line);
        }, 'line_removed');
    }

    public function visibility(Request $request, Cart $cart): RedirectResponse
    {
        /** @var array{visibility: string} $data */
        $data = $request->validate(SetVisibilityAction::rules());

        return $this->attempt($cart, fn (): mixed => app(SetVisibilityAction::class)->execute($cart, $data['visibility']), 'visibility_changed');
    }

    public function share(Request $request, Cart $cart): RedirectResponse
    {
        /** @var array{member_type: string, member_id: string, role: string} $data */
        $data = $request->validate(ShareCartAction::rules());

        return $this->attempt($cart, fn (): mixed => app(ShareCartAction::class)->execute(
            $cart,
            app(Morphs::class)->memberFrom($data),
            $data['role'],
        ), 'cart_shared');
    }

    public function unshare(Cart $cart, CartMember $member): RedirectResponse
    {
        return $this->attempt($cart, function () use ($cart, $member): void {
            app(UnshareCartAction::class)->execute($cart, $member);
        }, 'cart_unshared');
    }

    /**
     * Run a change and return to the cart, showing the type's refusal if any.
     *
     * @param  Closure(): mixed  $change
     */
    private function attempt(Cart $cart, Closure $change, string $message): RedirectResponse
    {
        try {
            $change();
        } catch (ModelNotFoundException) {
            return back()->withErrors(['polycart' => __('polycart::polycart.member_not_found')]);
        } catch (PolycartException $e) {
            return back()->withErrors(['polycart' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.polycart.carts.show', $cart)
            ->with('status', __('polycart::polycart.'.$message));
    }
}

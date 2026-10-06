<?php

declare(strict_types=1);

namespace JayI\Polycart\Atrium\Http\Controllers;

use BackedEnum;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Foundation\Audit\History;
use JayI\Foundation\Packages\PackageRegistry;
use JayI\Polycart\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Polycart\Atrium\ScreenAccess;
use JayI\Polycart\Domains\Cart\Actions\ClearCartAction;
use JayI\Polycart\Domains\Cart\Actions\ConvertCartAction;
use JayI\Polycart\Domains\Cart\Actions\DeleteCartAction;
use JayI\Polycart\Domains\Cart\Actions\ListCartsAction;
use JayI\Polycart\Domains\Cart\Actions\ShowCartAction;
use JayI\Polycart\Domains\Cart\Actions\TransitionCartAction;
use JayI\Polycart\Domains\Cart\Actions\UpdateCartAction;
use JayI\Polycart\Domains\Cart\Models\CartModel;
use JayI\Polycart\Domains\CartLine\Actions\RemoveLineAction;
use JayI\Polycart\Domains\CartLine\Actions\UpdateLineAction;
use JayI\Polycart\Domains\CartLine\Models\CartLineModel;
use JayI\Polycart\Domains\CartType\Services\CartTypeRegistry;
use JayI\Polycart\Domains\Sharing\Actions\SetVisibilityAction;
use JayI\Polycart\Domains\Sharing\Actions\ShareCartAction;
use JayI\Polycart\Domains\Sharing\Actions\UnshareCartAction;
use JayI\Polycart\Domains\Sharing\Enums\Visibility;
use JayI\Polycart\Domains\Sharing\Models\CartMemberModel;
use JayI\Polycart\Exceptions\PolycartException;
use JayI\Polycart\Support\Morphs;

/**
 * The dashboard pages. Every change goes through the same Action the JSON
 * API and MCP tools call, after the same policy check their requests make,
 * so a refusal here is the refusal they would give. The views hide each
 * control with that check (`@polycartCan`), so they cannot drift apart.
 */
final class CartUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        // Filters are validated by the Action's own rules, so the page and the
        // JSON API accept exactly the same query.
        $this->authorizeScreen('viewAny', CartModel::class);

        $filters = $request->validate(ListCartsAction::rules());

        /** @var view-string $view */
        $view = 'polycart::ui.carts.index';

        return view($view, [
            'carts' => app(ListCartsAction::class)->execute($filters, ScreenAccess::actor()),
            'filters' => $filters,
            'types' => array_keys(app(CartTypeRegistry::class)->all()),
            // The whole package's history, asked as its history endpoint asks it.
            'showHistory' => app(History::class)->allows(app(PackageRegistry::class)->get('polycart'), $request->user(), []),
        ]);
    }

    public function show(CartModel $cart): View
    {
        $this->authorizeScreen('view', $cart);

        $cart = app(ShowCartAction::class)->execute($cart);
        $type = app(CartTypeRegistry::class)->has($cart->type) ? $cart->cartType() : null;

        /** @var view-string $view */
        $view = 'polycart::ui.carts.show';

        return view($view, [
            'cart' => $cart->load(['parent', 'children', 'paths']),
            'type' => $type,
            // Only the moves the type allows from here, and the user may make.
            'nextStatuses' => array_values(array_filter(
                $type?->nextStatuses($cart->currentStatus()) ?? [],
                fn (BackedEnum $status): bool => ScreenAccess::allows('transition', $cart, [(string) $status->value]),
            )),
            'conversions' => array_values(array_filter(
                $type?->convertsTo() ?? [],
                fn (string $to): bool => ScreenAccess::allows('convert', $cart, [$to]),
            )),
            'roles' => array_keys($type?->roles() ?? []),
            'memberTypes' => [...array_keys(config()->array('polycart.owners')), ...array_keys(config()->array('polycart.scopes'))],
            'visibilities' => Visibility::cases(),
        ]);
    }

    public function update(Request $request, CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('update', $cart);

        $data = $request->validate(['label' => ['nullable', 'string', 'max:191'], 'meta' => ['nullable', 'json']]);

        // Meta arrives from the form as a JSON string.
        $data['meta'] = is_string($data['meta'] ?? null) ? json_decode($data['meta'], true) : null;

        return $this->attempt($cart, fn (): mixed => app(UpdateCartAction::class)->execute($cart, $data), 'cart_updated');
    }

    public function destroy(CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('delete', $cart);

        app(DeleteCartAction::class)->execute($cart);

        return redirect()
            ->route('atrium.polycart.carts.index')
            ->with('status', __('polycart::polycart.cart_deleted'));
    }

    public function clear(CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('update', $cart);

        return $this->attempt($cart, fn (): mixed => app(ClearCartAction::class)->execute($cart), 'cart_cleared');
    }

    public function transition(Request $request, CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('transition', $cart, [$request->string('status')->toString()]);

        /** @var array{status: string} $data */
        $data = $request->validate(TransitionCartAction::rules());

        return $this->attempt($cart, fn (): mixed => app(TransitionCartAction::class)->execute($cart, $data['status']), 'status_changed');
    }

    public function convert(Request $request, CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('convert', $cart, [$request->string('to')->toString()]);

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

    public function updateLine(Request $request, CartModel $cart, CartLineModel $line): RedirectResponse
    {
        $this->authorizeScreen('update', $line);

        /** @var array{quantity: int|string, unit_price?: int|string|null} $data */
        $data = $request->validate(UpdateLineAction::rules());

        return $this->attempt($cart, fn (): mixed => app(UpdateLineAction::class)->execute(
            $line,
            (int) $data['quantity'],
            isset($data['unit_price']) ? (int) $data['unit_price'] : null,
        ), 'line_updated');
    }

    public function removeLine(CartModel $cart, CartLineModel $line): RedirectResponse
    {
        $this->authorizeScreen('delete', $line);

        return $this->attempt($cart, function () use ($line): void {
            app(RemoveLineAction::class)->execute($line);
        }, 'line_removed');
    }

    public function visibility(Request $request, CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('share', $cart);

        /** @var array{visibility: string} $data */
        $data = $request->validate(SetVisibilityAction::rules());

        return $this->attempt($cart, fn (): mixed => app(SetVisibilityAction::class)->execute($cart, $data['visibility']), 'visibility_changed');
    }

    public function share(Request $request, CartModel $cart): RedirectResponse
    {
        $this->authorizeScreen('create', CartMemberModel::class, [$cart]);

        /** @var array{member_type: string, member_id: string, role: string} $data */
        $data = $request->validate(ShareCartAction::rules());

        return $this->attempt($cart, fn (): mixed => app(ShareCartAction::class)->execute(
            $cart,
            app(Morphs::class)->memberFrom($data),
            $data['role'],
        ), 'cart_shared');
    }

    public function unshare(CartModel $cart, CartMemberModel $member): RedirectResponse
    {
        $this->authorizeScreen('delete', $member);

        return $this->attempt($cart, function () use ($cart, $member): void {
            app(UnshareCartAction::class)->execute($cart, $member);
        }, 'cart_unshared');
    }

    /**
     * Run a change and return to the cart, showing the type's refusal if any.
     *
     * @param  Closure(): mixed  $change
     */
    private function attempt(CartModel $cart, Closure $change, string $message): RedirectResponse
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

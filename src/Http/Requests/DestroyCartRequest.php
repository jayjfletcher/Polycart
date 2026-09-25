<?php

declare(strict_types=1);

namespace JayI\Polycart\Http\Requests;

use Illuminate\Http\Response;
use JayI\Polycart\Actions\DeleteCartAction;

final class DestroyCartRequest extends CartRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->cart());
    }

    public function rules(): array
    {
        return DeleteCartAction::rules();
    }

    public function persist(): Response
    {
        app(DeleteCartAction::class)->execute($this->cart());

        return response()->noContent();
    }
}

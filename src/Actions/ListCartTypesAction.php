<?php

declare(strict_types=1);

namespace JayI\Polycart\Actions;

use JayI\Polycart\Events\Action\CartTypesListedActionEvent;
use JayI\Polycart\Events\Action\CartTypesListingActionEvent;
use JayI\Polycart\Types\CartType;
use JayI\Polycart\Types\CartTypeRegistry;

final class ListCartTypesAction
{
    public function __construct(private readonly CartTypeRegistry $types) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return array<int, CartType>
     */
    public function execute(): array
    {
        CartTypesListingActionEvent::dispatch();

        $result = $this->perform();

        CartTypesListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @return array<int, CartType>
     */
    private function perform(): array
    {
        return array_map(
            fn (string $key): CartType => $this->types->get($key),
            array_keys($this->types->all()),
        );
    }
}

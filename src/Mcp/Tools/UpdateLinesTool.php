<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Polycart\Actions\UpdateLinesAction;
use JayI\Polycart\Mcp\Requests\UpdateLinesMcpRequest;
use JayI\Polycart\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('update-lines')]
#[Description('Change the quantity, and optionally the unit price, of one or more lines in a cart, all or nothing. A quantity of 0 removes the line. If any change is refused, none are kept and the error names the entry by its position and gives a reason code. Returns the cart with its lines and totals.')]
final class UpdateLinesTool extends Tool
{
    public function handle(UpdateLinesMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cart' => $schema->string()->description('The cart id.')->required(),
            'lines' => $schema->array()
                ->description(sprintf('The changes, 1 to %d.', UpdateLinesAction::MAX_LINES))
                ->items($schema->object([
                    'id' => $schema->string()->description('The line id.')->required(),
                    'quantity' => $schema->integer()->description('The new quantity. 0 removes the line.')->min(0)->required(),
                    'unit_price' => $schema->integer()->description('A new unit price in minor units.')->min(0),
                ]))
                ->min(1)
                ->max(UpdateLinesAction::MAX_LINES)
                ->required(),
        ];
    }
}

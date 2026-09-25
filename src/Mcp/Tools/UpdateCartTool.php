<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Polycart\Mcp\Requests\UpdateCartMcpRequest;
use JayI\Polycart\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('update-cart')]
#[Description('Change a cart\'s label or meta. Meta is replaced as a whole. Use transition-cart and convert-cart to change status or type.')]
final class UpdateCartTool extends Tool
{
    public function handle(UpdateCartMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cart' => $schema->string()->description('The cart id.')->required(),
            'label' => $schema->string()->description('A name for the cart.'),
            'meta' => $schema->object()->description('Free-form header data, replacing what is there.'),
        ];
    }
}

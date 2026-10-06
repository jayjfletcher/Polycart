<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Polycart\Domains\Cart\Mcp\Requests\ShowCartMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('show-cart')]
#[Description('Show a cart with its lines, quantity, and subtotal. Prices are integers in minor units (cents).')]
final class ShowCartTool extends Tool
{
    public function handle(ShowCartMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cart' => $schema->string()->description('The cart id.')->required(),
        ];
    }
}

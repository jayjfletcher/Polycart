<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Polycart\Mcp\Requests\ListCartTypesMcpRequest;
use JayI\Polycart\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('list-cart-types')]
#[Description('List every cart type and what it allows: its statuses and allowed moves, the types it converts into, its allowed parents, and whether its lines need a price. Read this before transitioning or converting a cart.')]
final class ListCartTypesTool extends Tool
{
    public function handle(ListCartTypesMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}

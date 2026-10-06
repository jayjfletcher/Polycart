<?php

declare(strict_types=1);

namespace JayI\Polycart\Domains\Cart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Polycart\Domains\Cart\Mcp\Requests\DeleteCartMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('delete-cart')]
#[Description('Delete a cart. It is soft-deleted, and removed for good when it is pruned.')]
final class DeleteCartTool extends Tool
{
    public function handle(DeleteCartMcpRequest $request): Response|ResponseFactory
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

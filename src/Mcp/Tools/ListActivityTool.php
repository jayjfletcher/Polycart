<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Polycart\Mcp\Requests\ListActivityMcpRequest;
use JayI\Polycart\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('list-cart-activity')]
#[Description('List every change a cart has been through, newest first: what happened, the source it came from (api, mcp, atrium, code, or one the application records), and who did it. Includes the history of carts it was copied or merged from. Also returns every source that has touched the cart.')]
final class ListActivityTool extends Tool
{
    public function handle(ListActivityMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cart' => $schema->string()->description('The cart id.')->required(),
            'source' => $schema->string()->description('Only entries from this source.'),
            'action' => $schema->string()->description('Only entries of this action, such as line_added or converted.'),
            'cursor' => $schema->string()->description('Cursor from a previous page.'),
            'per_page' => $schema->integer()->description('Results per page, 1-200.')->min(1)->max(200),
        ];
    }
}

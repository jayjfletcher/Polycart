<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Polycart\Mcp\Requests\ListCartsMcpRequest;
use JayI\Polycart\Mcp\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;

#[Name('list-carts')]
#[Description('List carts, newest first. Filter by type, status, owner, guest session key, parent, root, or a label search. Cursor paginated.')]
final class ListCartsTool extends Tool
{
    public function handle(ListCartsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Only carts of this type key.'),
            'status' => $schema->string()->description('Only carts in this status.'),
            'source' => $schema->string()->description('Only carts created from this source: code, api, mcp, atrium, or one the application records.'),
            'touched_by' => $schema->string()->description('Only carts this source has touched at any point.'),
            'owner_type' => $schema->string()->description('Owner alias from polycart.owners, or its morph class. Requires owner_id.'),
            'owner_id' => $schema->string()->description('Key of the owning model. Requires owner_type.'),
            'session_key' => $schema->string()->description('Only the guest carts for this session key.'),
            'parent' => $schema->string()->description('Only carts nested directly under this cart id.'),
            'root' => $schema->string()->description('Only carts anywhere in the tree under this root cart id.'),
            'search' => $schema->string()->description('Match against the label, or the start of the id.'),
            'unexpired' => $schema->boolean()->description('Leave out expired carts.'),
            'cursor' => $schema->string()->description('Cursor from a previous page.'),
            'per_page' => $schema->integer()->description('Results per page, 1-200.')->min(1)->max(200),
            'scope_type' => $schema->string()->description('Only carts anywhere under this scope alias. Requires scope_id.'),
            'scope_id' => $schema->string()->description('Key of the scope model.'),
        ];
    }
}

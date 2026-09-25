<?php

declare(strict_types=1);

arch()->preset()->php();

arch()->preset()->security();

arch('it will not use dd(), ddd(), env(), or exit()')
    ->expect(['dd', 'ddd', 'env', 'exit'])
    ->each->not->toBeUsed();

arch('the package source declares strict types')
    ->expect('JayI\Polycart')
    ->toUseStrictTypes();

arch('actions are final and declare their own validation rules')
    ->expect('JayI\Polycart\Actions')
    ->classes()
    ->toBeFinal()
    ->toHaveMethod('rules')
    ->toHaveMethod('execute');

arch('every use case is reachable from the JSON API')
    ->expect(fn (): array => parityGaps('Http/Requests'))
    ->toBeEmpty();

arch('every use case is reachable from MCP')
    ->expect(fn (): array => parityGaps('Mcp/Requests'))
    ->toBeEmpty();

arch('every MCP request has a tool on the server')
    ->expect(function (): array {
        $server = (string) file_get_contents(dirname(__DIR__).'/src/Mcp/PolycartServer.php');

        return array_values(array_filter(
            array_map(fn (string $path): string => basename($path, 'McpRequest.php'), (array) glob(dirname(__DIR__).'/src/Mcp/Requests/*McpRequest.php')),
            fn (string $name): bool => ! str_contains($server, $name.'Tool::class'),
        ));
    })
    ->toBeEmpty();

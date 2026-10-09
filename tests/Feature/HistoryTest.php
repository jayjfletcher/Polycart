<?php

declare(strict_types=1);

use RefactorCircus\Polycart\Mcp\PolycartServer;
use RefactorCircus\Polycart\Mcp\Tools\ListPolycartHistoryTool;

it('serves the history route inside the JSON API', function (): void {
    expect(route('polycart.history.index', absolute: false))->toBe('/polycart/history');
});

it('answers 404 from the history route while no audit log is installed', function (): void {
    $this->getJson('/polycart/history')
        ->assertNotFound()
        ->assertJson(['message' => 'No audit log is installed. Install refactor-circus/keen to record history.']);
});

it('lists the history tool on the MCP server', function (): void {
    expect(PolycartServer::TOOLS)->toContain(ListPolycartHistoryTool::class)
        ->and(app(ListPolycartHistoryTool::class)->name())->toBe('list-polycart-history-tool');
});

it('answers the history tool with an error while no audit log is installed', function (): void {
    PolycartServer::tool(ListPolycartHistoryTool::class)
        ->assertHasErrors(['No audit log is installed, so there is no history to show. Install refactor-circus/keen to record it.']);
});

<?php

declare(strict_types=1);

namespace JayI\Polycart\Mcp;

use JayI\Polycart\Cortex\CortexIntegration;
use JayI\Polycart\Mcp\Tools\ActiveCartTool;
use JayI\Polycart\Mcp\Tools\AddLinesTool;
use JayI\Polycart\Mcp\Tools\ClearCartTool;
use JayI\Polycart\Mcp\Tools\ConvertCartTool;
use JayI\Polycart\Mcp\Tools\CreateCartTool;
use JayI\Polycart\Mcp\Tools\DeleteCartTool;
use JayI\Polycart\Mcp\Tools\ListActivityTool;
use JayI\Polycart\Mcp\Tools\ListCartsTool;
use JayI\Polycart\Mcp\Tools\ListCartTypesTool;
use JayI\Polycart\Mcp\Tools\ListMembersTool;
use JayI\Polycart\Mcp\Tools\MergeCartsTool;
use JayI\Polycart\Mcp\Tools\RemoveLinesTool;
use JayI\Polycart\Mcp\Tools\SetVisibilityTool;
use JayI\Polycart\Mcp\Tools\ShareCartTool;
use JayI\Polycart\Mcp\Tools\ShowCartTool;
use JayI\Polycart\Mcp\Tools\TransitionCartTool;
use JayI\Polycart\Mcp\Tools\UnshareCartTool;
use JayI\Polycart\Mcp\Tools\UpdateCartTool;
use JayI\Polycart\Mcp\Tools\UpdateLinesTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\ServerContext;
use Laravel\Mcp\Server\Tool;

#[Name('Polycart')]
#[Version('1.0.0')]
#[Instructions(
    'Manage carts of every type: shopping carts, saved carts, quotes, orders, and nested project carts. '.
    'Each cart has a type key, and the type decides its statuses, which moves between them are allowed, which '.
    'types it converts into, where it may nest, and whether its lines need a price. Call list-cart-types first '.
    'and offer only the moves a type allows. Prices and totals are integers in minor units (cents). An owner is '.
    'named by an alias from polycart.owners; a guest is named by a session key. A refusal explains which rule '.
    'was broken, so read it before retrying. Carts live in a scope of the application\'s tree, such as a team '.
    'inside an organization, and are locked to it. They can be shared with people or whole teams inside the same '.
    'boundary; the role a member holds decides what they may do, and roles come from the cart\'s type.',
)]
final class PolycartServer extends Server
{
    /**
     * Every tool the server offers. Also registered with Cortex when it is
     * installed.
     *
     * @var array<int, class-string<Tool>>
     */
    public const array TOOLS = [
        // Types
        ListCartTypesTool::class,

        // Carts
        ListCartsTool::class,
        ShowCartTool::class,
        CreateCartTool::class,
        ActiveCartTool::class,
        UpdateCartTool::class,
        DeleteCartTool::class,
        ClearCartTool::class,

        // Lifecycle
        TransitionCartTool::class,
        ConvertCartTool::class,
        MergeCartsTool::class,

        // Lines
        AddLinesTool::class,
        UpdateLinesTool::class,
        RemoveLinesTool::class,

        // Sharing
        ListMembersTool::class,
        ShareCartTool::class,
        UnshareCartTool::class,
        SetVisibilityTool::class,

        // History
        ListActivityTool::class,
    ];

    /**
     * @var array<int, class-string<Tool>>
     */
    protected array $tools = self::TOOLS;

    /**
     * Serve Cortex's published instructions override, when Cortex is
     * installed and one is published, in place of the ones declared above.
     */
    public function createContext(): ServerContext
    {
        $context = parent::createContext();
        $override = app(CortexIntegration::class)->instructions();

        if ($override !== null) {
            $context->instructions = $override;
        }

        return $context;
    }
}

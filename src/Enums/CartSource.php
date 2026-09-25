<?php

declare(strict_types=1);

namespace JayI\Polycart\Enums;

/**
 * Where a cart was created from, for the surfaces the package ships.
 *
 * A source is stored as a plain string, so an application can record any
 * other — `web`, `import`, `pos` — without touching this enum.
 */
enum CartSource: string
{
    /** Application code calling the package directly. */
    case Code = 'code';

    /** The JSON API. */
    case Api = 'api';

    /** The MCP server. */
    case Mcp = 'mcp';

    /** The Atrium dashboard, such as a conversion made there. */
    case Atrium = 'atrium';

    /** A Cortex agent calling one of the MCP tools. */
    case Cortex = 'cortex';
}

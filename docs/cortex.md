# Cortex

When [`jayi/cortex`](https://github.com/jayjfletcher/cortex) is installed, Polycart connects its MCP server to it:

- **Agents can manage carts.** Every Polycart MCP tool joins Cortex's tool registry under its own name (`list-carts`, `add-lines`, `convert-cart`, ...). Give an agent those tools, and it can list, build, share and convert carts.
- **Instructions and descriptions can change without a deploy.** The Polycart server is registered with Cortex as `polycart`, so its instructions get Cortex's versioned, publishable overrides, and so does each tool's description. Published overrides are served both to MCP clients and to agents.
- **Agent changes are traceable.** Anything an agent changes through a cart tool records `cortex` as its [source](sources-and-activity.md).

Cortex is optional. Without it, none of this runs, and nothing Cortex-related is loaded.

## Setup

```bash
composer require jayi/cortex
```

That is all: Polycart notices Cortex's service provider and registers itself. Configure it in `config/polycart.php`:

```php
'cortex' => [
    'enabled' => true,          // false leaves Cortex alone
    'server' => 'polycart',     // the server's name in Cortex
    'tools' => null,            // null for every tool, or a list of names
],
```

Offer agents a read-only subset:

```php
'tools' => ['list-cart-types', 'list-carts', 'show-cart', 'list-members', 'list-cart-activity'],
```

Registration is lazy. It happens the first time Cortex's registries are used, so a request that never touches Cortex does no extra work. A name that is already registered with Cortex, for example by your own config, is left alone.

## Giving an agent the cart tools

In Cortex, give the agent the tools by name, from the dashboard, the API or MCP. The agent then calls them like any other tool:

> "Start a quote for the Acme lobby with 12 of product 42 and share it with the estimating team."

The agent would call `create-cart`, then `add-lines`, then `share-cart`.

What an agent's calls go through:

- **The same checks as MCP:** each call passes the same validation, [authorization](roles-and-permissions.md) and [add pipeline](adding-lines.md) as a call from an MCP client.
- **Its user:** with `polycart.authorization` on, the agent acts as the user whose request is running it. It can only see and change what that user can.
- **Readable refusals:** a refused call, such as out of stock, missing price, or a status move the type doesn't allow, comes back to the agent with a message and a reason code it can act on.

## Overriding instructions and descriptions

Manage overrides in Cortex, the same way as for its own server:

- **Server instructions:** the `polycart` server under *Servers*, or `/cortex/servers/polycart/instructions`
- **Tool descriptions:** each tool under *Tools*, or `/cortex/tools/{tool}/description`

Once published, an override replaces the text written in code:

- **MCP clients** connected to Polycart's server see the new instructions and descriptions.
- **Agents** see the new tool descriptions.
- **Rolling back:** publish an older version, or delete the override to fall back to the code.

This is useful for fitting the tools to your business without forking the package, e.g. "Quotes are only for customers on net-30 terms".

## Sources

| Called by | Source recorded |
| --- | --- |
| An MCP client, directly | `mcp` |
| A Cortex agent, through the same tool | `cortex` |

Polycart listens to laravel/ai's `InvokingTool`, `ToolInvoked` and `ToolFailed` events. It sets the `cortex` source for exactly as long as each cart tool runs.

## Notes

- **Requires a Cortex version that passes agent arguments to tool request classes.** Polycart's tools each take their own request class. Earlier Cortex versions called such tools with an empty request when an agent used them; Cortex now copies the arguments across, so the same tools work for MCP clients and agents.
- **Name collisions:** tools are registered under their plain names. If another package registers the same name first, Polycart leaves it in place. Use `polycart.cortex.tools` to choose, or register your own under another name with `Cortex::tools()->register('cart-add-lines', AddLinesTool::class)`.

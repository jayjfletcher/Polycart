# Sources and activity

Polycart records where every change to a cart came from, and what that change was:

- **`source`** on a cart: where the cart was created, or last converted.
- **`sources`** on a cart: every distinct source that has touched it, in the order each first did.
- **The activity log** (`polycart_cart_activities`): one entry per change, with its action, source, acting user, context and time.

This guide covers:

- [Sources](#sources)
- [Setting the source](#setting-the-source)
- [The activity log](#the-activity-log)
- [What gets recorded](#what-gets-recorded)
- [History that travels](#history-that-travels)
- [Recording your own events](#recording-your-own-events)
- [Querying](#querying)
- [Over the JSON API and MCP](#over-the-json-api-and-mcp)
- [In the Atrium dashboard](#in-the-atrium-dashboard)
- [Storage and pruning](#storage-and-pruning)
- [What is not recorded](#what-is-not-recorded)

## Sources

A source is a short string naming the surface a change came through. The package sets five itself:

| Source | Set by |
| --- | --- |
| `api` | The JSON API |
| `mcp` | The MCP server |
| `atrium` | The Atrium dashboard |
| `cortex` | A Cortex agent calling a cart tool. See [Cortex](cortex.md). |
| `code` | Anything else. This is `polycart.default_source`, which you can change. |

These are listed in `JayI\Polycart\Enums\CartSource`, but a source is stored as a plain string, so you can use your own (`web`, `import`, `pos`, `erp`) without changing the package.

A cart has two source fields:

| Field | Holds | Changes when |
| --- | --- | --- |
| `source` | The source that created the cart, or last converted it | The cart is created, or converted (in place, or into a copy) |
| `sources` | Every source that has touched the cart, each once, oldest first | Any recorded change comes from a new source |

For example, a cart created through the API and converted into an order through MCP has `source` = `mcp` and `sources` = `['api', 'mcp']`.

## Setting the source

The JSON API, MCP server and dashboard set their own source around the work they do. The MCP server sets `mcp` only when no source is already in effect, so a Cortex agent's calls keep `cortex`. Anywhere else, set it yourself.

**Around a block of code:**

```php
use JayI\Polycart\Facades\Polycart;

Polycart::usingSource('import', function () use ($rows) {
    foreach ($rows as $row) {
        $cart->add(Product::find($row['id']), $row['qty']);
    }
});
```

**On your own routes**, with the middleware the package uses on its own routes:

```php
use JayI\Polycart\Http\Middleware\CartSource;

Route::middleware(CartSource::class.':web')->group(function () {
    Route::post('/cart/lines', AddToCart::class);
    Route::post('/checkout', Checkout::class);
});
```

**As the default** for everything with no more specific source:

```php
// config/polycart.php
'default_source' => 'web',
```

How the source is managed:

- **Restored afterwards:** the previous source comes back after each block or request, even when an exception is thrown. Blocks can be nested.
- **Per request:** the source is held per request or job, so it never leaks between them in Octane or queue workers.
- **Not settable by callers:** API and MCP callers cannot choose a source; it always names the surface they used.

## The activity log

Each entry records:

| Field | Meaning |
| --- | --- |
| `action` | What happened. See [What gets recorded](#what-gets-recorded). |
| `source` | The source in effect at the time |
| `actor_type`, `actor_id` | The signed-in user, when there is one. Empty for console commands, jobs and guest requests. |
| `context` | Details of the change, such as the line and quantity, or the from and to status |
| `created_at` | When it happened, to the microsecond, so entries keep their order |

```php
foreach ($cart->activities as $entry) {    // oldest first
    $entry->action;       // 'line_added'
    $entry->source;       // 'mcp'
    $entry->actor;        // the User model, or null
    $entry->context;      // ['line' => '01J...', 'quantity' => 2]
}
```

## What gets recorded

Every change made through the package's actions is recorded, whichever surface it came through: code, the API, MCP or the dashboard. The actions are listed in `JayI\Polycart\Enums\Activity`.

| Action | When | Context |
| --- | --- | --- |
| `created` | A cart is created | `type` |
| `updated` | The label or meta changes | `changed` (the keys that changed) |
| `deleted` | The cart is deleted | none |
| `cleared` | Every line is removed at once. Each line also gets its own `line_removed`. | none |
| `status_changed` | The status moves | `from`, `to` |
| `converted` | The cart is retyped in place, or copied into another type. Logged on the original. | `from`, `to`, and on a copy `into` |
| `converted_from` | This cart is a copy made by a conversion | `from`, `to`, `cart` (the original) |
| `merged` | Another cart's lines were merged into this one | `from` |
| `merged_into` | This cart's lines were merged into another | `into` |
| `line_added` | A new line is added | `line`, `quantity` |
| `line_updated` | A line's quantity changes, including when adding a matching item merges into it | `line`, `quantity` (the new total) |
| `line_removed` | A line is removed | `line` |
| `shared` | A member is added, or their role changes | `member_type`, `member_id`, `role` |
| `unshared` | A member is removed | `member_type`, `member_id` |
| `visibility_changed` | Visibility changes | `from`, `to` |

## History that travels

When a cart's contents move into another cart, its history moves with them. That way, the question "which sources touched this?" covers everything that ended up in the cart.

- **Conversion by copy.** The new cart receives the original's full log, followed by its own `created` and `converted_from` entries. The original keeps its log and gets a `converted` entry.
- **Conversion in place.** The cart keeps its log and gets a `converted` entry.
- **Merging.** The target receives the merged cart's log, and gets a `merged` entry. The merged cart gets `merged_into` and is then soft-deleted with its own log intact.

Copied entries keep their original times, sources and actors. After a copy or merge, `sources` is rebuilt from the combined log, so it stays in the order each source first touched what became this cart.

Example: a cart built through the API, then copied into an order through MCP.

```
order activity                         order sources
created          api   (inherited)     ['api', 'mcp']
line_added       api   (inherited)
created          mcp
converted        mcp   (inherited from the original's new entry)
converted_from   mcp
```

## Recording your own events

Add entries for things your application does to a cart. They are stamped with the current source and user, like the package's own entries:

```php
$cart->recordActivity('exported', ['reference' => $salesOrder->number]);

Polycart::usingSource('erp', fn () => $cart->recordActivity('synced'));
```

Any string can be an action, and backed enums are accepted too.

## Querying

```php
use JayI\Polycart\Models\Cart;

Cart::query()->fromSource('api')->get();                    // created (or last converted) through the API
Cart::query()->touchedBy('mcp')->get();                     // MCP touched it at any point
Cart::query()->touchedBy('mcp', CartSource::Atrium)->get(); // either one

$cart->sources;                                             // ['api', 'mcp']
$cart->activities()->where('source', 'mcp')->get();
$cart->activities()->where('action', 'status_changed')->latest('created_at')->first();
```

## Over the JSON API and MCP

| HTTP | MCP | Returns |
| --- | --- | --- |
| `GET /carts/{cart}/activity` | `list-cart-activity` | The log, newest first, cursor paginated. Filter with `source` and `action`. MCP also returns the cart's `sources`. |
| `GET /carts?touched_by=mcp` | `list-carts {touched_by}` | Carts a source has touched |
| `GET /carts?source=api` | `list-carts {source}` | Carts created, or last converted, through a source |

Cart responses include both `source` and `sources`. Reading the log needs the `view` ability on the cart.

## In the Atrium dashboard

The cart page shows the creating source and every source that has touched the cart, followed by an **Activity** timeline with the latest 25 entries. Each entry shows its action, source, actor and context. The cart list has a source column and filter.

## Storage and pruning

| Column or table | Holds |
| --- | --- |
| `polycart_carts.source` | Creating or converting source. Indexed. |
| `polycart_carts.sources` | JSON array of every source that has touched the cart |
| `polycart_cart_activities` | `cart_id`, `action`, `source` (indexed), `actor_type`, `actor_id`, `context` (JSON), `created_at` (microseconds) |

Entries are deleted along with their cart when it is pruned or force-deleted. A soft-deleted cart keeps its log.

## What is not recorded

The log records changes made through Polycart's actions: the model methods (`$cart->add()`, `$cart->transitionTo()`, ...), the facade, the API, MCP and the dashboard. A direct Eloquent write such as `$cart->update(['label' => ...])` or a raw query bypasses them and is not recorded. Use the actions, or call `$cart->recordActivity()` yourself.

Reads are not recorded.

# Release Notes

## [Unreleased](https://github.com/jayi/polycart/compare/v0.1.0...1.x)

### Added

- Polycart's Atrium navigation items have icons, and the screens follow Atrium's screen conventions: actions are icon buttons with their label as a tooltip, and cart statuses are status dots (`data-status`) coloured by `JayI\Polycart\Atrium\Badges`, which keeps `info` for pending and awaiting states.
- The `@polycartCan` Blade conditional and `JayI\Polycart\Http\Ui\ScreenAccess`, which ask the cart policies exactly as the JSON API does.
- `JayI\Polycart\Features\PolycartSupportFeature` and the `polycart.atrium.features` config: with jayi/pennantplus installed, a global Pennant switch for Polycart in Atrium. Feature classes that cannot be loaded are skipped.

### Changed

- With `polycart.authorization` on, the Atrium dashboard now applies the same per-user policies as the JSON API and MCP tools. Navigation, widgets and search need `viewAny` on `Cart`; each page action checks the ability its API request checks and answers 403 otherwise; controls the user may not use are hidden; and lists, widgets and search hold only the carts the user can access. Previously the dashboard relied on Atrium's gate alone.
- `JayI\Polycart\Atrium\Format::variant()` is replaced by `Badges::forCart()`.


## [v0.1.0](https://github.com/jayi/polycart/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.

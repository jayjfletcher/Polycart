# Polycart

This repository is a Laravel package. Keep the package focused, idiomatic, and easy for Laravel developers to install, test, and maintain.

## Package Conventions

- Use Laravel-native package APIs and the existing service provider shape before adding abstractions.
- Keep package names, namespaces, Composer metadata, publish tags, documentation, and examples aligned with `refactor-circus/polycart`.
- Add only the files and dependencies needed for the package behavior being implemented.
- Prefer explicit Laravel package code over helper abstractions unless the extension point is real.
- Keep tests focused on observable package behavior through public APIs, service provider wiring, commands, routes, published resources, and documentation promises.

## Architecture

- Code lives in domain modules under `src/Domains/{Domain}` (Cart, CartLine, CartType, Sharing, Scope), mirroring the `mono` domain-module standard. Each domain has a `{Domain}ServiceProvider` (extending `RefactorCircus\Foundation\Support\ServiceProvider`) registered by `src/Domains/DomainServiceProvider.php`, its own `routes.php`, and only the subdirectories it uses.
- Polycart stands on `refactor-circus/foundation`, the shared runtime of the Refactor Circus suite. `PolycartServiceProvider` extends `RefactorCircus\Foundation\Support\PackageServiceProvider`: it describes the package in `definition()` (`Package::make('polycart', ...)->authorization()`, so authorization defaults to on), calls `registerPackage()` right after merging config, and uses the base helpers for policies, the MCP server, Cortex, the Atrium plugin and the `GET {prefix}/history` route. Do not copy Foundation classes back into the package; if Foundation lacks something, add a thin local subclass.
- Use Foundation's classes directly: event contracts (`RefactorCircus\Foundation\Contracts\*`), `Models\Concerns\DispatchesModelEvents`, `Http\Requests\Request`, `Mcp\Tool`, `Mcp\Server`, `Auth\Authorizer` (`Authorizer::for(app(PackageRegistry::class)->get('polycart'))`) and `Cortex\CortexIntegration`. `PolycartException` extends `PackageException` with status 422, and the base policy extends Foundation's `Policy` (`allowsOn()`).
- MCP requests extend `RefactorCircus\Polycart\Mcp\Request`, which extends Foundation's and implements `handle()` to keep the `cortex` surface for agent calls and add line reason codes; each request implements `respond(array $validated)`.
- History belongs to refactor-circus/keen, the suite-wide audit log; Polycart keeps none of its own and must not grow a parallel log. A cart's `source` column is Foundation's `Surface::current()` when it is created or converted. Action events whose first model is not the right subject implement `RefactorCircus\Foundation\Audit\Contracts\Auditable` (line, sharing, merge and conversion events name the cart); carts and lines get `AuditHooks` labels from their domain providers. Screens show history with `<x-atrium::audit-trail source="polycart" ... />`.
- Models are named `{Entity}Model`; their pre-domain class names are kept as morph aliases in each domain provider. Model events derive from `{Entity}{Hook}Event` in the model's domain `Events` namespace.
- Cross-domain code stays outside the domains: `Polycart`, the facade, `Mcp\Request`, `PolycartServer` and `Mcp\Tools\ListPolycartHistoryTool`, `Support/`, and `Atrium/` (dashboard screens, `ScreenAccess`, `PolycartSupportFeature`).
- Atrium owns every component and style: Polycart ships no stylesheet, and views use only `x-atrium::*` components and the utilities Atrium safelists, never `<style>` or `style=`. `tests/Feature/Ui/StylesTest.php` checks both with `AtriumStyles`. `ScreenAccess` delegates to Atrium's `ScreenAccess::allows('polycart', ...)` and keeps only what is Polycart's own: operators and abilities with arguments.

## Quick Commands

- Full validation: `composer test`
- Formatting check: `composer lint:check`
- Static analysis: `composer analyse`
- Pest tests: `composer test:unit`
- Workbench build: `composer build`
- Workbench server: `composer serve`

## Local Skills

- `package-scaffold`: use when adding package capabilities or wiring them through the service provider, including commands, migrations, routes, config, views, translations, assets, middleware, publish tags, workbench files, and console-only behavior.
- `package-testing`: use when adding or changing package tests with Pest 4/5 and Orchestra Testbench.
- `package-release`: use when preparing changelog, release notes, tags, or GitHub release workflow changes.
- `package-compatibility`: use when reviewing code, dependencies, or CI against the PHP and Laravel support matrix.
- `package-generate-skill`: use when updating the bundled Boost skill from the package implementation, README, and examples.

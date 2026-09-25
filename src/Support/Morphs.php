<?php

declare(strict_types=1);

namespace JayI\Polycart\Support;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use JayI\Polycart\Exceptions\UnknownMorphException;

/**
 * Turns an owner or purchasable named in an API or MCP call into a model.
 *
 * Only the models listed in `polycart.owners`, `polycart.scopes` and
 * `polycart.purchasables` can be reached, so a caller can never make the package load an arbitrary
 * class. Either the alias (the array key) or the class itself is accepted.
 */
final class Morphs
{
    public function __construct(private readonly Config $config) {}

    /**
     * The owner described by validated input: a model, a guest session key,
     * or nobody.
     *
     * @param  array<string, mixed>  $data
     */
    public function ownerFrom(array $data): Model|string|null
    {
        if (is_string($data['owner_type'] ?? null) && is_scalar($data['owner_id'] ?? null)) {
            return $this->find('owners', $data['owner_type'], (string) $data['owner_id']);
        }

        return is_string($data['session_key'] ?? null) ? $data['session_key'] : null;
    }

    /**
     * The scope described by validated input, or null for an unscoped cart.
     *
     * @param  array<string, mixed>  $data
     */
    public function scopeFrom(array $data): ?Model
    {
        if (is_string($data['scope_type'] ?? null) && is_scalar($data['scope_id'] ?? null)) {
            return $this->find('scopes', $data['scope_type'], (string) $data['scope_id']);
        }

        return null;
    }

    /**
     * A cart member: a participant from `polycart.owners`, or a whole scope
     * from `polycart.scopes`.
     *
     * @param  array<string, mixed>  $data
     */
    public function memberFrom(array $data): Model
    {
        /** @var string $type */
        $type = $data['member_type'];

        /** @var int|string $id */
        $id = $data['member_id'];

        $group = $this->lookup('owners', $type) === null ? 'scopes' : 'owners';

        return $this->find($group, $type, (string) $id);
    }

    /**
     * The purchasable described by validated input, or null for a custom line.
     *
     * @param  array<string, mixed>  $data
     */
    public function purchasableFrom(array $data): ?Model
    {
        if (is_string($data['purchasable_type'] ?? null) && is_scalar($data['purchasable_id'] ?? null)) {
            return $this->find('purchasables', $data['purchasable_type'], (string) $data['purchasable_id']);
        }

        return null;
    }

    /**
     * The morph class stored for an owner alias, for filtering.
     *
     * An unknown value is returned as given: comparing a string is harmless,
     * and it still matches rows written under that exact morph class.
     */
    public function ownerMorphClass(string $type): string
    {
        $class = $this->lookup('owners', $type);

        return $class === null ? $type : (new $class)->getMorphClass();
    }

    /**
     * The morph class stored for a scope alias, for filtering.
     */
    public function scopeMorphClass(string $type): string
    {
        $class = $this->lookup('scopes', $type);

        return $class === null ? $type : (new $class)->getMorphClass();
    }

    private function find(string $group, string $type, string $id): Model
    {
        $class = $this->lookup($group, $type) ?? throw UnknownMorphException::notAllowed($group, $type);

        return $class::query()->findOrFail($id);
    }

    /**
     * @return class-string<Model>|null
     */
    private function lookup(string $group, string $type): ?string
    {
        /** @var array<string, class-string<Model>> $allowed */
        $allowed = $this->config->get('polycart.'.$group, []);

        if (isset($allowed[$type])) {
            return $allowed[$type];
        }

        $class = Relation::getMorphedModel($type) ?? $type;

        return in_array($class, $allowed, true) ? $class : null;
    }
}

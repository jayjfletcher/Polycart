<?php

declare(strict_types=1);

namespace JayI\Polycart\Tests\Fixtures\Types;

use Closure;
use JayI\Polycart\Pipeline\PendingLine;
use JayI\Polycart\Types\CartType;

/**
 * Checks after the line is written, to prove a late rejection still rolls
 * the write back.
 */
final class AuditedCart extends CartType
{
    public function addLineStages(): array
    {
        return [
            ...parent::addLineStages(),
            function (PendingLine $line, Closure $next): PendingLine {
                if (($line->meta['fail_after_write'] ?? false) === true) {
                    $line->reject('The audit refused it.', 'audit_failed');
                }

                return $next($line);
            },
        ];
    }
}

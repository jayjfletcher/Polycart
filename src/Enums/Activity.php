<?php

declare(strict_types=1);

namespace JayI\Polycart\Enums;

/**
 * What happened to a cart, for the changes the package records itself.
 *
 * Stored as a plain string, so an application can record its own —
 * `exported`, `emailed` — with Cart::recordActivity().
 */
enum Activity: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Cleared = 'cleared';
    case StatusChanged = 'status_changed';

    /** This cart was converted: retyped in place, or copied into another. */
    case Converted = 'converted';

    /** This cart is a copy converted from another. */
    case ConvertedFrom = 'converted_from';

    /** Another cart's lines were merged into this one. */
    case Merged = 'merged';

    /** This cart's lines were merged into another. */
    case MergedInto = 'merged_into';

    case LineAdded = 'line_added';
    case LineUpdated = 'line_updated';
    case LineRemoved = 'line_removed';
    case Shared = 'shared';
    case Unshared = 'unshared';
    case VisibilityChanged = 'visibility_changed';
}

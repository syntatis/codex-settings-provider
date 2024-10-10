<?php

declare(strict_types=1);

namespace Codex\Settings;

/**
 * Validate whether the value is a valid IP address.
 *
 * A value will be considered blank if it is:
 * - an empty string
 * - an empty array
 * - false
 * - null
 *
 * @param mixed $value
 *
 * @phpstan-assert-if-true ''|array{}|false|null $value
 *
 * @internal Internal use only.
 */
function is_blank($value): bool
{
	if ($value === false || $value === null) {
		return true;
	}

	if (is_string($value) && trim($value) === '') {
		return true;
	}

	return is_array($value) && count($value) === 0;
}

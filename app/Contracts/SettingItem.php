<?php

declare(strict_types=1);

namespace Codex\Settings\Contracts;

/**
 * @phpstan-type ValueDefault bool|float|int|string|array<array-key, bool|float|int|string|array<array-key, mixed>>|null
 * @phpstan-type ValueType 'string'|'boolean'|'integer'|'number'|'array'|'object'
 */
interface SettingItem
{
	public function getName(): string;

	/** @phpstan-return ValueType */
	public function getType(): string;

	/**
	 * @return mixed
	 * @phpstan-return ValueDefault
	 */
	public function getDefault();
}

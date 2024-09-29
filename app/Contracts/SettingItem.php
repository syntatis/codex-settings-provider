<?php

declare(strict_types=1);

namespace Codex\Settings\Contracts;

/**
 * Represents setting and its various arguments to register with the WordPress settings API.
 *
 * @phpstan-type ValueDefault bool|float|int|string|array<array-key, bool|float|int|string|array<array-key, mixed>>|null
 * @phpstan-type ValueType 'string'|'boolean'|'integer'|'number'|'array'|'object'
 * @phpstan-type ValueFormat 'date-time'|'uri'|'email'|'ip'|'uuid'|'hex-color'
 * @phpstan-type APISchemaProperties array<string, array{type: ValueType, default?: array<mixed>|bool|float|int|string}>
 * @phpstan-type APISchema array{properties?: APISchemaProperties, items?: array{type?: ValueType, format?: ValueFormat}}
 * @phpstan-type APIConfig array{name?: string, schema: APISchema}
 * @phpstan-type SettingVars array{description?: string, show_in_rest?: APIConfig|bool}
 * @phpstan-type SettingArgs array{type: ValueType, default: ValueDefault, description?: string, show_in_rest?: APIConfig|bool}
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

	/**
	 * @return array<string,mixed>
	 * @phpstan-return SettingArgs
	 */
	public function getArgs(): array;
}

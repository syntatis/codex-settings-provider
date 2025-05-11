<?php

declare(strict_types=1);

namespace Codex\Settings;

use Codex\Settings\Contracts\SettingItem;
use InvalidArgumentException;

use function array_merge;
use function is_array;

/**
 * @phpstan-import-type ValueDefault from SettingItem
 * @phpstan-import-type ValueType from SettingItem
 * @phpstan-import-type SettingVars from SettingItem
 * @phpstan-import-type SettingArgs from SettingItem
 * @phpstan-import-type APISchema from SettingItem
 */
class Setting implements SettingItem
{
	protected string $name;

	/** @phpstan-var ValueType */
	protected string $type = 'string';

	/** @phpstan-var ValueDefault */
	protected $default = null;

	/**
	 * @var array<string,mixed>
	 * @phpstan-var SettingVars
	 */
	protected array $settingVars = ['show_in_rest' => true];

	/** @phpstan-param ValueType $type */
	public function __construct(string $name, string $type = 'string')
	{
		if (is_blank($name)) {
			throw new InvalidArgumentException('Option name must not be blank.');
		}

		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * @param array|bool|float|int|string $value
	 * @phpstan-param ValueDefault $value
	 *
	 * @return static
	 */
	public function withDefault($value)
	{
		$self = clone $this;
		$self->default = $value;

		return $self;
	}

	/** @return static */
	public function withLabel(string $label)
	{
		$self = clone $this;
		$self->settingVars['label'] = $label;

		return $self;
	}

	/** @return static */
	public function withDescription(string $value)
	{
		$self = clone $this;
		$self->settingVars['description'] = $value;

		return $self;
	}

	/**
	 * Whether to show the option on WordPress REST API endpoint, `/wp/v2/settings`.
	 *
	 * @param array|bool $schema
	 * @phpstan-param APISchema|bool $schema
	 *
	 * @return static
	 */
	public function apiSchema($schema)
	{
		$self = clone $this;

		if (is_array($schema)) {
			$self->settingVars['show_in_rest'] = ['schema' => $schema];
		} else {
			$self->settingVars['show_in_rest'] = $schema;
		}

		return $self;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/** @phpstan-return ValueType */
	public function getType(): string
	{
		return $this->type;
	}

	/** @phpstan-return ValueDefault */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Retrieve the arguments to pass for the `register_setting` function.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_setting/#parameters
	 *
	 * @phpstan-return SettingArgs
	 */
	public function getArgs(): array
	{
		return array_merge([
			'type' => $this->type,
			'default' => $this->default,
		], $this->settingVars);
	}
}

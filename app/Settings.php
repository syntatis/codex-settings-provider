<?php

declare(strict_types=1);

namespace Codex\Settings;

use Codex\Settings\Contracts\SettingItem;
use IteratorAggregate;
use Syntatis\Utils\Val;
use Traversable;

/** @phpstan-implements IteratorAggregate<string,Registry> */
class Settings implements IteratorAggregate
{
	/** @var array<string,Registry> */
	private array $registries;

	/** @param array<string,Registry> $registries */
	public function __construct(array $registries)
	{
		$this->registries = $registries;
	}

	/**
	 * Retrieve collection of registered settings for a given group, or a specific setting
	 * from the group if the option name is provided.
	 *
	 * @return array<string,SettingItem>|SettingItem|null
	 */
	public function get(string $group, ?string $optionName = null)
	{
		$registry = $this->registries[$group] ?? null;

		if (! ($registry instanceof Registry)) {
			return null;
		}

		if (Val::isBlank($optionName)) {
			return $this->registries[$group]->getRegisteredSettings();
		}

		return $this->registries[$group]->getRegisteredSettings($optionName);
	}

	/** @return Traversable<string,Registry> */
	public function getIterator(): Traversable
	{
		foreach ($this->registries as $group => $registry) {
			yield $group => $registry;
		}
	}
}

<?php

declare(strict_types=1);

namespace Codex\Settings;

use IteratorAggregate;
use Syntatis\Utils\Val;
use Traversable;

use function is_array;

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
	 * @return array<string,RecordedSetting>|RecordedSetting|null
	 */
	public function get(string $group, ?string $optionName = null)
	{
		$registry = $this->registries[$group] ?? null;

		if (! ($registry instanceof Registry)) {
			return null;
		}

		if (Val::isBlank($optionName)) {
			return $this->registries[$group]->getSettings();
		}

		return $this->registries[$group]->getSettings($optionName);
	}

	/** @return array<string,array<string,RecordedSetting>> */
	public function getAll(): array
	{
		$all = [];

		foreach ($this->registries as $group => $registry) {
			$settings = $registry->getSettings();

			if (! is_array($settings) || Val::isBlank($settings)) {
				continue;
			}

			$all[$group] = $settings;
		}

		return $all;
	}

	/** @return Traversable<string,Registry> */
	public function getIterator(): Traversable
	{
		foreach ($this->registries as $group => $registry) {
			yield $group => $registry;
		}
	}
}

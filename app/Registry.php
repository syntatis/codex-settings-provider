<?php

declare(strict_types=1);

namespace Codex\Settings;

use Codex\Settings\Contracts\SettingItem;
use InvalidArgumentException;
use Syntatis\Utils\Val;

class Registry
{
	private string $prefix = '';

	/** @phpstan-var non-empty-string $settingGroup */
	private string $settingGroup;

	/** @var array<string,Setting> */
	private array $settings = [];

	/**
	 * List of settings that have been registered.
	 *
	 * @var array<string,RegisteredSetting>
	 */
	private array $registered = [];

	public function __construct(string $settingGroup, string $prefix = '')
	{
		if (Val::isBlank($settingGroup)) {
			throw new InvalidArgumentException('The setting group cannot be empty.');
		}

		$this->settingGroup = $settingGroup;
		$this->prefix = $prefix;
	}

	public function addSettings(SettingItem ...$settings): void
	{
		foreach ($settings as $key => $setting) {
			$this->settings[$this->getPrefixedName($setting)] = $setting;
		}
	}

	/**
	 * Retrieve the setting group that the registry is handling.
	 */
	public function getSettingGroup(): string
	{
		return $this->settingGroup;
	}

	/**
	 * Retrieve all the settings that's been added in the registry or one with specific name.
	 *
	 * @return array<string,Setting>|Setting|null
	 */
	public function getSettings(?string $name = null)
	{
		if (! Val::isBlank($name)) {
			return $this->settings[$this->getPrefixedName($name)] ?? null;
		}

		return $this->settings;
	}

	/**
	 * Retrieve all the settings that have been registered.
	 *
	 * @return array<string,RegisteredSetting>|RegisteredSetting|null
	 */
	public function getRegisteredSettings(?string $name = null)
	{
		if (! Val::isBlank($name)) {
			return $this->registered[$this->getPrefixedName($name)] ?? null;
		}

		return $this->registered;
	}

	public function register(): void
	{
		foreach ($this->settings as $setting) {
			$registeredSetting = new RegisteredSetting($setting, $this->prefix);
			$prefixedName = $registeredSetting->getName();

			register_setting(
				$this->settingGroup,
				$prefixedName,
				$setting->getSettingArgs(),
			);

			$this->registered[$prefixedName] = $registeredSetting;
		}
	}

	/**
	 * Remove options from the registry.
	 *
	 * @param bool $delete Whether to delete the options from the database.
	 */
	public function deregister(bool $delete = false): void
	{
		foreach ($this->registered as $settingName => $registeredSetting) {
			$prefixedName = $registeredSetting->getName();

			unregister_setting($this->settingGroup, $prefixedName);
			unset($this->registered[$prefixedName]);

			if ($delete !== true) {
				continue;
			}

			delete_option($prefixedName);
		}
	}

	/** @param string|SettingItem $setting The setting object or the setting name. */
	private function getPrefixedName($setting): string
	{
		if ($setting instanceof SettingItem) {
			$setting = $setting->getName();
		}

		return $this->prefix . $setting;
	}
}

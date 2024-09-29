<?php

declare(strict_types=1);

namespace Codex\Settings;

use Codex\Settings\Contracts\SettingItem;

class RecordedSetting implements SettingItem
{
	private Setting $setting;

	private string $prefix = '';

	public function __construct(Setting $setting, string $prefix = '')
	{
		$this->setting = $setting;
		$this->prefix = $prefix;
	}

	public function getName(): string
	{
		return $this->prefix . $this->setting->getName();
	}

	public function getType(): string
	{
		return $this->setting->getType();
	}

	/** @inheritDoc */
	public function getDefault()
	{
		return $this->setting->getDefault();
	}

	/** @inheritDoc */
	public function getArgs(): array
	{
		return $this->setting->getArgs();
	}
}

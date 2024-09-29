<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Settings\Registry;
use Codex\Settings\Setting;
use Codex\Settings\Settings;

class SettingsTest extends WPTestCase
{
	public function testGet(): void
	{
		$saySetting = new Setting('say');
		$helloSetting = new Setting('hello');

		$group1 = new Registry('group1');
		$group1->addSettings(
			$saySetting,
			$helloSetting,
		);

		$worldSetting = new Setting('world');

		$group2 = new Registry('group2');
		$group2->addSettings($worldSetting);

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$this->assertSame([
			'say' => $saySetting,
			'hello' => $helloSetting,
		], $settings->get('group1'));
		$this->assertSame(['world' => $worldSetting], $settings->get('group2'));
	}

	public function testGetAll(): void
	{
		$saySetting = new Setting('say');
		$helloSetting = new Setting('hello');

		$group1 = new Registry('group1');
		$group1->addSettings($saySetting, $helloSetting);

		$worldSetting = new Setting('world');

		$group2 = new Registry('group2');
		$group2->addSettings($worldSetting);

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$this->assertSame([
			'group1' => [
				'say' => $saySetting,
				'hello' => $helloSetting,
			],
			'group2' => ['world' => $worldSetting],
		], $settings->getAll());
	}
}

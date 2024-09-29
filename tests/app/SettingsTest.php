<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Settings\RegisteredSetting;
use Codex\Settings\Registry;
use Codex\Settings\Setting;
use Codex\Settings\Settings;

class SettingsTest extends WPTestCase
{
	public function testGetBeforeRegistered(): void
	{
		$settings = new Settings([
			'group1' => (new Registry('group1'))->addSettings(
				new Setting('say'),
				new Setting('hello'),
			),
			'group2' => (new Registry('group2'))->addSettings(
				new Setting('world'),
			),
		]);

		$this->assertNull($settings->get('group1'));
		$this->assertNull($settings->get('group2'));
	}

	public function testGetAfterRegistered(): void
	{
		$group1 = (new Registry('group1'));
		$group1->addSettings(new Setting('say'), new Setting('hello'));
		$group1->register();

		$group2 = (new Registry('group2'));
		$group2->addSettings(new Setting('world'));
		$group2->register();

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$registryGroup1 = $settings->get('group1');

		$this->assertArrayHasKey('say', $registryGroup1);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup1['say']);
		$this->assertSame('say', $registryGroup1['say']->getName());

		$this->assertArrayHasKey('hello', $registryGroup1);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup1['hello']);
		$this->assertSame('hello', $registryGroup1['hello']->getName());

		$registryGroup2 = $settings->get('group2');

		$this->assertArrayHasKey('world', $registryGroup2);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup2['world']);
		$this->assertSame('world', $registryGroup2['world']->getName());
	}

	public function testGetAfterRegisteredWithPrefix(): void
	{
		$group1 = (new Registry('group1'));
		$group1->addSettings(new Setting('say'), (new Setting('hello'))->withDefault('world'));
		$group1->register();

		$group2 = (new Registry('group2'));
		$group2->addSettings(new Setting('world', 'boolean'));
		$group2->register();

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$registryGroup1 = $settings->get('group1');

		$this->assertArrayHasKey('say', $registryGroup1);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup1['say']);
		$this->assertSame('say', $registryGroup1['say']->getName());
		$this->assertNull($registryGroup1['say']->getDefault());

		$this->assertArrayHasKey('hello', $registryGroup1);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup1['hello']);
		$this->assertSame('hello', $registryGroup1['hello']->getName());
		$this->assertSame('world', $registryGroup1['hello']->getDefault());

		$registryGroup2 = $settings->get('group2');

		$this->assertArrayHasKey('world', $registryGroup2);
		$this->assertInstanceOf(RegisteredSetting::class, $registryGroup2['world']);
		$this->assertNull($registryGroup2['world']->getDefault());
		$this->assertSame('world', $registryGroup2['world']->getName());
		$this->assertSame('boolean', $registryGroup2['world']->getType());

		$optionGroup2 = $settings->get('group2', 'world');

		$this->assertInstanceOf(RegisteredSetting::class, $optionGroup2);
		$this->assertSame('world', $optionGroup2->getName());
		$this->assertNull($optionGroup2->getDefault());
		$this->assertSame('boolean', $optionGroup2->getType());

		$optionGroupNotExist = $settings->get('group2', 'world2');

		$this->assertNull($optionGroupNotExist);
	}
}

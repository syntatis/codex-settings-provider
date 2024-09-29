<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Settings\RecordedSetting;
use Codex\Settings\Registry;
use Codex\Settings\Setting;
use Codex\Settings\Settings;

class SettingsTest extends WPTestCase
{
	public function testGet(): void
	{
		$saySetting = new Setting('say');
		$helloSetting = new Setting('hello', 'number');
		$worldSetting = new Setting('world', 'boolean');

		$group1 = new Registry('group1');
		$group1->addSettings(
			$saySetting,
			$helloSetting,
		);

		$group2 = new Registry('group2');
		$group2->addSettings($worldSetting);

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$this->assertArrayHasKey('say', $settings->get('group1'));
		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group1')['say']);
		$this->assertSame('say', $settings->get('group1')['say']->getName());
		$this->assertNull($settings->get('group1')['say']->getDefault());
		$this->assertSame('string', $settings->get('group1')['say']->getType());

		$this->assertArrayHasKey('hello', $settings->get('group1'));
		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group1')['hello']);
		$this->assertSame('hello', $settings->get('group1')['hello']->getName());
		$this->assertNull($settings->get('group1')['hello']->getDefault());
		$this->assertSame('number', $settings->get('group1')['hello']->getType());

		$this->assertArrayHasKey('world', $settings->get('group2'));
		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group2')['world']);
		$this->assertSame('world', $settings->get('group2')['world']->getName());
		$this->assertNull($settings->get('group2')['world']->getDefault());
		$this->assertSame('boolean', $settings->get('group2')['world']->getType());
	}

	public function testGetWithSpecifiedName(): void
	{
		$saySetting = new Setting('say');
		$helloSetting = new Setting('hello', 'number');
		$worldSetting = new Setting('world', 'boolean');

		$group1 = new Registry('group1');
		$group1->addSettings(
			$saySetting,
			$helloSetting,
		);

		$group2 = new Registry('group2');
		$group2->addSettings($worldSetting);

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group1', 'say'));
		$this->assertSame('say', $settings->get('group1', 'say')->getName());
		$this->assertNull($settings->get('group1', 'say')->getDefault());
		$this->assertSame('string', $settings->get('group1', 'say')->getType());

		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group1', 'hello'));
		$this->assertSame('hello', $settings->get('group1', 'hello')->getName());
		$this->assertNull($settings->get('group1', 'hello')->getDefault());
		$this->assertSame('number', $settings->get('group1', 'hello')->getType());

		$this->assertInstanceOf(RecordedSetting::class, $settings->get('group2', 'world'));
		$this->assertSame('world', $settings->get('group2', 'world')->getName());
		$this->assertNull($settings->get('group2', 'world')->getDefault());
		$this->assertSame('boolean', $settings->get('group2', 'world')->getType());
	}

	public function testGetAll(): void
	{
		$saySetting = new Setting('say');
		$helloSetting = new Setting('hello', 'number');
		$worldSetting = new Setting('world', 'boolean');

		$group1 = new Registry('group1');
		$group1->addSettings(
			$saySetting,
			$helloSetting,
		);

		$group2 = new Registry('group2');
		$group2->addSettings($worldSetting);

		$settings = new Settings([
			'group1' => $group1,
			'group2' => $group2,
		]);

		// Group 1
		$this->assertArrayHasKey('group1', $settings->getAll());

		$this->assertArrayHasKey('say', $settings->getAll()['group1']);
		$this->assertInstanceOf(RecordedSetting::class, $settings->getAll()['group1']['say']);
		$this->assertSame('say', $settings->getAll()['group1']['say']->getName());
		$this->assertNull($settings->getAll()['group1']['say']->getDefault());
		$this->assertSame('string', $settings->getAll()['group1']['say']->getType());

		$this->assertArrayHasKey('hello', $settings->getAll()['group1']);
		$this->assertInstanceOf(RecordedSetting::class, $settings->getAll()['group1']['hello']);
		$this->assertSame('hello', $settings->getAll()['group1']['hello']->getName());
		$this->assertNull($settings->getAll()['group1']['hello']->getDefault());
		$this->assertSame('number', $settings->getAll()['group1']['hello']->getType());

		// Group 2
		$this->assertArrayHasKey('group2', $settings->getAll());

		$this->assertArrayHasKey('world', $settings->getAll()['group2']);
		$this->assertInstanceOf(RecordedSetting::class, $settings->getAll()['group2']['world']);
		$this->assertSame('world', $settings->getAll()['group2']['world']->getName());
		$this->assertNull($settings->getAll()['group2']['world']->getDefault());
		$this->assertSame('boolean', $settings->getAll()['group2']['world']->getType());
	}

	public function testGetInvalidRegistry(): void
	{
		$settings = new Settings([]);

		$this->assertNull($settings->get('group1'));
		$this->assertNull($settings->get('group1', 'say'));
	}

	public function testGetAllInvalidRegistry(): void
	{
		$settings = new Settings([]);

		$this->assertEmpty($settings->getAll());
	}

	public function testGetEmptySettings(): void
	{
		$group1 = new Registry('group1');
		$group1->addSettings(...[]);

		$settings = new Settings(['group1' => $group1]);

		$this->assertIsArray($settings->get('group1'));
		$this->assertEmpty($settings->get('group1'));
	}

	public function testGetAllEmptySettings(): void
	{
		$group1 = new Registry('group1');
		$group1->addSettings(...[]);

		$settings = new Settings(['group1' => $group1]);

		$this->assertIsArray($settings->getAll());
		$this->assertEmpty($settings->getAll());
	}
}

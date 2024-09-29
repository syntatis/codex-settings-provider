<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Settings\Setting;
use InvalidArgumentException;

class SettingTest extends WPTestCase
{
	public function testBlankName(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Setting('');
	}

	public function testName(): void
	{
		$this->assertSame('say', (new Setting('say'))->getName());
	}

	public function testDefault(): void
	{
		$setting = new Setting('say');

		$this->assertNull($setting->getDefault());

		$setting = $setting->withDefault('bar');

		$this->assertSame('bar', $setting->getDefault());
	}

	public function testApiSchema(): void
	{
		$setting = new Setting('say', 'array');

		$this->assertSame(
			[
				'type' => 'array',
				'default' => null,
				'show_in_rest' => true,
			],
			$setting->getSettingArgs(),
		);

		$setting = $setting->apiSchema([
			'items' => ['type' => 'string'],
		]);

		$this->assertSame([
			'type' => 'array',
			'default' => null,
			'show_in_rest' => [
				'schema' => [
					'items' => ['type' => 'string'],
				],
			],
		], $setting->getSettingArgs());
	}

	public function testGetSettingArgs(): void
	{
		$setting = new Setting('say');

		$this->assertEquals([
			'type' => 'string',
			'default' => null,
			'show_in_rest' => true,
		], $setting->getSettingArgs());
	}
}

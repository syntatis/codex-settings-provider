<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Foundation\Hooks\Hook;
use Codex\Settings\RecordedSetting;
use Codex\Settings\Registry;
use Codex\Settings\Setting;
use InvalidArgumentException;

use function version_compare;

class RegistryTest extends WPTestCase
{
	private Hook $hook;

	// phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
	public function set_up(): void
	{
		parent::set_up();

		$this->hook = new Hook();
	}

	/** @dataProvider dataInvalidGroup */
	public function testInvalidGroup(string $group): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Registry($group);
	}

	public static function dataInvalidGroup(): iterable
	{
		yield [''];
		yield [' '];
	}

	public function testGetSettingGroup(): void
	{
		$registry = new Registry('codex');
		$this->assertSame('codex', $registry->getSettingGroup());
	}

	public function testRecordedSettings(): void
	{
		$registry = new Registry('codex');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!')
				->withLabel('Say'),
			(new Setting('count', 'integer'))
				->withDefault(1)
				->withDescription('How many time?'),
			(new Setting('list', 'array'))
				->withDefault(['count', 'two', 'three'])
				->apiSchema(['items' => ['type' => 'string']]),
		]);
		$recordedSettings = get_registered_settings();

		$this->assertArrayNotHasKey('say', $recordedSettings);
		$this->assertArrayNotHasKey('count', $recordedSettings);
		$this->assertArrayNotHasKey('list', $recordedSettings);

		$registry->register();

		$recordedSettings = get_registered_settings();

		$this->assertArrayHasKey('say', $recordedSettings);
		$this->assertSame('string', $recordedSettings['say']['type']);
		$this->assertSame('', $recordedSettings['say']['description']);
		$this->assertTrue($recordedSettings['say']['show_in_rest']);

		if (version_compare($GLOBALS['wp_version'], '6.6', '>=')) {
			$this->assertSame('Say', $recordedSettings['say']['label']);
		}

		$this->assertArrayHasKey('count', $recordedSettings);
		$this->assertSame('integer', $recordedSettings['count']['type']);
		$this->assertSame('How many time?', $recordedSettings['count']['description']);
		$this->assertTrue($recordedSettings['count']['show_in_rest']);

		if (version_compare($GLOBALS['wp_version'], '6.6', '>=')) {
			$this->assertSame('', $recordedSettings['count']['label']);
		}

		$this->assertArrayHasKey('list', $recordedSettings);
		$this->assertSame('array', $recordedSettings['list']['type']);
		$this->assertSame('', $recordedSettings['list']['description']);
		$this->assertEquals([
			'schema' => ['items' => ['type' => 'string']],
		], $recordedSettings['list']['show_in_rest']);

		if (version_compare($GLOBALS['wp_version'], '6.6', '>=')) {
			$this->assertSame('', $recordedSettings['list']['label']);
		}

		$registry->deregister();

		$recordedSettings = get_registered_settings();

		$this->assertArrayNotHasKey('say', $recordedSettings);
		$this->assertArrayNotHasKey('count', $recordedSettings);
		$this->assertArrayNotHasKey('list', $recordedSettings);
	}

	public function testDefault(): void
	{
		$registry = new Registry('codex');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
			(new Setting('count', 'number'))
				->withDefault(1),
			(new Setting('list', 'array'))
				->withDefault(['count', 'two', 'three'])
				->apiSchema(['items' => ['type' => 'string']]),
		]);

		$this->assertFalse(get_option('say'));
		$this->assertFalse(get_option('count'));
		$this->assertFalse(get_option('list'));

		$registry->register();

		$this->assertSame('Hello, World!', get_option('say'));
		$this->assertSame(1, get_option('count'));
		$this->assertSame(['count', 'two', 'three'], get_option('list'));

		$registry->deregister();

		$this->assertFalse(get_option('say'));
		$this->assertFalse(get_option('count'));
		$this->assertFalse(get_option('list'));
	}

	public function testPrefix(): void
	{
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
			(new Setting('count', 'number'))
				->withDefault(1),
			(new Setting('list', 'array'))
				->withDefault(['count', 'two', 'three'])
				->apiSchema(['items' => ['type' => 'string']]),
		]);

		$this->assertFalse(get_option('codex_say'));
		$this->assertFalse(get_option('codex_count'));
		$this->assertFalse(get_option('codex_list'));

		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertSame(1, get_option('codex_count'));
		$this->assertSame(['count', 'two', 'three'], get_option('codex_list'));

		$registry->deregister();

		$this->assertFalse(get_option('codex_say'));
		$this->assertFalse(get_option('codex_count'));
		$this->assertFalse(get_option('codex_list'));
	}

	public function testAddOption(): void
	{
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertTrue(add_option('codex_say', 'Hi'));
		$this->assertSame('Hi', get_option('codex_say'));
	}

	public function testUpdateOption(): void
	{
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertTrue(add_option('codex_say', 'Hi'));
		$this->assertSame('Hi', get_option('codex_say'));
		$this->assertTrue(update_option('codex_say', 'Hai'));
		$this->assertSame('Hai', get_option('codex_say'));
	}

	public function testDeleteOption(): void
	{
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertTrue(update_option('codex_say', 'Hai'));
		$this->assertSame('Hai', get_option('codex_say'));
		$this->assertTrue(delete_option('codex_say'));
		$this->assertSame('Hello, World!', get_option('codex_say'));
	}

	public function testPassingDefault(): void
	{
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertSame('Hai', get_option('codex_say', 'Hai'));
	}

	public function testDeregister(): void
	{
		$wpdb = $GLOBALS['wpdb'];
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertTrue(update_option('codex_say', 'World'));
		$this->assertSame('World', get_option('codex_say'));

		// phpcs:ignore Squiz.Strings.DoubleQuoteUsage.ContainsVar
		$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'codex_say'));

		$this->assertEquals(['option_value' => 'World'], (array) $row);

		$registry->deregister();

		// phpcs:ignore Squiz.Strings.DoubleQuoteUsage.ContainsVar
		$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'codex_say'));

		$this->assertEquals(['option_value' => 'World'], (array) $row);
		$this->assertSame('World', get_option('codex_say'));
	}

	public function testDeregisterWithDelete(): void
	{
		$wpdb = $GLOBALS['wpdb'];
		$registry = new Registry('codex', 'codex_');
		$registry->addSettings(...[
			(new Setting('say', 'string'))
				->withDefault('Hello, World!'),
		]);
		$registry->register();

		$this->assertSame('Hello, World!', get_option('codex_say'));
		$this->assertTrue(update_option('codex_say', 'World'));
		$this->assertSame('World', get_option('codex_say'));

		// phpcs:ignore Squiz.Strings.DoubleQuoteUsage.ContainsVar
		$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'codex_say'));

		$this->assertEquals(['option_value' => 'World'], (array) $row);

		$registry->deregister(true);

		// phpcs:ignore Squiz.Strings.DoubleQuoteUsage.ContainsVar
		$row = $wpdb->get_row($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name = %s LIMIT 1", 'codex_say'));

		$this->assertNull($row);
		$this->assertFalse(get_option('codex_say'));
	}

	public function testGetSettings(): void
	{
		$saySetting = new Setting('say', 'string');
		$countSetting = (new Setting('count', 'number'))->withDefault(1);
		$listSetting = (new Setting('list', 'array'))
			->withDefault(['count', 'two', 'three'])
			->apiSchema(['items' => ['type' => 'string']]);

		$registry = new Registry('codex');
		$registry->addSettings($saySetting, $countSetting, $listSetting);

		$this->assertCount(3, $registry->getSettings());
		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('say'));
		$this->assertNull($registry->getSettings('say')->getDefault());
		$this->assertSame('say', $registry->getSettings('say')->getName());

		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('count'));
		$this->assertSame(1, $registry->getSettings('count')->getDefault());
		$this->assertSame('count', $registry->getSettings('count')->getName());

		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('list'));
		$this->assertSame(['count', 'two', 'three'], $registry->getSettings('list')->getDefault());
		$this->assertSame('list', $registry->getSettings('list')->getName());
	}

	public function testGetSettingsWithPrefix(): void
	{
		$saySetting = new Setting('say', 'string');
		$countSetting = (new Setting('count', 'number'))->withDefault(1);
		$listSetting = (new Setting('list', 'array'))
			->withDefault(['count', 'two', 'three'])
			->apiSchema(['items' => ['type' => 'string']]);

		$registry = new Registry('codex', 'foo_');
		$registry->addSettings($saySetting, $countSetting, $listSetting);

		$this->assertCount(3, $registry->getSettings());
		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('say'));
		$this->assertNull($registry->getSettings('say')->getDefault());
		$this->assertSame('foo_say', $registry->getSettings('say')->getName());
		$this->assertSame('string', $registry->getSettings('say')->getType());
		$this->assertSame([
			'type' => 'string',
			'default' => null,
			'show_in_rest' => true,
		], $registry->getSettings('say')->getArgs());

		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('count'));
		$this->assertSame(1, $registry->getSettings('count')->getDefault());
		$this->assertSame('foo_count', $registry->getSettings('count')->getName());
		$this->assertSame('number', $registry->getSettings('count')->getType());
		$this->assertSame([
			'type' => 'number',
			'default' => 1,
			'show_in_rest' => true,
		], $registry->getSettings('count')->getArgs());

		$this->assertInstanceOf(RecordedSetting::class, $registry->getSettings('list'));
		$this->assertSame(['count', 'two', 'three'], $registry->getSettings('list')->getDefault());
		$this->assertSame('foo_list', $registry->getSettings('list')->getName());
		$this->assertSame('array', $registry->getSettings('list')->getType());
		$this->assertSame([
			'type' => 'array',
			'default' => ['count', 'two', 'three'],
			'show_in_rest' => [
				'schema' => ['items' => ['type' => 'string']],
			],
		], $registry->getSettings('list')->getArgs());
	}
}

<?php

declare(strict_types=1);

namespace Codex\Tests;

use Codex\Contracts\Extendable;
use Codex\Facades\App;
use Codex\Foundation\Hooks\Hook;
use Codex\Plugin;
use Codex\Settings\Provider;
use Pimple\Container;
use Psr\Container\ContainerInterface;

class ProviderTest extends WPTestCase
{
	private Hook $hook;

	private Container $container;

	// phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
	public function set_up(): void
	{
		parent::set_up();

		remove_action('admin_init', '_maybe_update_core');
		remove_action('admin_init', '_maybe_update_plugins');
		remove_action('admin_init', '_maybe_update_themes');
		remove_action('admin_init', '_wp_check_for_scheduled_split_terms');
		remove_action('admin_init', '_wp_check_for_scheduled_update_comment_type');
		remove_action('admin_init', 'default_password_nag_handler');
		remove_action('admin_init', 'handle_legacy_widget_preview_iframe', 20);
		remove_action('admin_init', 'register_admin_color_schemes');
		remove_action('admin_init', 'send_frame_options_header');
		remove_action('admin_init', 'wp_admin_headers');
		remove_action('admin_init', 'wp_schedule_update_network_counts');
		remove_action('admin_init', 'wp_schedule_update_user_counts');
		remove_action('admin_init', ['WP_Privacy_Policy_Content', 'add_suggested_content'], 1);
		remove_action('admin_init', ['WP_Privacy_Policy_Content', 'text_change_check'], 100);

		$app = new Plugin(
			new class () implements Extendable {
				public function getInstances(ContainerInterface $container): iterable
				{
					return [];
				}
			},
		);
		$app->setPluginFilePath(self::getFixturesPath('/plugin-name.php'));
		$app->addServices([Provider::class]);
		$app->boot();

		do_action('admin_init');
	}

	// phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps
	public function tear_down(): void
	{
		App::clearResolvedInstances();

		add_action('admin_init', '_wp_check_for_scheduled_split_terms');
		add_action('admin_init', '_wp_check_for_scheduled_update_comment_type');
		add_action('admin_init', 'default_password_nag_handler');
		add_action('admin_init', 'handle_legacy_widget_preview_iframe', 20);
		add_action('admin_init', 'register_admin_color_schemes');
		add_action('admin_init', 'send_frame_options_header');
		add_action('admin_init', 'wp_admin_headers');
		add_action('admin_init', 'wp_schedule_update_network_counts');
		add_action('admin_init', 'wp_schedule_update_user_counts');
		add_action('admin_init', ['WP_Privacy_Policy_Content', 'add_suggested_content'], 1);
		add_action('admin_init', ['WP_Privacy_Policy_Content', 'text_change_check'], 100);

		parent::tear_down();
	}

	/**
	 * Test default options.
	 *
	 * @see tests/phpunit/fixtures/inc/settings/*.php
	 */
	public function testDefaultValue(): void
	{
		$this->assertSame('', get_option('codex_foo_option_name'));
		$this->assertSame(0, get_option('codex_bar_option_name'));
		$this->assertNull(get_option('codex_baz_option_name')); // Registered with no default set.
		$this->assertFalse(get_option('codex_qux_option_name')); // Not registered.
	}

	/**
	 * Test updating option values.
	 *
	 * @see tests/phpunit/fixtures/inc/settings/*.php
	 */
	public function testUpdateValue(): void
	{
		$this->assertTrue(update_option('codex_foo_option_name', 'Hello World!'));
		$this->assertSame('Hello World!', get_option('codex_foo_option_name'));

		$this->assertTrue(update_option('codex_bar_option_name', 101));
		$this->assertSame(101, get_option('codex_bar_option_name'));

		$this->assertTrue(update_option('codex_baz_option_name', 10));
		$this->assertSame(10, get_option('codex_baz_option_name'));

		// Not registered.
		$this->assertTrue(update_option('codex_qux_option_name', 'abc'));
		$this->assertSame('abc', get_option('codex_qux_option_name'));
	}
}

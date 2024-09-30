<?php

declare(strict_types=1);

namespace Codex\Settings;

use Codex\Abstracts\ServiceProvider;
use Codex\Contracts\Hookable;
use Codex\Core\Config;
use Codex\Foundation\Hooks\Hook;
use InvalidArgumentException;
use Pimple\Container;
use RecursiveDirectoryIterator;
use SplFileInfo;
use Syntatis\Utils\Val;

use function dirname;
use function is_dir;
use function is_string;

class Provider extends ServiceProvider implements Hookable
{
	public function register(): void
	{
		$this->container[Settings::class] = static function (Container $container): Settings {
			/** @var Config $config */
			$config = $container['config'];
			$filePath = $container['plugin_file_path'] ?? '';
			$filePath = is_string($filePath) ? $filePath : '';

			if (Val::isBlank($filePath)) {
				throw new InvalidArgumentException('The plugin file path is required to register the settings.');
			}

			$settingsDir = wp_normalize_path(dirname($filePath) . '/inc/settings');

			if (! is_dir($settingsDir)) {
				throw new InvalidArgumentException('The settings directory does not exist.');
			}

			$settingFiles = new RecursiveDirectoryIterator(
				$settingsDir,
				RecursiveDirectoryIterator::SKIP_DOTS,
			);

			/** @var array<string,Registry> $registries */
			$registries = [];
			$prefix = '';

			if (! $config->isBlank('app.option_prefix')) {
				/** @var string $prefix */
				$prefix = $config->get('app.option_prefix');
			}

			foreach ($settingFiles as $settingFile) {
				if (
					! $settingFile instanceof SplFileInfo ||
					! $settingFile->isFile() ||
					$settingFile->getExtension() !== 'php'
				) {
					continue;
				}

				/** @var array<string,Setting> $register */
				$register = include $settingFile->getPathname();

				if (Val::isBlank($register)) {
					continue;
				}

				/**
				 * Defines the registry to register and manage the plugin settings.
				 *
				 * The registry allows us to register the plugin options in the WordPress
				 * Setting API with their type, default, and other attributes.
				 */

				/**
				 * The seting group is derived from the file name where the settings are added.
				 *
				 * If the App name is `acme` and the settings are added in the `/inc/settings/payments.php`,
				 * for example, the setting group would be `payments`.
				 *
				 * @var string $settingGroup
				 */
				$settingGroup = $settingFile->getBasename('.php');
				$registry = new Registry($settingGroup, $prefix);
				$registry->addSettings(...$register);

				$registries[$settingGroup] = $registry;
			}

			return new Settings($registries);
		};
	}

	public function hook(Hook $hook): void
	{
		/**
		 * Register all the options added in the registry.
		 *
		 * @var Settings $settings
		 */
		$settings = $this->container[Settings::class];

		foreach ($settings as $registry) {
			$hook->addAction('admin_init', [$registry, 'register']);
			$hook->addAction('rest_api_init', [$registry, 'register']);
		}
	}
}

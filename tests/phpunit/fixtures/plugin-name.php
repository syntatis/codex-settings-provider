<?php

declare(strict_types=1);

/**
 * Plugin bootstrap file.
 *
 * This file is read by WordPress to display the plugin's information in the admin area.
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Name
 * Plugin URI:        https://example.org/plugin/plugin-name
 * Description:       The plugin short description.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Author Name
 * Author URI:        https://example.org
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-name
 * Domain Path:       /inc/languages
 */

namespace PluginName;

use function defined;

// If this file is called directly, abort.
if (! defined('WPINC')) {
	die;
}

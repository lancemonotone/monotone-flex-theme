<?php

namespace monotone;

/*
 * Autoactivate plugins on theme activation
 */

class Autoactivate {
	private static array $plugins = [
		'advanced-custom-fields-pro/acf.php',
		'autoptimize/autoptimize.php',
		// 'classic-editor/classic-editor.php',
	];

	public function __construct() {
		add_action('after_switch_theme', [$this, 'autoactivate_plugins']);
	}

	public function autoactivate_plugins() {
		foreach (self::$plugins as $plugin) {
			$this->activate_plugin($plugin);
		}
	}

	private function activate_plugin($plugin) {
		if (!function_exists('activate_plugin')) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		activate_plugin($plugin);
	}
}

new Autoactivate();

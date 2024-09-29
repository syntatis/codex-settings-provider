<?php

use Codex\Settings\Setting;

return [
	(new Setting('foo_option_name'))
		->withDefault(''),
	(new Setting('bar_option_name', 'integer'))
		->withDefault(0),
	new Setting('baz_option_name'),
];

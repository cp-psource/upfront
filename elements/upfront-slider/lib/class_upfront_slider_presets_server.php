<?php

if (!class_exists('Upfront_Presets_Server')) {
	require_once Upfront::get_root_dir() . '/library/servers/class_upfront_presets_server.php';
}

class Upfront_Slider_Presets_Server extends Upfront_Presets_Server {
	private static $instance;

	public function get_element_name() {
		return 'slider';
	}

	public static function serve () {
		self::$instance = new self;
		self::$instance->_add_hooks();
		add_filter( 'get_element_preset_styles', array(self::$instance, 'get_preset_styles_filter')) ;
	}

	public static function get_instance() {
		return self::$instance;
	}

	public function get_preset_styles_filter($style) {
		$style .= self::$instance->get_presets_styles();
		return $style;
	}

	protected function get_style_template_path() {
		return realpath(Upfront::get_root_dir() . '/elements/upfront-slider/tpl/preset-style.html');
	}
	
	public static function get_preset_properties($preset) {
		$properties = self::$instance->get_preset_by_id($preset);

		return $properties;
	}
	
	public static function get_preset_defaults() {
		return array(
			'primaryStyle' => 'notext',
			'captionBackground' => 'rgb(255, 255, 255, 0.8)',
			'id' => 'default',
			'name' => self::$instance->get_l10n('default_preset')
		);
	}
}

Upfront_Slider_Presets_Server::serve();

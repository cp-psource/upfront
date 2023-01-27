<?php
/**
 * Tabbed element for Upfront
 */
class Upfront_UtabsView extends Upfront_Object {

	public static function default_properties() {
		$defaultTab = new StdClass();
		$defaultTab->title = '';
		$defaultTab->content = self::_get_l10n('default_tab_content');

		$secondTab = new StdClass();
		$secondTab->title = '';
		$secondTab->content = self::_get_l10n('second_tab_content');

		return array(
			'type' => 'UtabsModel',
			'view_class' => 'UtabsView',
			'has_settings' => 1,
			'class' =>  'upfront-tabs',
			'tabs' => array($defaultTab, $secondTab),
			'tabs_count' => 2,

			'id_slug' => 'utabs',
			'preset' => 'default'
		);
	}

	function __construct($data) {
		$data['properties'] = $this->merge_default_properties($data);
		parent::__construct($data);
	}

	protected function merge_default_properties($data){
		$flat = array();
		if(!isset($data['properties']))
				return $flat;

		foreach($data['properties'] as $prop) {
			if (isset($prop['value']) === false) continue;
			$flat[$prop['name']] = $prop['value'];
		}

		$flat = array_merge(self::default_properties(), $flat);

		$properties = array();
		foreach($flat as $name => $value)
				$properties[] = array('name' => $name, 'value' => $value);

		return $properties;
	}

	public function get_markup () {
		// This data is passed on to the template to precompile template
		$data = $this->properties_to_array();

		// Ensure tab title
		// Do shortcode
		foreach($data['tabs'] as $index=>$tab) {
			$ttl = trim(str_replace("\n", '', $tab['title']));
			if (empty($ttl)) {
				$tab['title'] = 'Tab ' . ($index + 1);
			}
			$tab['content'] = $this->_do_shortcode($tab['content']);
			$data['tabs'][$index] = $tab;
		}

		if (!$data['preset']) {
			$data['preset'] = 'default';
		}

		$data['wrapper_id'] = str_replace('utabs-object-', 'wrapper-', $data['element_id']);

		$markup = upfront_get_template('utabs', $data, dirname(dirname(__FILE__)) . '/tpl/utabs.html');

		// upfront_add_element_style('upfront_tabs', array('css/utabs.css', dirname(__FILE__)));
		upfront_add_element_script('upfront_tabs', array('js/utabs-front.js', dirname(__FILE__)));

		return $markup;
	}

	protected function _do_shortcode ($content) {
		return Upfront_Codec::get('wordpress')->do_shortcode($content);
	}

	public function add_js_defaults($data){
		$data['utabs'] = array(
				'defaults' => self::default_properties(),
				'template' => upfront_get_template_url('utabs', upfront_element_url('tpl/utabs.html', dirname(__FILE__)))
		);
		return $data;
	}

	private function properties_to_array(){
		$out = array();
		foreach($this->_data['properties'] as $prop)
				$out[$prop['name']] = $prop['value'];
		return $out;
	}

	public static function add_styles_scripts() {
		upfront_add_element_style('utabs-style', array('css/utabs.css', dirname(__FILE__)));
		//wp_enqueue_style('utabs-style', upfront_element_url('css/utabs.css', dirname(__FILE__)));
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['utabs_element'])) return $strings;
		$strings['utabs_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Tabs', 'upfront'),
			'default_tab_content' => __('<p>Klicke auf den Titel des aktiven Tabs, um den Titel zu bearbeiten. Bestätige mit der Eingabetaste.</p><p>Klicke auf die Plus-Schaltfläche [+], um eine neue Registerkarte hinzuzufügen.</p>', 'upfront'),
			'second_tab_content' => __('Viel Spaß mit Registerkarten.', 'upfront'),
			'tab_label'	=> __('Registerkarte', 'upfront'),
			'content_label'	=> __('Inhalt', 'upfront'),
			'tab_placeholder' => __('Registerkarte Inhalt', 'upfront'),
			'css' => array(
				'container_label' => __('Tabs-Container', 'upfront'),
				'container_info' => __('Die Ebene, die den gesamten Inhalt des Registerkartenelements enthält.', 'upfront'),
				'menu_label' => __('Registerkarten-Menü', 'upfront'),
				'menu_info' => __('Die Zeile, die alle Registerkarten enthält.', 'upfront'),
				'tabs_label' => __('Registerkarten', 'upfront'),
				'tabs_info' => __('Jede der Registerkarten.', 'upfront'),
				'active_tab_label' => __('Aktive Registerkarte', 'upfront'),
				'active_tab_info' => __('Die aktive Registerkarte', 'upfront'),
				'tab_content_label' => __('Registerkarte-Inhalt', 'upfront'),
				'tab_content_info' => __('Die Ebene, die den Registerkarteninhalt umschließt', 'upfront'),
				'tab_p_label' => __('Registerkarte-Inhaltsabsatz', 'upfront'),
				'tab_p_info' => __('Der Absatz, der Registerkarteninhalte enthält', 'upfront'),
				'active_content_label' => __('Aktive Registerkarte-Inhalt', 'upfront'),
				'active_content_info' => __('Die Ebene, die aktive Registerkarteninhalte umschließt', 'upfront'),
				'active_p_label' => __('Inhaltsabsatz der aktiven Registerkarte', 'upfront'),
				'active_p_info' => __('Der Absatz, der aktive Registerkarteninhalte enthält', 'upfront'),
			),
			'settings' => __('Einstellungen', 'upfront'),
			'add_tab' => __('Füge eine Registerkarte hinzu', 'upfront'),
			'default_preset' => __('Standard', 'upfront'),
			'content_area_colors_label' => __('Farben des Inhaltsbereichs', 'upfront'),
			'content_area_bg_label' => __('Inhaltsbereich BG', 'upfront'),
			'colors_label' => __('Farben', 'upfront'),
			'tab_typography_label' => __('Tab-Label-Typografie', 'upfront'),
			'tab_bg_label' => __('Tab-Hintergrund', 'upfront'),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

function upfront_tabs_add_local_url ($data) {
	$data['upfront_tabs'] = array(
		"root_url" => trailingslashit(upfront_element_url('/', dirname(__FILE__)))
	);
	return $data;
}
add_filter('upfront_data', 'upfront_tabs_add_local_url');

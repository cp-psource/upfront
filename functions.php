<?php

require 'psource/psource-plugin-update/psource-plugin-updater.php';
use Psource\PluginUpdateChecker\v5\PucFactory;
$MyUpdateChecker = PucFactory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=upfront',
	__FILE__, 
	'upfront'
);

/**
 * Haupteinstiegspunkt für den Upfront-Core
 *
 * Hier richten wir die Upfront-Hauptklasse ein
 * der Kern-Bootstrap und Ausführungskontext behandelt.
 */

require_once(dirname(__FILE__) . '/library/upfront_functions.php');
require_once(dirname(__FILE__) . '/library/upfront_functions_theme.php');
require_once(dirname(__FILE__) . '/library/class_upfront_cache_utils.php');
require_once(dirname(__FILE__) . '/library/class_upfront_permissions.php');
require_once(dirname(__FILE__) . '/library/class_upfront_registry.php');
require_once(dirname(__FILE__) . '/library/class_upfront_logger.php');
require_once(dirname(__FILE__) . '/library/class_upfront_debug.php');
require_once(dirname(__FILE__) . '/library/class_upfront_behavior.php');
require_once(dirname(__FILE__) . '/library/class_upfront_http_response.php');
require_once(dirname(__FILE__) . '/library/class_upfront_server.php');
require_once(dirname(__FILE__) . '/library/class_upfront_model.php');
require_once(dirname(__FILE__) . '/library/class_upfront_module_loader.php');
require_once(dirname(__FILE__) . '/library/class_upfront_theme.php');
require_once(dirname(__FILE__) . '/library/class_upfront_grid.php');
require_once(dirname(__FILE__) . '/library/class_upfront_style_preprocessor.php');
require_once(dirname(__FILE__) . '/library/class_upfront_output.php');
require_once(dirname(__FILE__) . '/library/class_upfront_endpoint.php');
require_once(dirname(__FILE__) . '/library/class_upfront_media.php');
require_once(dirname(__FILE__) . '/library/class_ufront_ufc.php');
require_once(dirname(__FILE__) . '/library/class_upfront_codec.php');
require_once(dirname(__FILE__) . '/library/class_upfront_compat.php');
require_once(dirname(__FILE__) . '/library/class_upfront_postpart.php');
require_once(dirname(__FILE__) . '/library/class_upfront_admin.php');
require_once(dirname(__FILE__) . '/library/class_upfront_compression.php');


Upfront_Behavior::debug()->set_baseline();

/**
 * Hauptklasse
 */
class Upfront {

	/**
	 * Textdomäne des Themas
	 *
	 * @var string
	 */
	const TextDomain = "upfront";

	/**
	 * Liste der Dateien, die beim Scannen ausgeschlossen werden sollen
	 *
	 * @TODO-Variablennamen und -speicherort umgestalten
	 *
	 * @var array
	 */
	public static $Excluded_Files = array(".", "..", ".DS_Store");

	/**
	 * Server, die automatisch geladen werden
	 *
	 * @var array
	 */
	private $_servers = array(
		'ajax',
		'javascript_main',
		'stylesheet_main',
		'stylesheet_editor',
		'element_styles',
	);

	/**
	 * Debugger-Instanz
	 *
	 * @var object
	 */
	private $_debugger;

	/**
	 * Klasse instanziieren – niemals an Außenstehende
	 */
	private function __construct () {
		$this->_debugger = Upfront_Debug::get_debugger();
		$servers = apply_filters('upfront-servers', $this->_servers);
		foreach ($servers as $component) $this->_run_server($component);
		Upfront_ModuleLoader::serve();
		do_action('upfront-core-initialized');
	}

	/**
	 * Öffentliche Spawning-Schnittstelle
	 */
	public static function serve () {
		$me = new self;
		$me->_add_hooks();
		$me->_add_supports();
	}

	/**
	 * Die Basis Hooks
	 */
	private function _add_hooks () {
		if (Upfront_Behavior::compression()->get_option('freeze')) {
			Upfront_DependencyCache_Server::serve();
		}

		add_filter('body_class', array($this, 'inject_grid_scope_class'));
		add_action('wp_head', array($this, "inject_global_dependencies"), 0);
		add_action('wp_footer', array($this, "inject_upfront_dependencies"), 99);
		add_action('upfront-core-wp_dependencies', array($this, "inject_core_wp_dependencies"), 99);

		add_action('admin_bar_menu', array($this, 'add_edit_menu'), 85);

		if (is_admin()) {
			require_once(dirname(__FILE__) . '/library/servers/class_upfront_admin.php');
			if (class_exists('Upfront_Server_Admin')) Upfront_Server_Admin::serve();
		}

		if ( is_rtl() ) {
			add_action('wp_head', array($this, "inject_rtl_dependencies"), 99);
		}

	}

	/**
	 * Richtet Textdomänen für übergeordnete und untergeordnete Themen ein
	 */
	public static function load_textdomain () {
		$path = untrailingslashit(self::get_root_dir()) . '/languages';

		load_theme_textdomain('upfront', $path);

		// Versuchen wir es jetzt mit dem Child-Theme ...
		$current = wp_get_theme();
		$parent = $current->parent();
		if (empty($parent)) return false; // Das aktuelle Theme ist kein Child-Theme, mache weiter ...
		if ('upfront' !== $parent->get_template()) return false; // Kein Upfront-Child-Theme, weitermachen...
		$child_domain = $current->get('TextDomain');
		if (!empty($child_domain) && 'upfront' !== $child_domain) {
			load_child_theme_textdomain($child_domain, get_stylesheet_directory() . '/languages');
		}

	}

	/**
	 * Fügt Design-Zusatzfunktionen hinzu
	 */
	private function _add_supports () {
		add_theme_support('post-thumbnails');
		add_theme_support('title-tag'); // Lasse WP mit unseren Thementiteln umgehen
		register_nav_menu('default', __('Standard', 'upfront'));
		// Erstelle Widget-Text
		$do_widget_text = apply_filters(
			'upfront-shortcode-enable_in_widgets',
			(defined('UPFRONT_DISABLE_WIDGET_TEXT_SHORTCODES') && UPFRONT_DISABLE_WIDGET_TEXT_SHORTCODES ? false : true)
		);
		if ($do_widget_text) {
			add_filter('widget_text', 'do_shortcode');
		}
	}

	/**
	 * Automatisch bootfähigen Server instanziieren
	 *
	 * @param string $comp Server zu booten
	 */
	private function _run_server ($comp) {
		$class = Upfront_Server::name_to_class($comp);
		if (!$class) return false;
		call_user_func(array($class, 'serve'));
	}

	/**
	 * Ruft die Stamm-URL des übergeordneten Designs ab
	 *
	 * @return string
	 */
	public static function get_root_url () {
		return get_template_directory_uri();
	}

	/**
	 * Ruft den Stammpfad des übergeordneten Designs ab
	 *
	 * @return string
	 */
	public static function get_root_dir () {
		return get_template_directory();
	}

	/**
	 * Menüeintrag der Admin-Toolbar einfügen
	 *
	 * @param object $wp_admin_bar Toolbar
	 */
	public function add_edit_menu ($wp_admin_bar) {
		if (!Upfront_Permissions::current(Upfront_Permissions::BOOT)) return false;

		$item = array(
			'id'    => 'upfront-edit_layout',
			'title' => '<span class="ab-icon"></span><span class="ab-label">' . __('UpFront', 'upfront') . '</span>',
			'href'  => (is_admin() ? home_url('/?editmode=true', is_ssl() ? "https" : null) : '#'),
			'meta'  => array(
				'class' => 'upfront-edit_layout upfront-editable_trigger',
			),
		);
		$permalinks_on = get_option('permalink_structure');

		if (!$permalinks_on) {
			// Wir prüfen WP priv direkt, weil wir dafür einen Admin brauchen
			if (current_user_can('manage_options')) {
				$item['href'] = admin_url('/options-permalink.php');
				unset($item['meta']);
			} else {
				$item = array(); // Nichts dergleichen für Nicht-Administratoren
			}
		}

		if (!empty($item)) {
			$wp_admin_bar->add_menu($item);
		}

		// Änder die vorhandenen Nodes
		$nodes = $wp_admin_bar->get_nodes();
		if (!empty($nodes) && is_array($nodes)) {
			foreach ($nodes as $node) {
				if (!empty($node->href) && preg_match('/customize\.php/', $node->href)) {
					$node->href = !empty($node->id) && 'customize-themes' === $node->id // ClassicPress verdoppelt auch den Customizer-Endpunkt für die Themenliste ...
						? admin_url('themes.php') // ... wird nicht passieren
						: home_url('?editmode=true')
					;
				}
				$wp_admin_bar->add_node($node);
			}
		}

		// Action Hook hier, damit andere Dinge ihre Bar-Elemente hinzufügen können
		// (vor allem der Exporteur)
		do_action('upfront-admin_bar-process', $wp_admin_bar, $item);
	}

	/**
	 * Hängt die Grid-Scope-Klasse an das Klassen-Array an
	 *
	 * @param array $cls Klassen bis hierher
	 *
	 * @return array
	 */
	function inject_grid_scope_class ($cls) {
		$grid = Upfront_Grid::get_grid();
		$cls[] = $grid->get_grid_scope();
		return $cls;
	}

	/**
	 * Verarbeitet die Kern-WP-Front-End-Abhängigkeitsinjektion
	 */
	public function inject_core_wp_dependencies () {
		$deps = Upfront_CoreDependencies_Registry::get_instance();

		if (Upfront_Behavior::compression()->has_experiments()) {
			if (defined('DOING_AJAX') && DOING_AJAX) {
				$deps->add_wp_script('jquery-ui-core');
				$deps->add_wp_script('jquery-ui-widget');
				$deps->add_wp_script('jquery-ui-mouse');
				$deps->add_wp_script('jquery-effects-core');
				$deps->add_wp_script('jquery-effects-slide');
				$deps->add_wp_script('jquery-ui-draggable');
				$deps->add_wp_script('jquery-ui-droppable');
				$deps->add_wp_script('jquery-ui-resizable');
				$deps->add_wp_script('jquery-ui-selectable');
				$deps->add_wp_script('jquery-ui-sortable');
				$deps->add_wp_script('jquery-ui-slider');
				$deps->add_wp_script('jquery-ui-datepicker');
			} else {
				$deps->add_script(admin_url('admin-ajax.php?action=wp_scripts'));
			}
		} else {
			// Non-experiments load
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-effects-core');
			wp_enqueue_script('jquery-effects-slide');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-droppable');
			wp_enqueue_script('jquery-ui-resizable');
			wp_enqueue_script('jquery-ui-selectable');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-slider');
			wp_enqueue_script('jquery-ui-datepicker');
		}

		/**
		 * Todo: Macht es sauberer
		 */
		wp_enqueue_script("wp_shortcode", "/wp-includes/js/shortcode.js", array("underscore"));
	}

	/**
	 * Behandelt die globale FE-Abhängigkeitsinjektion
	 */
	function inject_global_dependencies () {
		$deps = Upfront_CoreDependencies_Registry::get_instance();
		wp_enqueue_script('jquery');

		// Grundlegende Stile für die Arbeit im Voraus werden immer geladen.
		$global_style = Upfront_Behavior::compression()->has_experiments()
			? '/styles/global.min.css'
			: '/styles/global.css'
		;
		//wp_enqueue_style('upfront-global', self::get_root_url() . $global_style, array(), Upfront_ChildTheme::get_version());
		$deps->add_header_style(self::get_root_url() . $global_style);

		if (!Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			// Stelle das Front-Grid nicht in die Warteschlange, wenn die Berechtigung zum Booten von Upfront haben, sondern Warteschlangen-Editor-Grid
			//wp_enqueue_style('upfront-front-grid', admin_url('admin-ajax.php?action=upfront_load_grid'), array(), Upfront_ChildTheme::get_version());
			$deps->add_header_style(admin_url('admin-ajax.php?action=upfront_load_grid'));
		}

		if (Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			do_action('upfront-core-wp_dependencies');

			//wp_enqueue_style('upfront-editor-interface', self::get_root_url() . ( $this->_debugger->is_dev()  ?  '/styles/editor-interface.css' : '/styles/editor-interface.min.css' ) , array(), Upfront_ChildTheme::get_version());
			$deps->add_header_style(self::get_root_url() . ( $this->_debugger->is_dev()  ?  '/styles/editor-interface.css' : '/styles/editor-interface.min.css' ));

			$link_urls = array(
				admin_url('admin-ajax.php?action=upfront_load_editor_grid'),
				self::get_root_url() . '/scripts/chosen/chosen.min.css',
				self::get_root_url() . '/styles/font-icons.css',
			);
			foreach ($link_urls as $url) {
				$deps->add_style($url);
			}
			$deps->add_font('Open Sans', array(
				'300',
				'400',
				'600',
				'700',
				'400italic',
				'600italic',
			));

			add_action('wp_footer', array($this, 'add_responsive_css'));
		}
	}

	/**
	 * Behandelt die Editor-spezifische FE-Abhängigkeitsinjektion von Upfront
	 */
	function inject_upfront_dependencies () {

		if (!Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			do_action('upfront-core-inject_dependencies'); // Löst auch den Hook für die Abhängigkeitsinjektion aus
			return false; // Injiziere nicht für Benutzer, die dies nicht verwenden können
		}

		$url = self::get_root_url();

		// Boot-Bearbeitungsmodus, wenn die Abfragezeichenfolge den Parameter editmode enthält
		if (isset($_GET['editmode'])) echo upfront_boot_editor_trigger();

		$storage_key = apply_filters('upfront-data-storage-key', Upfront_Layout::STORAGE_KEY);
		$save_storage_key = $storage_key;
		$is_ssl = is_ssl() ? '&ssl=1' : '';

		if (Upfront_Behavior::debug()->is_dev() && current_user_can('switch_themes') && apply_filters('upfront-enable-dev-saving', true)) {
			$save_storage_key .= '_dev';
		}

		$is_dev = $this->_debugger->is_dev();
		$upfront = $theme = wp_get_theme('upfront');
		$main_source = $is_dev ? "scripts/setup.js" : "build/main-$upfront->version.js";
		$script_urls = array();

		// Wir brauchen nur require.js auf dev, für build wird es jetzt in main.js gebacken
		if ($is_dev) {
			$script_urls[] = "{$url}/scripts/require.js";
		}
		$script_urls[] = admin_url('admin-ajax.php?action=upfront_load_main&ufver=' . $upfront->version . $is_ssl);
		$script_urls[] = "{$url}/{$main_source}";

		$deps = Upfront_CoreDependencies_Registry::get_instance();
		foreach ($script_urls as $url) {
			$deps->add_script($url);
		}

		$layout_post_data = json_encode(array(
			'layout' => Upfront_EntityResolver::get_entity_ids(),
			'post_id' => (is_singular() ? apply_filters('upfront-data-post_id', get_the_ID()) : false),
		));
		echo '<script type="text/javascript">
			var _upfront_post_data=' . $layout_post_data . ';
			var _upfront_storage_key = "' . esc_js($storage_key) . '";
			var _upfront_save_storage_key = "' . esc_js($save_storage_key) . '";
			var _upfront_stylesheet = "' . esc_js(get_stylesheet()) . '";
			var _upfront_debug_mode = ' . (int)Upfront_Behavior::debug()->is_debug() . ';
			var _upfront_please_hold_on = ' . json_encode(__('Bitte halte noch ein bisschen durch', 'upfront')) . ';
		</script>';
		echo <<<EOAdditivemarkup
	<div id="sidebar-ui" class="upfront-ui"></div>
	<div id="settings" style="display:none"></div>
	<div id="contextmenu" style="display:none"></div>
EOAdditivemarkup;

		do_action('upfront-core-inject_dependencies');
	}

	/**
	 * Fügt responsives CSS ein
	 */
	function add_responsive_css () {
		include(self::get_root_dir() . '/styles/editor-interface-responsive.html');
	}

	/**
	 * Fügt Abhängigkeiten für RTL-Sprachen ein
	 */
	function inject_rtl_dependencies () {

		wp_enqueue_style('upfront-global-rtl', self::get_root_url() . ( $this->_debugger->is_dev() ? "/styles/global-rtl.css" : "/styles/global-rtl.min.css" ), array(), Upfront_ChildTheme::get_version());
	}
}
add_action('init', array('Upfront', 'serve'), 0);
add_action('after_setup_theme', array('Upfront', "load_textdomain"));

/**
 * Filtert wp caption atts, um die Beschriftung auszublenden, falls show_caption gleich "0" ist
 */
add_filter("shortcode_atts_caption", 'uf_shortcode_atts_caption', 10, 3);

/**
 * Filtert wp captions atts, um die Beschriftung zu entfernen, falls show_caption gleich "0" ist
 *
 * @param array $out Das Ausgabearray von Shortcode-Attributen.
 * @param array $pairs Die unterstützten Attribute und ihre Standardwerte.
 * @param array $atts Die benutzerdefinierten Shortcode-Attribute.
 * @return mixed
 */
function uf_shortcode_atts_caption ($out, $pairs, $atts) {

	if (isset( $atts['show_caption'] ) && "0" == $atts['show_caption']) {
		$out['caption'] = "&nbsp;";
	}

	return $out;
}


/**
 * Filtert Bildunterschrift-Shortcode, um uf-Unterschrift-spezifisches Markup zu generieren
 */
add_filter("img_caption_shortcode", "uf_image_caption_shortcode", 10, 3);
/**
 * Uses img_caption_shortcode to add support for UF image variants
 *
 * @param string $out Out
 * @param array $attr Dem Shortcode zugeordnete Attribute.
 * @param string $content Shortcode Content.
 *
 * @return string|void
 */
function uf_image_caption_shortcode ($out, $attr, $content) {

	$is_wp_cation = strpos($attr["id"], "uinsert-" ) === false;

	if ($is_wp_cation) return; // Wenn wir null zurückgeben, lasse uns wp seine eigene Logik und das Rendering für den Untertitel-Shortcode ausführen

	$image_reg = preg_match('/src="([^"]+)"/', $content, $image_arr);
	$href_reg = preg_match('/href="([^"]+)"/', $content, $anchor_arr);

	$data = (object) shortcode_atts( array(
		'id'	  => '',
		'caption' => '',
		'class'   => '',
		'uf_variant' => '',
		'uf_isLocal' => true,
		'uf_show_caption' => true,
		'image' => $image_reg ? $image_arr[1] : "",
		'linkUrl' => $href_reg ? $anchor_arr[1] : "",

	), $attr, 'caption' );

	 return Upfront_Post_Data_PartView::get_post_image_markup($data);

}

/**
 * Lädt Iconfont in Admin, um Symbolleistensymbol anzuzeigen.
 */
function uf_admin_bar_styles() {
	wp_enqueue_style( 'uf-font-icons', get_template_directory_uri() . '/styles/font-icons.css');
}
add_action( 'admin_enqueue_scripts', 'uf_admin_bar_styles' );

/**
 * Beseitigt den Admin-Hinweis und erklärt Unterstützung für Woo
 */
function uf_add_woocommerce_support() {
	add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'uf_add_woocommerce_support');

/**
 * TODO: Abhängigkeiten von Terminmanager
 * Wenn PS-Terminmanager aktiv
 * Abhängigkeiten laden auch im Child
 */
/*function upfront_force_load_dependencies () {
	globale $appointments;
	$appointments->load_scripts_styles();
}
add_action('wp_footer', 'upfront_force_load_dependencies', 1);*/
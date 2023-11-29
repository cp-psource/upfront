<?php

class Upfront_LoginView extends Upfront_Object {

	public static function default_properties () {
		return array(
			'preset' => 'default',
			'style' => 'form',
			'behavior' => 'click',
			'appearance' => 'icon',
			'label_image' => self::_get_l10n('login'),
			'login_button_label' => self::_get_l10n('log_in'),
			'logout_link' => self::_get_l10n('log_out'),
			'trigger_text' => self::_get_l10n('log_in'),
			'logged_in_preview' => '',
			'type' => "LoginModel",
			'view_class' => "LoginView",
			"class" => "c24 upfront-login_element-object",
			'has_settings' => 1,
			'id_slug' => 'upfront-login_element',
			'logout_style' => 'link',
			'top_offset' => 0,
			'left_offset' => 0,
			'username_label' => self::_get_l10n('username_label'),
			'password_label' => self::_get_l10n('password_label'),
			'remember_label' => self::_get_l10n('remember_label'),
			'lost_password_text' => self::_get_l10n('lost_password'),
			'lost_password_link' => self::_get_l10n('click_here'),
		);
	}

	public function get_markup () {

		// We're registering the styles as it turns out we'll need them
		upfront_add_element_style('upfront_login', array('css/public.css', dirname(__FILE__)));
		upfront_add_element_script('upfront_login', array('js/public.js', dirname(__FILE__)));
		// They'll get concatenated and cached later on, we're done with this. Get the actual markup.

		$properties = !empty($this->_data['properties']) ? $this->_data['properties'] : array();

		return is_user_logged_in ()
			? self::get_logout_markup(self::_normalize_properties($properties))
			: self::get_login_markup($properties)
		;
	}

	public static function get_logout_markup ($properties=array()) {
		if (!(!empty($properties['logout_style']) && 'link' === $properties['logout_style'])) return ' ';

		$label = !empty($properties['logout_link'])
			? $properties['logout_link']
			: self::_get_l10n('log_out')
		;
		return upfront_get_template("login-logout", array('label' => $label), dirname(dirname(__FILE__)) . "/tpl/logout.php");
	}

	public static function get_login_markup ($properties=array()) {
		$properties = self::_normalize_properties($properties);

		$logged_in_preview = is_array($properties['logged_in_preview'])
			? !empty($properties['logged_in_preview'][0])
			: !empty($properties['logged_in_preview'])
		;

		if ($logged_in_preview) {
			return self::get_logout_markup($properties);
		}

		$block = !empty($properties['style']) && 'form' == $properties['style'];
		$login_button_label = !empty($properties['login_button_label'])
			? $properties['login_button_label']
			: self::_get_l10n('log_in')
		;
		$trigger_label = !empty($properties['trigger_text']) ? $properties['trigger_text'] : $login_button_label;
		$trigger = empty($block)
			? self::_get_trigger_markup('', $trigger_label)
			: ''
		;
		
		$username_label = !empty($properties['username_label'])
			? $properties['username_label']
			: self::_get_l10n('username_label')
		;
		$password_label = !empty($properties['password_label'])
			? $properties['password_label']
			: self::_get_l10n('password_label')
		;
		$remember_label = !empty($properties['remember_label'])
			? $properties['remember_label']
			: self::_get_l10n('remember_label')
		;
		$lost_password_text = !empty($properties['lost_password_text'])
			? $properties['lost_password_text']
			: self::_get_l10n('lost_password')
		;
		$lost_password_link = !empty($properties['lost_password_link'])
			? $properties['lost_password_link']
			: self::_get_l10n('click_here')
		;
		

		$allow_registration = !is_user_logged_in() && get_option('users_can_register');
		// Allow override for in-editor form previews
		if (defined('DOING_AJAX') && DOING_AJAX && Upfront_Permissions::current(Upfront_Permissions::BOOT)) {
			$allow_registration = get_option('users_can_register');
		}

		$data = array(
			'trigger' => $trigger,
			'login_button_label' => $login_button_label,
			'allow_registration' => $allow_registration,
			'username_label' => $username_label,
			'password_label' => $password_label,
			'remember_label' => $remember_label,
			'lost_password' => $lost_password_text,
			'lost_password_link' => $lost_password_link,
			'register' => self::_get_l10n('register'),
		);
		
		// top and left offset for hover and click behavior
		$top_offset = ( !empty($properties['top_offset']) )
			? 'top:' . $properties['top_offset'] . 'px;'
			: ''
		;
		$left_offset = ( !empty($properties['left_offset']) )
			? 'left:' . $properties['left_offset'] . 'px;'
			: ''
		;
		$data['offset'] = $top_offset . $left_offset;
		
		$tpl = 'block'; // default
		if (!$block && !empty($properties['behavior'])) {
			$tpl = preg_replace('/[^a-z0-9]/', '', $properties['behavior']);
		}
		return upfront_get_template("login-form-{$tpl}", $data, dirname(dirname(__FILE__)) . "/tpl/form-{$tpl}.php");
	}

	private static function _get_trigger_markup ($icon=false, $trigger_label='') {
		$tpl = !empty($icon) ? 'icon' : 'link';
		return upfront_get_template("login-trigger-{$tpl}", array('label' => $trigger_label), dirname(dirname(__FILE__)) . "/tpl/trigger-{$tpl}.php");
	}

	private static function _normalize_properties ($raw_properties) {
		$to_map = array('style', 'behavior', 'appearance', 'login_button_label', 'trigger_text', 'logged_in_preview', 'logout_style', 'logout_link', 'label_image', 'top_offset', 'left_offset', 'username_label', 'password_label', 'remember_label', 'lost_password_text', 'lost_password_link');
		$properties = upfront_properties_to_array($raw_properties, $to_map);
		return $properties;
	}

	public static function add_data_defaults ($data) {
		$data['upfront_login'] = array(
			"defaults" => self::default_properties(),
			"root_url" => trailingslashit(upfront_element_url('/', dirname(__FILE__)))
		);
		return $data;
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['login_element'])) return $strings;
		$strings['login_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Login', 'upfront'),
			'click_here' => __('Klicke hier zum zurückzusetzen', 'upfront'),
			'css' => array(
				'form_wrapper' => __('Formular-Wrapper', 'upfront'),
				'form_wrapper_info' => __('Container-Wrapper für das Anmeldeformular.', 'upfront'),
				'form_wrapper_triggered' => __('Form Wrapper ausgelöst', 'upfront'),
				'form_wrapper_triggered_info' => __('Container-Wrapper für das getriggerte Login-Formular per Klick oder Hover.', 'upfront'),
				'containers' => __('Feldcontainer', 'upfront'),
				'containers_info' => __('Wrapper-Layer für jedes Feld', 'upfront'),
				'labels' => __('Feldbeschriftungen', 'upfront'),
				'labels_info' => __('Beschriftungen für die Eingabefelder', 'upfront'),
				'inputs' => __('Eingabefelder', 'upfront'),
				'inputs_info' => __('Felder für Benutzername und Passwort', 'upfront'),
				'button' => __('Schaltfläche', 'upfront'),
				'button_info' => __('Login-Schaltfläche', 'upfront'),
				'remember' => __('Kontrollkästchen Angemeldet bleiben', 'upfront'),
				'remember_info' => __('Angemeldet bleiben Checkbox-Eingabe.', 'upfront'),
				'pwd_wrap' => __('Passwort verloren-Wrapper', 'upfront'),
				'pwd_wrap_info' => __('Container-Wrapper für die Funktion „Passwort verloren“..', 'upfront'),
				'pwd_link' => __('Passwort-Link verloren', 'upfront'),
				'pwd_link_info' => __('Link für verlorene Passwörter', 'upfront'),
				'login_trigger' => __('Login-Trigger', 'upfront'),
				'login_trigger_info' => __('Der Link, der das Öffnen des Logins ermöglicht, wenn die Dropdown- oder Lightbox-Option ausgewählt ist.', 'upfront'),
			),
			'hold_on' => __('Bitte warte einen Moment', 'upfront'),
			'settings' => __("Login-Element", 'upfront'),
			'general_settings' => __('Allgemeine Einstellungen', 'upfront'),
			'display' => __("Anzeige", 'upfront'),
			'show_form_label' => __("Formular anzeigen", 'upfront'),
			'show_on_hover' => __("Bei Hover", 'upfront'),
			'show_on_click' => __("Bei Klicken", 'upfront'),
			'behavior' => __("Anzeigeverhalten", 'upfront'),
			'general_settings_description' => __("Um Beschriftungen, Schaltflächen oder Linktext zu bearbeiten, doppelklicke darauf, wie Du es bei einem Textelement tun würdest", 'upfront'),
			'on_page' => __("In einem Layout", 'upfront'),
			'dropdown' => __("In einem Dropdown", 'upfront'),
			'in_lightbox' => __("Formular in Lightbox", 'upfront'),
			'appearance' => __("Anmeldeformular anzeigen", 'upfront'),
			'trigger' => __("Trigger", 'upfront'),
			'username_label' => __("Nutzername", 'upfront'),
			'password_label' => __("Passwort", 'upfront'),
			'remember_label' => __("Angemeldet bleiben", 'upfront'),
			'lost_password' => __("Passwort verloren?", 'upfront'),
			'login' => __("Login", 'upfront'),
			'log_in' => __("Einloggen", 'upfront'),
			'log_out' => __("Ausloggen", 'upfront'),
			'logged_in_preview' => __("Angemeldete Benutzer sehen", 'upfront'),
			'preview' => __("Vorschau", 'upfront'),
			'nothing' => __("Nichts", 'upfront'),
			'log_out_link' => __("Link zum Abmelden", 'upfront'),
			'log_out_label' => __("Abmeldelink:", 'upfront'),
			'log_in_button' => __("Login-Button:", 'upfront'),
			'log_in_trigger' => __("Login-Trigger:", 'upfront'),
			'register' => __("Registrieren", 'upfront'),
			'top_offset' => __("Oberer Versatz", 'upfront'),
			'left_offset' => __("Linker Versatz", 'upfront'),
			'px' => __("px", 'upfront'),
			'preset' => array(
				'part_to_style' => __("Element zum Stylen:", 'upfront'),
				'form_wrapper' => __("Formular-Wrapper", 'upfront'),
				'field_labels' => __("Feldbeschriftungen", 'upfront'),
				'input_fields' => __("Eingabefelder", 'upfront'),
				'button' => __("Schaltfläche", 'upfront'),
				'lost_password' => __("Passwort verloren-Text", 'upfront'),
				'login_trigger' => __("Login-Trigger", 'upfront'),
				'logout_link' => __("Logout-Link", 'upfront'),
				'wrapper_background' => __("Wrapper-Hintergrund", 'upfront'),
				'field_background' => __("Feldhintergrund", 'upfront'),
				'button_background' => __("Hintergrund", 'upfront'),
				'link_color' => __("Linkfarbe", 'upfront'),
			)
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}
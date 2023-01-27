<?php
/**
 * Contact form element for Upfront.
 */
class Upfront_UcontactView extends Upfront_Object {
	var $error = false;
	var $defaults = array();

	public function get_markup () {

		$element_id = $this->_get_property('element_id');

		$template = self::_get_l10n('template');
		if (is_array($template)) $template = array_map('esc_attr', $template);

		//Check if the form has been sent
		//$this->check_form_received(); // Do NOT just send out form data.
		$args = array_merge($this->properties_to_array(), array(
			'field_classes' => $this->get_field_classes(),
			'validate' => $this->_get_property('form_validate_when') == 'field' ? 'ucontact-validate-field' : '',
			'message' => isset($this->msg) ? $this->msg : false,
			'message_class' => isset($this->msg_class) ? $this->msg_class : 'error',
			'entity_id' => $this->get_entity_ids_value(),
			'form_button_text' => $this->_get_property_t('form_button_text'),
			'placeholders' => array(
				'name' => $this->get_placeholder($this->_get_property_t('form_name_label')),
				'email' => $this->get_placeholder($this->_get_property_t('form_email_label')),
				'subject' => $this->get_placeholder($this->_get_property_t('form_subject_label')),
				'captcha' => $this->get_placeholder($this->_get_property_t('form_captcha_label')),
				'message' => $this->get_placeholder($this->_get_property_t('form_message_label'))
			),
			'values' => array(
				'name' => esc_attr($this->get_post('sendername')),
				'email' => esc_attr($this->get_post('senderemail')),
				'subject' => esc_attr($this->get_post('subject')),
				'message' => esc_textarea($this->get_post('sendermessage')),
			),
			'ids' => array(
				'name' => esc_attr("name-{$element_id}"),
				'email' => esc_attr("email-{$element_id}"),
				'subject' => esc_attr("subject-{$element_id}"),
				'message' => esc_attr("message-{$element_id}"),
				'captcha' => esc_attr("captcha-{$element_id}"),
			),
			'l10n' => $template,
		));

		if (!isset($args['preset'])) {
			$args['preset'] = 'default';
		}

		$args['show_subject'] = $args['show_subject'] && sizeof($args['show_subject']);
		$args['show_captcha'] = $args['show_captcha'] && sizeof($args['show_captcha']);
		$args['form_add_title'] = $args['form_add_title'] && sizeof($args['form_add_title']);

		$markup =  $this->get_template('ucontact', $args);

		return $markup . '
			<script type="text/javascript">
				if(! window.ajax_url)
					ajax_url = "' . admin_url( 'admin-ajax.php' ) . '";
			</script>';
	}

	private function properties_to_array (){
		$out = array();
		foreach ($this->_data['properties'] as $prop) {
			$out[$prop['name']] = $prop['value'];
		}
		return $out;
	}

	public function add_js_defaults ($data){
		$data['ucontact'] = array(
			'defaults' => self::default_properties(),
			'template' => upfront_get_template_url('ucontact', upfront_element_url('templates/ucontact.html', dirname(__FILE__))),
		);
		return $data;
	}

	public static function default_properties () {
		return array(
			'form_add_title' => array(),
			'form_title' => self::_get_l10n('contact_form'),
			'form_name_label' => self::_get_l10n('name_label'),
			'form_email_label' => self::_get_l10n('email_label'),
			'form_email_to' => Upfront_Cache_Utils::get_option('admin_email'),
			'show_subject' => array(),
			'show_captcha' => array(),
			'form_subject_label' => self::_get_l10n('subject_label'),
			'form_captcha_label' => self::_get_l10n('captcha_label'),
			'form_default_subject' => self::_get_l10n('default_subject'),
			'form_message_label' => self::_get_l10n('message_label'),
			'form_button_text' => self::_get_l10n('button_text'),
			'form_validate_when' => 'submit',
			'form_label_position' => 'above',
			'preset' => 'default',
			'type' => "UcontactModel",
			'view_class' => "UcontactView",
			"class" => "c24 upfront-contact-form",
			'has_settings' => 1,
			'id_slug' => 'ucontact',
		);
	}

	public static function add_styles_scripts () {
		// CAPTCHA
		upfront_add_element_style('jquery-realperson', array('/scripts/realperson/jquery.realperson.css', false));
		upfront_add_element_script('jquery-plugin', array('/scripts/realperson/jquery.plugin.js', false));
		upfront_add_element_script('jquery-realperson', array('/scripts/realperson/jquery.realperson.js', false));

		upfront_add_element_style('ucontact-style', array('css/ucontact.css', dirname(__FILE__)));
		upfront_add_element_script('ucontact-front', array('js/ucontact-front.js', dirname(__FILE__)));

		if (is_user_logged_in()) {
			upfront_add_element_style('ucontact-style-editor', array('css/ucontact-editor.css', dirname(__FILE__)));
		}
	}

	public static function ajax_send () {
		$settings  = self::get_settings_from_ajax();
		$unknown_form_error = array(
			'error' => true,
			'message' => self::_get_l10n('unknown_form'),
		);

		if (!$settings) return $unknown_form_error;

		$form = new Upfront_UcontactView($settings);

		if (!$form) return $unknown_form_error;

		$form->check_form_received();

		return array(
			'error'=> (bool)(!empty($form->msg_class) && 'error' === $form->msg_class),
			'message' => $form->msg
		);
	}

	public static function store () {
		if (!$_POST['data'] || !$_POST['data']['properties']) {
			return array(
				'error' => true,
				'message' => self::_get_l10n('invalid_data'),
			);
		}

		$contact_form = array();
		$data = $_POST['data']['properties'];
		foreach ($data as $prop) {
			$contact_form[$prop['name']] = $prop['value'];
		}

		if (!$contact_form['element_id']) {
			return array(
				'error' => true,
				'message' => self::_get_l10n('unknown_form'),
			);
		}

		if (Upfront_Cache_Utils::update_option($contact_form['element_id'], $_POST['data'])) {
			return array(
				'error' => false,
				'message' => self::_get_l10n('settings_stored'),
			);
		}

		return array(
			'error' => true,
			'message' => self::_get_l10n('store_error'),
		);
	}

	private static function json_response ($data) {
		header("Content-type: application/json; charset=utf-8");
		if ($data instanceof Upfront_HttpResponse) {
			die($data->get_output());
		}
		die(json_encode($data));
	}

	private function check_form_received () {
		
		if (isset($_POST['ucontact']) && $_POST['ucontact'] == 'sent' && $_POST['contactformid'] == $this->_get_property('element_id')) {
			//Get all the needed fields and sanitize them
			$_POST = stripslashes_deep( $_POST );
			$name = preg_replace('/\n\r/', ' ', sanitize_text_field($_POST['sendername']));
			$email = is_email($_POST['senderemail']);
			$show_subject = $this->_get_property('show_subject');
			$show_captcha = $this->_get_property('show_captcha');

			if ($show_subject && $show_subject != 'false') {
				$subject = sanitize_text_field($_POST['subject']);
			} else {
				$show_subject = false;
				$subject = $this->_get_property('form_default_subject');
			}

			if ($show_captcha && $show_captcha != 'false') {
				$real_person = $_POST['realPerson'];
				$real_person_hash = $_POST['realPersonHash'];

				if (!$real_person || !$real_person_hash || upfront_realperson_hash($real_person) != $real_person_hash) {
					$this->msg = self::_get_l10n('error_captcha');
					$this->msg_class = 'error';
					return;
				}
			}

			//Strip unwanted tags from the message
			$message = wp_kses(
				$_POST['sendermessage'],
				array(
					'a' => array(
						'href' => array (),
						'title' => array ()),
					'abbr' => array(
						'title' => array ()),
					'acronym' => array(
						'title' => array ()),
					'b' => array(),
					'blockquote' => array(
						'cite' => array ()),
					'cite' => array (),
					'code' => array(),
					'del' => array(
						'datetime' => array ()),
					'em' => array (), 'i' => array (),
					'q' => array(
						'cite' => array ()),
					'strike' => array(),
					'strong' => array(),
				)
			);

			// Add name and email to email body
			$name_label = self::_get_l10n('name_label');
			$email_label = self::_get_l10n('email_label');
			$message = $message . "\n\n{$name_label} {$name} \n{$email_label} {$email}";

			$emailto = trim($this->_get_property('form_email_to'));
			if (empty($emailto)) $emailto = Upfront_Cache_Utils::get_option('admin_email');
			if (!is_email($emailto)) $emailto = false;

			$headers[] = 'From: ' . $name . ' <' . $email . ">\r\n";
			$this->msg = $this->check_fields($name, $email, $subject, $message);
			
			if ($this->msg) {
				$this->msg_class = 'error';
			} else if (!empty($emailto)) {
				
				// Let's first force mail callbacks
				/*if (!empty($name)) {
					$email_callback = create_function('$email', "return '{$email}';");
					$name_callback = create_function('$name', "return '{$name}';");
					add_filter('wp_mail_from', $email_callback, 99);
					add_filter('wp_mail_from_name', $name_callback, 99);
				}*/
				if (!empty($name)) {
					// Create a closure using anonymous function
					$email_callback = function($email) use ($name) {
						return $name;
					};
				
					$name_callback = function($name_param) use ($name) {
						return $name;
					};
					add_filter('wp_mail_from', $email_callback, 99);
					add_filter('wp_mail_from_name', $name_callback, 99);
				}

				// Do the SMTP magic here
				$smtp_enable = $this->_get_property_t('smtp_enable') == 'yes' ? true : false;

				if($smtp_enable) {
					add_action('phpmailer_init', array($this, 'kick_in_smtp'));					
				}

				// ... then send email
				if (!wp_mail($emailto, $subject, $message, $headers)) {
					$this->msg = self::_get_l10n('error_sending');
					$this->msg_class = 'error';
				} else $this->msg = self::_get_l10n('mail_sent');

				// ... and clean up afterwards
				if (!empty($name)) {
					remove_filter('wp_mail_from_name', $name_callback, 99);
					remove_filter('wp_mail_from', $email_callback, 99);
				}

			} else {
				$this->msg = self::_get_l10n('mail_sent');
			}
		}
	}

	public function kick_in_smtp($phpmailer){
		//Get all the needed fields and sanitize them
		$_POST = stripslashes_deep( $_POST );
		$name = preg_replace('/\n\r/', ' ', sanitize_text_field($_POST['sendername']));
		$email = is_email($_POST['senderemail']);
		
		$smtp_from_email = $this->_get_property_t('smtp_from_email');
		$smtp_host = $this->_get_property_t('smtp_host');
		if( !is_email($smtp_from_email) || empty($smtp_host) ){
			return;
		}

		$phpmailer->isSMTP();   
		$phpmailer->Mailer = "smtp";
		$phpmailer->SetFrom($email, $name);
		$phpmailer->AddReplyTo($email, $name); //Reply-To
		$phpmailer->Host = $this->_get_property_t('smtp_host');
		$phpmailer->SMTPSecure = $this->_get_property_t('smtp_secure');
		$phpmailer->Port = $this->_get_property_t('smtp_port');
		$smtp_auth = $this->_get_property_t('smtp_authentication');
		$phpmailer->SMTPAuth = ( isset( $smtp_auth[0] ) && $smtp_auth[0] == "yes" ) ? TRUE : FALSE;

		// Strip unwanted tags from the message
		$message = wp_kses(
			$_POST['sendermessage'],
			array(
				'a' => array(
					'href' => array (),
					'title' => array ()),
				'abbr' => array(
					'title' => array ()),
				'acronym' => array(
					'title' => array ()),
				'b' => array(),
				'blockquote' => array(
					'cite' => array ()),
				'cite' => array (),
				'code' => array(),
				'del' => array(
					'datetime' => array ()),
				'em' => array (), 'i' => array (),
				'q' => array(
					'cite' => array ()),
				'strike' => array(),
				'strong' => array(),
			)
		);
		
		// Add name and email to email body
		$name_label = self::_get_l10n('name_label');
		$email_label = self::_get_l10n('email_label');
		$body = $message . "\n\n{$name_label} {$name} \n{$email_label} {$email}";
		$phpmailer->Body = $body;

		if( isset( $smtp_auth[0] ) && $smtp_auth[0] == "yes" ) {
			$phpmailer->Username = $this->_get_property_t('smtp_username');
			$phpmailer->Password = $this->_get_property_t('smtp_password');
		}
	}

	public function get_post ($param){
		return isset($_POST[$param]) ? $_POST[$param] : '';
	}

	/**
	 * Validation check for the contact form fields.
	 * @param  String $name    Name sent by the user
	 * @param  String $email   Email sent by the user
	 * @param  String $subject Subject for the email
	 * @param  String $message Message sent by the user
	 * @return String|Boolean          A message with the error or false if no errors.
	 */
	private function check_fields ($name, $email, $subject, $message){
		if (empty($name)) return self::_get_l10n('missing_name');
		if (empty($email)) return self::_get_l10n('invalid_email');
		if ($this->_get_property('show_subject') && empty($subject)) return self::_get_l10n('missing_subject');
		if (empty($message)) return self::_get_l10n('missing_message');
		return false;
	}

	/**
	 * Parses the PHP file output to a variable
	 */
	private function get_template ($templatename, $args = array()){
		return upfront_get_template('ucontact', $args, dirname(dirname(__FILE__)) . '/templates/ucontact.html');
	}

	public function get_entity_ids_value () {
		$entities = array();
		$entities = Upfront_Layout::get_parsed_cascade();

		if (empty($entities)) {
			$entities = Upfront_EntityResolver::get_entity_ids();
		}

		$entities['storage_key'] = Upfront_Model::get_storage_key();
		return base64_encode(json_encode($entities));
	}

	private static function get_settings_from_ajax (){
		$entity_ids = array();
		try {
			$entity_ids = (array)json_decode(base64_decode($_POST['entity_ids']));
		} catch (Exception $e) {
			return false;
		}

		$storage_key = false;
		if (isset($entity_ids['storage_key'])) {
			$storage_key = $entity_ids['storage_key'];
			unset($entity_ids['storage_key']);
		}

		if( isset( $entity_ids['item'] )  && 'single-page' ===  $entity_ids['item'] ){
			$is_dev = Upfront_Debug::get_debugger()->is_dev();

			/**
			 * If $is_dev is false, then try to see if dev=true exists in the page url
			 */
			if( !$is_dev ){
				$parsed_ref = parse_url( wp_get_raw_referer() );
				$is_dev = isset( $parsed_ref['query'] ) && false !== strpos(  $parsed_ref['query'], "dev=true" ) ;
			}

			$store_key = str_replace('_dev','',Upfront_Layout::get_storage_key());

			// loading from Page Layout CPT
			$layout_cpt_slug = ( isset($entity_ids['specificity']) )
					? strtolower($store_key . '-' . $entity_ids['specificity'])
					: strtolower($store_key . '-' . $entity_ids['item'])
			;

			$layout = Upfront_Server_PageLayout::get_instance()->get_layout_by_slug( $layout_cpt_slug, $is_dev );

			if( empty($layout) )
				return new Exception("Unknown layout for slug: $layout_cpt_slug and is_dev: $is_dev");

		}else{
			$layout = Upfront_Layout::from_entity_ids($entity_ids, $storage_key);

			if ($layout instanceof Upfront_Layout) {
				$layout = $layout->to_php();
			} else {
				return false;
			}
		}

		if (is_array($layout['regions'])) {
			foreach ($layout['regions'] as $region) {
				if (sizeof($region['modules'])) {
					foreach ($region['modules'] as $module) {
						if (isset($module['objects']) && sizeof($module['objects'])) {
							foreach ($module['objects'] as $object) {
								if (sizeof($object['properties'])) {
									foreach ($object['properties'] as $prop) {
										if ($prop['name'] == 'element_id' && $prop['value'] == $_POST['contactformid']) return $object;
									}
								}
							}
						}
					}
				}
			}
		}
		return false;
	}

	private function get_field_classes () {
		return 'ucontact-label-' . $this->_get_property('form_label_position');
	}

	public function get_placeholder ($label) {
		if ('over' === $this->_get_property('form_label_position')) {
			return 'placeholder="' . esc_attr(preg_replace('/<span[^<]+<\/span>/i', '', $label)) . '"';
		}
		return '';
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['contact_element'])) return $strings;
		$strings['contact_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Kontakt', 'upfront'),
			'contact_form' => __('Kontakt Formular', 'upfront'),
			'contact_details' => __('Kontaktdetails', 'upfront'),
			'name_label' => __('Name:', 'upfront'),
			'email_label' => __('Email:', 'upfront'),
			'subject_label' => __('Betreff:', 'upfront'),
			'captcha_label' => __('CAPTCHA:', 'upfront'),
			'default_subject' => __('Gesendet von der Webseite', 'upfront'),
			'message_label' => __('Nachricht:', 'upfront'),
			'button_text' => __('Senden', 'upfront'),
			'unknown_form' => __('Unbekanntes Kontaktformular.', 'upfront'),
			'invalid_data' => __('Die Kontaktformulardaten sind ungültig.', 'upfront'),
			'settings_stored' => __('Kontaktformulareinstellungen gespeichert.', 'upfront'),
			'store_error' => __('Beim Speichern der Kontaktformulareinstellungen ist ein Problem aufgetreten.', 'upfront'),
			'error_sending' => __('Beim Senden der E-Mail ist ein Fehler aufgetreten.', 'upfront'),
			'error_captcha' => __('Das CAPTCHA-Feld ist ungültig.', 'upfront'),
			'mail_sent' => __('Die E-Mail wurde erfolgreich versendet.', 'upfront'),
			'missing_name' => __('Du musst Deinen Namen angeben.', 'upfront'),
			'invalid_email' => __('Deine E-Mail-Adresse ist ungültig.', 'upfront'),
			'missing_subject' => __('Du musst einen Betreff für die Nachricht schreiben.', 'upfront'),
			'missing_message' => __('Du hast vergessen, eine Nachricht zu schreiben.', 'upfront'),
			'css' => array(
				'labels_label' => __('Feldbezeichnungen', 'upfront'),
				'labels_info' => __('Textinfo für jedes Feld', 'upfront'),
				'fields_label' => __('Felder', 'upfront'),
				'fields_info' => __('Feldeingaben', 'upfront'),
				'msg_label' => __('Nachrichtenfeld', 'upfront'),
				'msg_info' => __('Nachrichtenfeld', 'upfront'),
				'err_label' => __('Fehlerfelder', 'upfront'),
				'err_info' => __('Felder mit Fehlern.', 'upfront'),
				'send_label' => __('Senden-Schaltfläche', 'upfront'),
				'send_info' => __('Senden-Schaltfläche des Formulars', 'upfront'),
			),
			'general' => array(
				'label' => __('Allgemeine Einstellungen', 'upfront'),
				'send_to' => __('Formularinhalt senden an:', 'upfront'),
				'button_text' => __('Text der Schaltfläche zum Senden des Kontaktformulars:', 'upfront'),
				'use_title' => __('Formulartitel verwenden', 'upfront'),
				'form_title' => __('Titel des Kontaktformulars:', 'upfront'),
			),
			'validation' => array(
				'label' => __('Formularvalidierung', 'upfront'),
				'on_field' => __('Inline', 'upfront'),
				'on_submit' => __('Vor dem Senden', 'upfront'),
			),
			'fields' => array(
				'label' => __('Formularfelder', 'upfront'),
				'title' => __('Kontaktformularfelder', 'upfront'),
				'name' => __('Namensfeldtext:', 'upfront'),
				'email' => __('E-Mail-Feldtext:', 'upfront'),
				'msg' => __('Nachrichtenfeldtext:', 'upfront'),
				'show_subject' => __('Betrefffeld', 'upfront'),
				'show_captcha' => __('CAPTCHA', 'upfront'),
				'subject' => __('Betreff Feldtext:', 'upfront'),
				'default_subject' => __('Standardbetreff:', 'upfront'),
				'label_localtion' => __('Position der Feldbeschriftung:', 'upfront')
			),
			'apr' => array(
				'label' => __('Darstellung', 'upfront'),
				'above' => __('Über dem Feld', 'upfront'),
				'over' => __('Über das Feld', 'upfront'),
				'inline' => __('Inline mit Feld', 'upfront'),
			),
			'settings' => __('Kontaktformular-Einstellungen', 'upfront'),
			'colors_label' => __('Farben', 'upfront'),
			'field_bg_label' => __('Feld BG', 'upfront'),
			'button_bg_label' => __('Schaltfläche BG', 'upfront'),
			'typography_label' => __('Typografie', 'upfront'),
			'field_labels_label' => __('Feldbeschriftungen', 'upfront'),
			'field_values_label' => __('Feldwerte', 'upfront'),
			'button_label' => __('Schaltfläche', 'upfront'),
			'field_button_label' => __('Feld & Schaltfläche', 'upfront'),
			'field_label' => __('Feld', 'upfront'),
			'template' => array(
				'missing_name' => __('Du musst Deinen Namen schreiben.', 'upfront'),
				'invalid_email' => __('Die E-Mail-adresse ist nicht gültig.', 'upfront'),
				'missing_subject' => __('Du musst einen Betreff für die Nachricht schreiben.', 'upfront'),
				'missing_body' => __('Du hast vergessen, eine Nachricht zu schreiben.', 'upfront'),
				'realperson_regenerate' => __('Klicke um zu ändern', 'upfront'),
			),
			'smtp' => array(
				'enable' => __('Aktiviere SMTP', 'upfront'),
				'label' => __('SMTP Einstellungen', 'upfront'),
				'from_email' => __('Von (Email)', 'upfront'),
				'from_name' => __('Von (Name)', 'upfront'),
				'host' => __('SMTP Host', 'upfront'),
				'none' => __('Keine', 'upfront'),
				'ssl' => __('SSL'),
				'tls' => __('TLS'),
				'port' => __('SMTP Port'),
				'no' => __('Nein', 'upfront'),
				'yes' => __('Ja', 'upfront'),
				'username' => __('Nutzername', 'upfront'),
				'password' => __('Passwort', 'upfront'),
				'configuration' => __('Konfiguration', 'upfront'),
				'secure' => __('SMTP Sicherheit', 'upfront'),
				'authentication' => __('Aktiviere SMTP-Authentifizierung', 'upfront')
			)
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

class Ucontact_Server extends Upfront_Server {
	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		upfront_add_ajax('upfront_contact-form',  array($this, 'on_ajax_send'));
		upfront_add_ajax_nopriv('upfront_contact-form',  array($this, 'on_ajax_send'));
	}

	public function on_settings_store () {
		$this->send_results(Upfront_UcontactView::store());
	}

	public function on_ajax_send () {
		$this->send_results(Upfront_UcontactView::ajax_send());

	}
	protected function send_results ($results) {
		if ($results['error']) {
			$this->_out(new Upfront_JsonResponse_Error($results['message']));
		} else {
			$this->_out(new Upfront_JsonResponse_Success($results));
		}
	}
}

<?php

class Upfront_Admin_Experimental extends Upfront_Admin_Page {

	const FORM_NONCE_KEY = "upfront_experimental_wpnonce";

	const FORM_NONCE_ACTION = "upfront_experimental_save";

	public function __construct () {
		if ($this->_can_access( Upfront_Permissions::SEE_USE_DEBUG )) {
			add_submenu_page( "upfront", __("Experimentelle Funktionen", Upfront::TextDomain), __("Experimental", Upfront::TextDomain), 'manage_options', Upfront_Admin::$menu_slugs['experimental'], array($this, "render_page") );
		}
	}

	/**
	 * Validiert und speichert per POST-Request übermittelte Daten
	 *
	 * @return bool
	 */
	private function _save_settings () {
		if (empty($_POST)) return false;
		if (!current_user_can('manage_options')) return false;

		$input = stripslashes_deep($_POST);

		// Überprüfe die erforderlichen Felder
		if (!isset($input['upront_experiments_submit']) || empty($input[self::FORM_NONCE_KEY])) return false;
		if (!wp_verify_nonce($input[self::FORM_NONCE_KEY], self::FORM_NONCE_ACTION)) return false;

		$compression = Upfront_Behavior::compression();
		$options = $compression->get_options();

		$all_levels = $compression->get_known_compression_levels();
		$options['level'] = !empty($input['experimental_optimization']) && in_array($input['experimental_optimization'], array_keys($all_levels))
			? $input['experimental_optimization']
			: false
		;

		$options['compression'] = !empty($input['experimental_compress_response']);

		$options['freeze'] = !empty($input['experimental_freeze_mode']);
		$options['freeze_time'] = !empty($input['experimental_freeze_time']) && is_numeric($input['experimental_freeze_time'])
			? (int)$input['experimental_freeze_time']
			: 0
		;

		$result = $compression->set_options($options);

		// Analysiere die Optionen nach erfolgreichem Speichern erneut
		if (!empty($result)) $compression->reload();

		return $result;
	}

	public function render_page () {
		if (!current_user_can('manage_options')) wp_die('Nope.');
		$this->_save_settings();

		$compression = Upfront_Behavior::compression();

		?>
		<div class="wrap upfront_admin upfront_admin_experimental">
			<h1><?php _e("Experimentelle Funktionen", Upfront::TextDomain); ?><span class="upfront_logo"></span></h1>
						<div class="upfront_admin_experimental_contents">
								<p class="info">
									<?php esc_html_e("Dies sind verschiedene experimentelle Funktionen, die UpFront zur Verfügung stehen. Bitte sei vorsichtig, einige dieser Einstellungen könnten Plugins stören.", Upfront::TextDomain ); ?>
								</p>
								<form action="<?php echo esc_url( add_query_arg( array("page" => "upfront_experimental") ) ) ?>" method="post" id="upfront_experimental_form">
										<div class="form_content">
												<div class="form_title bottom_separator">
													<span><?php esc_html_e("Leistungsoptimierungen", Upfront::TextDomain); ?></span>
												</div>
												<div class="form_content_group clear_after">
														<div class="form_content_group_title">
															<?php esc_html_e("Leistungsverbessernde Verhaltensänderungen", Upfront::TextDomain); ?>
														</div>
														<div class="form_content_input float_left">
																<div class="upfront_toggle_radio">
																		<input type="radio" name="experimental_optimization" id="experimental_optimization_off" <?php checked(false, $compression->has_experiments()); ?> value="" />
																		<label class="upfront_toggle_radio_label" for="experimental_optimization_off">
																			<span class="upfront_toggle_radio_button"></span>
																			<span class="upfront_toggle_radio_main_label"><?php esc_html_e("&#x1F40C; Keine Optimierung", Upfront::TextDomain ); ?></span>
																			<span class="upfront_toggle_radio_sub_label"><?php esc_html_e("Deaktiviert Standardoptimierung - Keine Optimierungen für Hohe Kompatibilität (Langsam)", Upfront::TextDomain ); ?></span>
																		</label>
																</div>
														</div>
														<div class="form_content_input float_left">
																<div class="upfront_toggle_radio">
																		<input type="radio" name="experimental_optimization" id="experimental_optimization_on" <?php checked(true, $compression->has_experiments_level('default')); ?> value="<?php echo esc_attr($compression->constant('default')); ?>" />
																		<label class="upfront_toggle_radio_label" for="experimental_optimization_on">
																			<span class="upfront_toggle_radio_button"></span>
																			<span class="upfront_toggle_radio_main_label"><?php esc_html_e("&#x1F407; Standard-Optimierung", Upfront::TextDomain ); ?></span>
																			<span class="upfront_toggle_radio_sub_label"><?php esc_html_e("Aktiviert Standardoptimierung - Leichte Optimierungen für Hohe Kompatibilität (Flott)", Upfront::TextDomain ); ?></span>
																		</label>
																</div>
														</div>
												</div>
												<div class="form_content_group">
														<div class="form_content_input">
																<div class="upfront_toggle_radio">
																		<input type="radio" name="experimental_optimization" id="experimental_aggressive" <?php checked(true, $compression->has_experiments_level('aggressive')); ?> value="<?php echo esc_attr($compression->constant('aggressive')); ?>" />
																		<label class="upfront_toggle_radio_label" for="experimental_aggressive">
																			<span class="upfront_toggle_radio_button"></span>
																			<span class="upfront_toggle_radio_main_label"><?php esc_html_e("&#x1F680; Aggressiv", Upfront::TextDomain ); ?></span>
																			<span class="upfront_toggle_radio_sub_label"><?php esc_html_e("Entprellt verwendete ClassicPress-integrierte Skripte und lädt sie asynchron (Schnell)", Upfront::TextDomain ); ?></span>
																		</label>
																</div>
														</div>
														<div class="form_content_input bottom_separator">
																<div class="upfront_toggle_radio">
																		<input type="radio" name="experimental_optimization" id="experimental_hardcore" <?php checked(true, $compression->has_experiments_level('hardcore')); ?> value="<?php echo esc_attr($compression->constant('hardcore')); ?>" />
																		<label class="upfront_toggle_radio_label" for="experimental_hardcore">
																			<span class="upfront_toggle_radio_button"></span>
																			<span class="upfront_toggle_radio_main_label"><?php esc_html_e("&#x2622; Hardcore", Upfront::TextDomain ); ?></span>
																			<span class="upfront_toggle_radio_sub_label"><?php esc_html_e("Alle eingebauten Abhängigkeiten sowie jQuery werden entprellt und in den Footer verschoben. &#x26A0; Dieser Modus wird sehr wahrscheinlich Plugins beschädigen, bitte mit Vorsicht verwenden &#x26A0; (RASANT).", Upfront::TextDomain ); ?></span>
																		</label>
																</div>
														</div>
														<div class="form_content_group_title">
															<?php esc_html_e("Zwischenspeichern von Assets (Freeze-Modus)", Upfront::TextDomain); ?>
														</div>
														<div class="form_content_input ">
															<div class="upfront_toggle">
																<input type="checkbox" name="experimental_freeze_mode" id="experimental_freeze_mode" <?php checked(true, $compression->get_option('freeze')); ?> value="1" class="upfront_toggle_checkbox" />
																<label class="upfront_toggle_label" for="experimental_freeze_mode">
																	<span class="upfront_toggle_inner"></span>
																	<span class="upfront_toggle_switch"></span>
																</label>
															</div>
															<div class="upfront_toggle_description">
																<span class="upfront_toggle_checkbox_main_label"><?php esc_html_e("Eingefrorene Assets zwischenspeichern", Upfront::TextDomain ); ?></span>
																<span class="upfront_toggle_checkbox_sub_label"><?php esc_html_e("Friere alle Assets in ihrem aktuellen Zustand ein und stellt sie zwischengespeichert bereit. Diese Option verbessert die Leistung und wird am besten verwendet, wenn Du mit den Änderungen an Deiner Webseite fertig bist.", Upfront::TextDomain ); ?></span>
															</div>
														</div>
														<div class="form_content_input ">
															<label class="upfront_select_label" for="experimental_freeze_time">
																<span class="upfront_select_main_label"><?php esc_html_e("Freeze Zeitraum", Upfront::TextDomain ); ?></span>
																<span class="upfront_select_sub_label"><?php esc_html_e("Wie lange muss gewartet werden, bis der Cache für eingefrorene Assets aktualisiert wird", Upfront::TextDomain ); ?></span>
															</label>
															<select name="experimental_freeze_time" id="experimetal_freeze_time">
																<option value="120" <?php selected(120, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Zwei Minuten (Debug)', 'upfront'); ?></option>
																<option value="3600" <?php selected(3600, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Eine Stunde', 'upfront'); ?></option>
																<option value="86400" <?php selected(86400, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Einen Tag', 'upfront'); ?></option>
																<option value="<?php echo (int)(DAY_IN_SECONDS * 2); ?>" <?php selected(DAY_IN_SECONDS * 2, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Zwei Tage', 'upfront'); ?></option>
																<option value="<?php echo (int)(DAY_IN_SECONDS * 3); ?>" <?php selected(DAY_IN_SECONDS * 3, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Drei Tage', 'upfront'); ?></option>
																<option value="<?php echo (int)(DAY_IN_SECONDS * 7); ?>" <?php selected(DAY_IN_SECONDS * 7, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Eine Woche', 'upfront'); ?></option>
																<option value="<?php echo (int)(DAY_IN_SECONDS * 14); ?>" <?php selected(DAY_IN_SECONDS * 14, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Zwei Wochen', 'upfront'); ?></option>
																<option value="<?php echo (int)(DAY_IN_SECONDS * 30); ?>" <?php selected(DAY_IN_SECONDS * 30, $compression->get_option('freeze_time', DAY_IN_SECONDS)); ?> ><?php esc_html_e('Einen Monat', 'upfront'); ?></option>
															</select>
														</div>
														<div class="form_content_input compress_response">
																<div class="upfront_toggle">
																		<input value="1" <?php checked(true, $compression->has_compression()); ?> type="checkbox" name="experimental_compress_response" id="experimental_compress_response" class="upfront_toggle_checkbox" />
																		<label class="upfront_toggle_label" for="experimental_compress_response">
																			<span class="upfront_toggle_inner"></span>
																			<span class="upfront_toggle_switch"></span>
																		</label>
																</div>
																<div class="upfront_toggle_description">
																	<span class="upfront_toggle_checkbox_main_label"><?php esc_html_e("Upfront Kompression aktivieren (GZip)", Upfront::TextDomain ); ?></span>
																	<span class="upfront_toggle_checkbox_sub_label"><?php esc_html_e("Wendet die GZip-Komprimierung auf alle von Upfront generierten Antworten an (AJAX)", Upfront::TextDomain ); ?></span>
																</div>
														</div>
												</div>
										</div>
										<?php wp_nonce_field(self::FORM_NONCE_ACTION, self::FORM_NONCE_KEY); ?>
										<button type="submit" name="upront_experiments_submit" id="upront_restrictions_submit"><?php esc_html_e("SPEICHERN", Upfront::TextDomain); ?></button>
								</form>
						</div>
		</div>
		<?php
	}
}

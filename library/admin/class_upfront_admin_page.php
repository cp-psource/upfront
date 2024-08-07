<?php


/**
 * Abstraktion der Admin-Seitenklasse
 */
abstract class Upfront_Admin_Page {

	public function __construct () {}

	abstract public function render_page ();

	/**
	 * Dienstprogramm zum Überprüfen der Fähigkeit zum Ändern von Einschränkungen
	 *
	 * @return bool
	 */
	protected function _can_access ($level=false) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$level = !empty($level)
			? $level
			: Upfront_Permissions::MODIFY_RESTRICTIONS
		;

		$current_user = wp_get_current_user();
		return isset($current_user->roles[0])
			? Upfront_Permissions::role( $current_user->roles[0], $level )
			: Upfront_Permissions::current( $level )
		;
	}
}

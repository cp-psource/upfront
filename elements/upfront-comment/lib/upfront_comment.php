<?php

class Upfront_UcommentView extends Upfront_Object {

	public function get_markup () {
		$element_id = $this->_get_property('element_id');
		$element_id = $element_id ? "id='{$element_id}'" : '';

		return "<div class=' upfront-comment-wrapper'>" .
			self::get_comment_markup(get_the_ID()) .
		"</div>";
	}

	public static function spawn_random_comments ($post) {
		$fake_comment = array(
			'user_id' => get_current_user_id(),
			'comment_author' => 'Author',
			'comment_author_IP' => '',
			'comment_author_url' => '',
			'comment_author_email' => '',
			'comment_post_ID' => $post->ID,
			'comment_type' => '',
			'comment_date' => current_time('mysql'),
			'comment_date_gmt' => current_time('mysql', 1),
			'comment_approved' => 1,
			'comment_content' => 'test stuff author comment',
			'comment_parent' => 0,
		);
		$comments = array(
			array_merge($fake_comment, array(
				'user_id' => get_current_user_id(),
				'comment_author' => __('Autor','upfront'),
				'comment_content' => 'test stuff author comment',
			)),
			array_merge($fake_comment, array(
				'user_id' => 0,
				'comment_author' => __('Besucher', 'upfront'),
				'comment_content' => 'test stuff visitor comment',
			)),
			array_merge($fake_comment, array(
				'user_id' => 0,
				'comment_author' => __('Trackback', 'upfront'),
				'comment_type' => 'trackback',
				'comment_content' => 'test stuff visitor trackback',
			)),
			array_merge($fake_comment, array(
				'user_id' => 0,
				'comment_author' => __('Pingback', 'upfront'),
				'comment_type' => 'pingback',
				'comment_content' => 'test stuff visitor pingkback',
			)),
		);
		foreach ($comments as $cid => $comment) {
			$comment['comment_ID'] = $cid;
			$comments[$cid] = (object)wp_filter_comment($comment);
		}
		return $comments;
	}

	/**
	 * Check if we have any external comments templates assigned by plugins.
	 *
	 * @param int $post_id Current post ID
	 *
	 * @return mixed (bool)false if nothing found, (string)finished template otherwise
	 */
	private static function _get_external_comments_template ($post_id) {
		if (!is_numeric($post_id)) return false;

		global $post, $wp_query;

		$overriden_template = false;

		// Sooo... override the post globals
		$global_post = is_object($post) ? clone($post) : $post;
		$post = get_post($post_id);

		$global_query = is_object($wp_query) ? clone($wp_query) : $wp_query;
		$wp_query->is_singular = true;
		if (!isset($wp_query->comments)) {
			$wp_query->comments = get_comments("post_id={$post_id}");
			$wp_query->comment_count = count($wp_query->comments);
		}

		$wp_tpl = apply_filters('comments_template', false);

		if (!empty($wp_tpl)) {
			ob_start();
			require_once($wp_tpl);
			$overriden_template = ob_get_clean();
		}

		$post = is_object($global_post) ? clone($global_post) : $global_post;
		$wp_query = is_object($global_query) ? clone($global_query) : $global_query;

		return $overriden_template;
	}

	public static function get_comment_markup ($post_id) {
		if (!$post_id) return '';
		if (!is_numeric($post_id)) {
			if (!in_array($post_id, array('fake_post', 'fake_styled_post'))) return '';
		}

		$tpl = self::_get_external_comments_template($post_id);
		if (!empty($tpl)) return $tpl;

		$defaults = self::default_properties();
		$prepend_form = (bool) $defaults['prepend_form'];
		$form_args = array(
			'comment_field' => self::_get_comment_form_field(),
		);
		$form_args = apply_filters('upfront_comment_form_args', array_filter($form_args));

		$comments = array();
		$post = false;
		if (is_numeric($post_id)) {
			$post = get_post($post_id);
			$comment_args = array(
				'post_id' => $post->ID,
				'order' => 'ASC',
				'orderby' => 'comment_date_gmt',
				'status' => 'approve',
			);
			$commenter = wp_get_current_commenter();
			$user_id = get_current_user_id();

			if (!empty($user_id)) $comment_args['include_unapproved'] = array($user_id);
			else if (!empty($commenter['comment_author_email'])) $comment_args['include_unapproved'] = array($commenter['comment_author_email']);

			$comments = get_comments($comment_args);
		} else {
			$posts = get_posts(array('orderby' => 'rand', 'posts_per_page' => 1));
			if (!empty($posts[0])) {
				$post = $posts[0];
				$comments = self::spawn_random_comments($post);
				add_filter('comments_open', '__return_true');
			}
			else return '';
		}

		if (empty($post) || !is_object($post)) return '';
		if (post_password_required($post->ID)) return '';

		// ... aaand start with comments fields rearrangement for WP4.4
		add_filter('comment_form_fields', array('Upfront_UcommentView', 'rearrange_comment_form_fields'));

		ob_start();

		if ($prepend_form) {
			comment_form($form_args, $post->ID);
		}
		// Load comments
		if ($comments && sizeof($comments)) {
			echo '<ol class="upfront-comments">';
			wp_list_comments(array('callback' => array('Upfront_UcommentView', 'list_comment'), 'style' => 'ol'), $comments);
			echo '</ol>';
		}
		// Load comment form
		if (!$prepend_form) {
			comment_form($form_args, $post->ID);
		}

		// Clean up after ourselves
		remove_filter('comment_form_fields', array('Upfront_UcommentView', 'rearrange_comment_form_fields'));

		return ob_get_clean();
	}

	/**
	 * Get the actual input form field markup.
	 * Yanked directly from wp-includes/comment-template.php
	 * because that's where it's hardcoded. Yay for hardcoding stuff.
	 *
	 * @return string
	 */
	private static function _get_comment_form_field () {
		return '<p class="comment-form-comment">' .
			'<label for="comment">' . _x( 'Comment', 'noun' ) . '</label>' .
			' ' .
			'<textarea placeholder="' . esc_attr(__('Hinterlasse eine Antwort', 'upfront')) . '" id="comment" name="comment" cols="45" rows="8" aria-describedby="form-allowed-tags" aria-required="true" required="required"></textarea>' .
		'</p>';
	}

	/**
	 * Re-arrange comment form fields.
	 *
	 * At the moment just to revert the WP 4.4 fields order change,
	 * but can be used to apply custom order down the line
	 *
	 * @param array $fields Comment form fields
	 *
	 * @return array
	 */
	public static function rearrange_comment_form_fields ($fields) {
		if (!is_array($fields) || empty($fields['comment'])) return $fields;
		
		$result = array();
		foreach ($fields as $key => $field) {
			if ('comment' === $key) continue;
			$result[$key] = $field;
		}
		$result['comment'] = $fields['comment'];

		return $result;
	}

	public static function list_comment ( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		echo upfront_get_template('upfront-comment-list', array( 'comment' => $comment, 'args' => $args, 'depth' => $depth ), upfront_element_dir('templates/upfront-comment-list.php', __DIR__));
	}

	public static function add_public_script () {
		if ( is_singular() && comments_open() && Upfront_Cache_Utils::get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );
	}

	public static function add_js_defaults($data){
		$data['ucomments'] = array(
			'defaults' => self::default_properties(),
		);
		return $data;
	}

	public static function default_properties(){
		return array(
			'id_slug' => 'ucomment',
			'type' => "UcommentModel",
			'view_class' => "UcommentView",
			"class" => "c24 upfront-comment",
			'has_settings' => 1,
			"prepend_form" => false
		);
	}

	public static function add_l10n_strings ($strings) {
		if (!empty($strings['comments_element'])) return $strings;
		$strings['comments_element'] = self::_get_l10n();
		return $strings;
	}

	private static function _get_l10n ($key=false) {
		$l10n = array(
			'element_name' => __('Kommentar', 'upfront'),
			'error_permissions' => __('Du kannst das nicht machen', 'upfront'),
			'loading' => __('Wird geladen', 'upfront'),
			'loading_error' => __("Fehler beim Laden des Kommentars", 'upfront'),
			'discussion_settings' => __('Diskussionseinstellungen', 'upfront'),
			'settings_disabled' => __('Diskussionseinstellungen sind deaktiviert', 'upfront'),
			'avatars' => __('Avatars', 'upfront'),
			'ok' => __('OK', 'upfront'),
			'please_wait' => __('Bitte warte', 'upfront'),
			'avatar_settings' => __('Avatar Einstellungen', 'upfront'),
			'show_avatars' => __('Avatare anzeigen', 'upfront'),
			'max_rating' => __('Maximale Bewertung', 'upfront'),
			'settings' => __('Einstellungen', 'upfront'),
			'main_panel' => __('Hauptsächlich', 'upfront'),
			'rating' => array(
				'g' => __('Für alle Zielgruppen geeignet', 'upfront'),
				'pg' => __('Möglicherweise anstößig, normalerweise für Zuschauer ab 13 Jahren', 'upfront'),
				'r' => __('Für erwachsenes Publikum über 18 bestimmt', 'upfront'),
				'x' => __('Noch reifer als R', 'upfront'),
			),
			'default_avatar' => __('Standard-Avatar', 'upfront'),
			'article' => array(
				'label' => __('Standardeinstellungen für Artikel', 'upfront'),
				'pingback' => __('Versuche alle Blogs, die mit dem Artikel verlinkt sind, zu benachrichtigen', 'upfront'),
				'ping_status' => __('Link-Benachrichtigungen von anderen Blogs zulassen (Pingbacks und Trackbacks)', 'upfront'),
				'comment_status' => __('Personen erlauben, Kommentare zu neuen Artikeln zu posten<br />(Diese Einstellungen können für einzelne Artikel überschrieben werden.)', 'upfront'),
				'attachments' => __('Anhänge in Kommentaren zulassen', 'upfront'),
				'email' => __('E-Mail-Abonnementfeld anzeigen', 'upfront'),
			),
			'other' => array(
				'label' => __('Andere Kommentareinstellungen', 'upfront'),
				'require_name_email' => __('Der Autor des Kommentars muss Name und E-Mail-Adresse angeben', 'upfront'),
				'comment_registration' => __('Benutzer müssen registriert und eingeloggt sein, um Kommentare abgeben zu können', 'upfront'),
				'autoclose' => __('Kommentare zu Artikeln, die älter als {{subfield}} Tage sind, automatisch schließen', 'upfront'),
				'thread_comments' => __('Aktiviere Thread-Kommentare (verschachtelte) {{subfield}} Ebenen tief', 'upfront'),
				'page_comments' => __('Paginiere Kommentare nach {{depth}} Kommentaren der obersten Ebene und zeige standardmäßig {{page}} Seite an', 'upfront'),
				'last' => __('Letzte', 'upfront'),
				'first' => __('Erste', 'upfront'),
				'order' => __('Kommentare sollten mit diesen Kommentaren oben auf jeder Seite angezeigt werden', 'upfront'),
				'older' => __('Älteste', 'upfront'),
				'newer' => __('Neueste', 'upfront'),
				'email_me' => __('Schicke eine E-Mail wenn', 'upfront'),
				'comments_notify' => __('Jemand einen Kommentar postet', 'upfront'),
				'moderation_notify' => __('Ein Kommentar zur Moderation zurückgehalten wird', 'upfront'),
				'before_comment_appears' => __('Bevor ein Kommentar erscheint', 'upfront'),
				'comment_moderation' => __('Ein Administrator muss den Kommentar immer genehmigen', 'upfront'),
				'comment_whitelist' => __('Der Kommentarautor muss einen zuvor genehmigten Kommentar haben', 'upfront'),
				'moderation_label' => __('Kommentar Moderation', 'upfront'),
				'max_links' => __('Halte einen Kommentar in der Warteschlange, wenn er {{field}} oder mehr Links enthält. (Ein gemeinsames Merkmal von Kommentar-Spam ist eine große Anzahl von Hyperlinks.)', 'upfront'),
				'moderation_keys' => __('Wenn ein Kommentar eines dieser Wörter in seinem Inhalt, Namen, URL, E-Mail oder IP enthält, wird er in der Moderationswarteschlange gehalten. Ein Wort oder IP pro Zeile. Es wird innerhalb von Wörtern übereinstimmen, also wird „press“ mit „ClassicPress“ übereinstimmen.', 'upfront'),
				'blacklist_keys' => __('Wenn ein Kommentar eines dieser Wörter in seinem Inhalt, Namen, URL, E-Mail oder IP enthält, wird er als Spam markiert. Ein Wort oder IP pro Zeile. Es wird innerhalb von Wörtern übereinstimmen, also wird „press“ mit „ClassicPress“ übereinstimmen.', 'upfront'),
			),
		);
		return !empty($key)
			? (!empty($l10n[$key]) ? $l10n[$key] : $key)
			: $l10n
		;
	}
}

class Upfront_UcommentAjax extends Upfront_Server {
	public static function serve () {
		$me = new self;
		$me->_add_hooks();
	}

	private function _add_hooks () {
		//add_action('wp_ajax_ucomment_get_comment_markup', array($this, "load_markup"));
		upfront_add_ajax('ucomment_get_comment_markup', array($this, "load_markup"));
		
		//add_action('wp_ajax_upfront-discussion_settings-get', array($this, "get_settings"));
		upfront_add_ajax('upfront-discussion_settings-get', array($this, "get_settings"));
		
		add_action('wp_ajax_upfront-discussion_settings-settings-save', array($this, "save_discussion_settings"));
		add_action('wp_ajax_upfront-discussion_settings-avatars-save', array($this, "save_avatars_settings"));
	}

	public function load_markup () {
		$data = json_decode(stripslashes($_POST['data']), true);
		if (empty($data['post_id'])) die('error');
		//if (!is_numeric($data['post_id'])) die('error');

		$this->_out(new Upfront_JsonResponse_Success(Upfront_UcommentView::get_comment_markup($data['post_id'])));
	}

	public function save_discussion_settings () {
		if (!Upfront_Permissions::current(Upfront_Permissions::OPTIONS)) $this->_out(new Upfront_JsonResponse_Error("Du kannst das nicht machen"));
		$data = stripslashes_deep($_POST['data']);

		if (isset($data['default_pingback_flag'])) {
			Upfront_Cache_Utils::update_option('default_pingback_flag', 1);
		} else {
			Upfront_Cache_Utils::update_option('default_pingback_flag', 0);
		}

		if (isset($data['default_ping_status'])) {
			Upfront_Cache_Utils::update_option('default_ping_status', 'open');
		} else {
			Upfront_Cache_Utils::update_option('default_ping_status', 0);
		}

		if (isset($data['default_comment_status'])) {
			Upfront_Cache_Utils::update_option('default_comment_status', 'open');
		} else {
			Upfront_Cache_Utils::update_option('default_comment_status', 0);
		}

		if (isset($data['require_name_email'])) {
			Upfront_Cache_Utils::update_option('require_name_email', (int)$data['require_name_email']);
		} else {
			Upfront_Cache_Utils::update_option('require_name_email', 0);
		}

		if (isset($data['comment_registration'])) {
			Upfront_Cache_Utils::update_option('comment_registration', (int)$data['comment_registration']);
		} else {
			Upfront_Cache_Utils::update_option('comment_registration', 0);
		}

		if (isset($data['close_comments_for_old_posts'])) {
			Upfront_Cache_Utils::update_option('close_comments_for_old_posts', (int)$data['close_comments_for_old_posts']);
		} else {
			Upfront_Cache_Utils::update_option('close_comments_for_old_posts', 0);
		}

		if (isset($data['close_comments_days_old'])) {
			Upfront_Cache_Utils::update_option('close_comments_days_old', (int)$data['close_comments_days_old']);
		} else {
			Upfront_Cache_Utils::update_option('close_comments_days_old', 0);
		}

		if (isset($data['thread_comments'])) {
			Upfront_Cache_Utils::update_option('thread_comments', (int)$data['thread_comments']);
		} else {
			Upfront_Cache_Utils::update_option('thread_comments', 0);
		}

		if (isset($data['thread_comments_depth'])) {
			Upfront_Cache_Utils::update_option('thread_comments_depth', (int)$data['thread_comments_depth']);
		} else {
			Upfront_Cache_Utils::update_option('thread_comments_depth', 0);
		}

		if (isset($data['page_comments'])) {
			Upfront_Cache_Utils::update_option('page_comments', (int)$data['page_comments']);
		} else {
			Upfront_Cache_Utils::update_option('page_comments', 0);
		}

		if (isset($data['default_comments_page'])) {
			if (in_array($data['default_comments_page'], array('newest', 'oldest'))) {
				Upfront_Cache_Utils::update_option('default_comments_page', $data['default_comments_page']);
			}
		}

		if (isset($data['comments_per_page'])) {
			Upfront_Cache_Utils::update_option('comments_per_page', (int)$data['comments_per_page']);
		} else {
			Upfront_Cache_Utils::update_option('comments_per_page', 0);
		}

		if (isset($data['comment_order'])) {
			if (in_array($data['comment_order'], array('asc', 'desc'))) {
				Upfront_Cache_Utils::update_option('comment_order', $data['comment_order']);
			}
		}

		if (isset($data['comments_notify'])) {
			Upfront_Cache_Utils::update_option('comments_notify', (int)$data['comments_notify']);
		} else {
			Upfront_Cache_Utils::update_option('comments_notify', 0);
		}

		if (isset($data['comment_moderation'])) {
			Upfront_Cache_Utils::update_option('comment_moderation', (int)$data['comment_moderation']);
		} else {
			Upfront_Cache_Utils::update_option('comment_moderation', 0);
		}

		if (isset($data['comment_max_links'])) {
			Upfront_Cache_Utils::update_option('comment_max_links', (int)$data['comment_max_links']);
		} else {
			Upfront_Cache_Utils::update_option('comment_max_links', 0);
		}

		if (isset($data['comment_whitelist'])) {
			Upfront_Cache_Utils::update_option('comment_whitelist', (int)$data['comment_whitelist']);
		} else {
			Upfront_Cache_Utils::update_option('comment_whitelist', 0);
		}

		if (isset($data['moderation_keys'])) {
			Upfront_Cache_Utils::update_option('moderation_keys', $data['moderation_keys']);
		}

		if (isset($data['blacklist_keys'])) {
			Upfront_Cache_Utils::update_option('blacklist_keys', $data['blacklist_keys']);
		}


		$this->_out(new Upfront_JsonResponse_Success('Yay'));
	}

	public function save_avatars_settings () {
		if (!Upfront_Permissions::current(Upfront_Permissions::OPTIONS)) $this->_out(new Upfront_JsonResponse_Error(self::_get_l10n('error_permissions')));
		$data = stripslashes_deep($_POST['data']);

		if (isset($data['show_avatars'])) {
			Upfront_Cache_Utils::update_option('show_avatars', 1);
		} else {
			Upfront_Cache_Utils::update_option('show_avatars', 0);
		}

		if (isset($data['avatar_rating'])) {
			if (in_array($data['avatar_rating'], array('G', 'PG', 'R', 'X'))) {
				Upfront_Cache_Utils::update_option('avatar_rating', $data['avatar_rating']);
			}
		} else {
			Upfront_Cache_Utils::update_option('avatar_rating', 'G');
		}

		if (isset($data['avatar_default'])) {
			$avatar_defaults = apply_filters('avatar_defaults', array(
				'mystery' => __('Mystery Man'),
				'blank' => __('Blank'),
				'gravatar_default' => __('Gravatar Logo'),
				'identicon' => __('Identicon (Generated)'),
				'wavatar' => __('Wavatar (Generated)'),
				'monsterid' => __('MonsterID (Generated)'),
				'retro' => __('Retro (Generated)')
			));
			if (in_array($data['avatar_default'], array_keys($avatar_defaults))) {
				Upfront_Cache_Utils::update_option('avatar_default', $data['avatar_default']);
			}
		} else {
			Upfront_Cache_Utils::update_option('avatar_default', 'mystery');
		}

		$this->_out(new Upfront_JsonResponse_Success('Yay'));
	}

	public function get_settings () {
		if (!Upfront_Permissions::current(Upfront_Permissions::OPTIONS)) $this->_out(new Upfront_JsonResponse_Error(self::_get_l10n('error_permissions')));
		global $current_user;
		$avatar_defaults = apply_filters('avatar_defaults', array(
			'mystery' => __('Mystery Man'),
			'blank' => __('Blank'),
			'gravatar_default' => __('Gravatar Logo'),
			'identicon' => __('Identicon (Generated)'),
			'wavatar' => __('Wavatar (Generated)'),
			'monsterid' => __('MonsterID (Generated)'),
			'retro' => __('Retro (Generated)')
		));

		// Temporary options toggle
		$show_avatars = Upfront_Cache_Utils::get_option('show_avatars');
		Upfront_Cache_Utils::update_option('show_avatars', "1");

		$avatars = array();
		foreach ($avatar_defaults as $key => $av) {
			$avatars[] = array(
				"value" => $key,
				"label" => $av,
				"icon" => preg_replace("/src='(.+?)'/", "src='\$1&amp;forcedefault=1'", get_avatar($current_user->user_email, 32, $key)),
			);
		}
		Upfront_Cache_Utils::update_option('show_avatars', $show_avatars);

		$this->_out(new Upfront_JsonResponse_Success(array(
			"properties" => array(
				// Discussion settings
				array("name" => "default_pingback_flag", "value" => Upfront_Cache_Utils::get_option('default_pingback_flag')),
				array("name" => "default_ping_status", "value" => Upfront_Cache_Utils::get_option('default_ping_status')),
				array("name" => "default_comment_status", "value" => Upfront_Cache_Utils::get_option('default_comment_status')),
				array("name" => "require_name_email", "value" => Upfront_Cache_Utils::get_option('require_name_email')),
				array("name" => "comment_registration", "value" => Upfront_Cache_Utils::get_option('comment_registration')),
				array("name" => "close_comments_for_old_posts", "value" => Upfront_Cache_Utils::get_option('close_comments_for_old_posts')),
				array("name" => "close_comments_days_old", "value" => Upfront_Cache_Utils::get_option('close_comments_days_old')),
				array("name" => "thread_comments", "value" => Upfront_Cache_Utils::get_option('thread_comments')),
				array("name" => "thread_comments_depth", "value" => Upfront_Cache_Utils::get_option('thread_comments_depth')),
				array("name" => "page_comments", "value" => Upfront_Cache_Utils::get_option('page_comments')),
				array("name" => "default_comments_page", "value" => Upfront_Cache_Utils::get_option('default_comments_page')),
				array("name" => "comments_per_page", "value" => Upfront_Cache_Utils::get_option('comments_per_page')),
				array("name" => "comment_order", "value" => Upfront_Cache_Utils::get_option('comment_order')),
				array("name" => "comments_notify", "value" => Upfront_Cache_Utils::get_option('comments_notify')),
				array("name" => "moderation_notify", "value" => Upfront_Cache_Utils::get_option('moderation_notify')),
				array("name" => "comment_moderation", "value" => Upfront_Cache_Utils::get_option('comment_moderation')),
				array("name" => "comment_max_links", "value" => Upfront_Cache_Utils::get_option('comment_max_links')),
				array("name" => "comment_whitelist", "value" => Upfront_Cache_Utils::get_option('comment_whitelist')),
				array("name" => "moderation_keys", "value" => esc_textarea(Upfront_Cache_Utils::get_option('moderation_keys'))),
				array("name" => "blacklist_keys", "value" => esc_textarea(Upfront_Cache_Utils::get_option('blacklist_keys'))),
				// Avatars settings
				array("name" => "show_avatars", "value" => $show_avatars),
				array("name" => "avatar_rating", "value" => Upfront_Cache_Utils::get_option('avatar_rating')),
				array("name" => "avatar_default", "value" => Upfront_Cache_Utils::get_option('avatar_default')),
			),
			"avatar_defaults" => $avatars
		)));
	}
}
Upfront_UcommentAjax::serve();


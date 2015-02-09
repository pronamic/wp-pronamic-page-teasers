<?php
/*
Plugin Name: Pronamic Page Teasers
Plugin URI: http://www.pronamic.eu/plugins/pronamic-page-teasers/
Description: Deprecated — This plugin makes it simple to bind pages (teasers) to a page.

Version: 1.2.1
Requires at least: 3.0

Author: Pronamic
Author URI: http://pronamic.eu/

Text Domain: pronamic-page-teasers
Domain Path: /languages/

License: GPL
*/

class PronamicPageTeasers {
	/**
	 * Nonce word not expected to recur, this nonce word is 
	 * used for the text domain, nonce fields, field names, etc.
	 * 
	 * @doc http://en.wikipedia.org/wiki/Nonce
	 * @var string
	 */
	const NONCE = 'pronamic-page-teasers';

	/**
	 * Indicator for save action
	 * 
	 * @var string
	 */
	const ACTION_SAVE = 'pronamic-page-teasers-save';

	/**
	 * Meta key for the teasers
	 * 
	 * @var string
	 */
	const META_KEY_TEASERS = '_pronamic_page_teasers';

	////////////////////////////////////////////////////////////

	/**
	 * Is teasers render
	 * 
	 * @var boolean
	 */
	public static $isTeasersRender = false;

	////////////////////////////////////////////////////////////

	/**
	 * Initialise this plugin
	 */
	public static function init() {
		$relPath = dirname(plugin_basename(__FILE__)) . '/languages/';

		load_plugin_textdomain(self::NONCE, false, $relPath);

		add_action('admin_init', array(__CLASS__, 'adminInitialize'));

		add_filter( 'the_content', 'pronamic_page_teasers_the_content' );
	}

	////////////////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public static function adminInitialize() {
		// Scripts
		wp_enqueue_script(
			self::NONCE , 
			plugins_url('/js/admin.js', __FILE__) , 
			array('jquery', 'jquery-ui-sortable')
		);

		$data = array(
			'del' => __('Delete', self::NONCE)
		);

		wp_localize_script(self::NONCE, 'pronamicPageTeasersL10n', $data);

		// Styles
		wp_enqueue_style(
			self::NONCE , 
			plugins_url('/css/admin.css', __FILE__) 
		);

		// Other
		add_action('add_meta_boxes', array(__CLASS__, 'addMetaBoxes'));

		add_action('save_post', array(__CLASS__, 'savePost'));
	}

	////////////////////////////////////////////////////////////

	/**
	 * Add a meta box to the page editor
	 */
	public static function addMetaBoxes() {
		$id = self::NONCE;
		$title = __('Teasers', self::NONCE);
		$callback = array(__CLASS__, 'renderMetaBox');
		$page = 'page';
		$context = 'side';

		add_meta_box($id, $title, $callback, $page, $context);
	}

	////////////////////////////////////////////////////////////

	/**
	 * Render the meta box
	 */
	public static function renderMetaBox() {
		wp_nonce_field(self::ACTION_SAVE, self::NONCE . '-nonce');

		include 'meta-box.php';
	}

	////////////////////////////////////////////////////////////

	/**
	 * Save the teaser data for the specified post ID
	 *
	 * @param int $postId
	 * @return int
	 */
	public static function savePost($postId) {
		$nonce = filter_input(INPUT_POST, self::NONCE . '-nonce', FILTER_SANITIZE_STRING);

		if(!wp_verify_nonce($nonce, self::ACTION_SAVE)) {
			return $postId;
		}

		// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want
		// to do anything
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $postId;
		}
	  
		// Check permissions
		if('page' == $_POST['post_type']) {
			if(!current_user_can('edit_page', $postId)) {
				return $postId;
			}
		} else {
			if(!current_user_can('edit_post', $postId)) {
				return $postId;
			}
		}
	
		// OK, we're authenticated: we need to find and save the data
		$teasers = filter_input(INPUT_POST, self::NONCE, FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY);
	
		if(empty($teasers)) {
			delete_post_meta($postId, self::META_KEY_TEASERS);
		} else {
			update_post_meta($postId, self::META_KEY_TEASERS, $teasers);
		}
	
		return $postId;
	}

	////////////////////////////////////////////////////////////

	/**
	 * Get the teaser of the current global post
	 * 
	 * @param mixed $args
	 * @return array
	 */
	public static function getTeasers() {
		$teasers = array();

		global $wpdb;

		$ids = get_post_meta(get_the_ID(), self::META_KEY_TEASERS, true);

		if(!empty($ids)) {
			$pages = get_pages(array(
				'include' => implode(', ', $ids) 
			));

			foreach($pages as $page) {
				$key = array_search($page->ID, $ids);
				
				$teasers[$key] = $page;
			}
			
			ksort($teasers);
		}

		return $teasers;
	}

	////////////////////////////////////////////////////////////

	/**
	 * Check if the current global post has teasers
	 * 
	 * @return boolean true if teasers exists, false otherwise
	 */
	public static function hasTeasers() {
		global $post;

		$ids = get_post_meta($post->ID, self::META_KEY_TEASERS, true);

		return !empty($ids);
	}

	////////////////////////////////////////////////////////////

	/**
	 * Template
	 */
	public static function render($args = '') {
		global $post;

		// Arguments
		$defaults = array(
			'echo' => true 
		);

		$args = wp_parse_args($args, $defaults);

		extract($args); 

		// If teasers are auto added to the_content remove the filter to 
		// prefend recursion
		$autoAdd = has_filter('the_content', 'pronamic_page_teasers_the_content');
		if($autoAdd) {
			remove_filter('the_content', 'pronamic_page_teasers_the_content', self::FILTER_THE_CONTENT_PRIORITY);
		}

		self::$isTeasersRender = true; 
		$page = $post;

		// Determine template
		$templates = array();
		$templates[] = 'pronamic-page-teasers-' . $post->ID . '.php';
		$templates[] = 'pronamic-page-teasers.php';

		$template = locate_template($templates);

		if(!$template) {
			$template = 'templates/pronamic-page-teasers.php';
		}

		$template = apply_filters('pronamic_page_teasers_template', $template);
		$content = null;

		if($template) {
			// Check to echo the content immediately or return
			if(!$echo) {
				ob_start();
			}

			include $template;

			if(!$echo) {
				$content = ob_get_clean();
			}
		}

		// If teasers are auto added to the_content remove the filter to 
		// prefend recursion
		if($autoAdd) {
			add_filter('the_content', 'pronamic_page_teasers_the_content', self::FILTER_THE_CONTENT_PRIORITY);
		}

		self::$isTeasersRender = false;
		$post = $page;

		return $content;
	}
}

////////////////////////////////////////////////////////////

/**
 * Checks if the teasers are being rendered
 * 
 * @return booelan true if teaser are rendered, false otherwise
 */
function is_pronamic_page_teasers() {
	return PronamicPageTeasers::$isTeasersRender;
}

/**
 * Pronamic page teaser the content
 * 
 * @param string $content
 * @return string
 */
function pronamic_page_teasers_the_content($content) {
	global $more;

	if($more) {
		return $content . pronamic_page_teasers(array('echo' => false));
	} else {
		return $content;
	}
}

////////////////////////////////////////////////////////////

/**
 * Get the Pronamic page teasers
 * 
 * @alias PronamicPageTeasers::getTeasers
 * @param mixed $args
 */
function pronamic_get_page_teasers($args = '') {
	return PronamicPageTeasers::getTeasers($args);
}

////////////////////////////////////////////////////////////

/**
 * Get the template part teaser
 * 
 * @param mixed $args
 */
function pronamic_page_teasers($args = '') {
	return PronamicPageTeasers::render($args);
}

////////////////////////////////////////////////////////////

add_action('init', array('PronamicPageTeasers', 'init'));

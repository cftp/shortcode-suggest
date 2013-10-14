<?php 
/*
Plugin Name: Shortcode Suggest
Description: Easily discover registered shortcodes in TinyMCE
Version: 1.0
Author:	Scott Evans  
Author URI: http://scott.ee
Text Domain: shortcode-suggest
Domain Path: /languages/
License: GPL v2 or later
*/

defined( 'ABSPATH' ) or die();

define( 'SS_PATH', dirname( __FILE__ ) );
define( 'SS_PATH_INCLUDES', dirname( __FILE__ ) . '/inc' );
define( 'SS_FOLDER', basename( SS_PATH ) );
define( 'SS_URL', plugins_url() . '/' . SS_FOLDER );
define( 'SS_URL_INCLUDES', SS_URL . '/inc' );

if (is_admin())
	$shortcode_suggest = new shortcode_suggest;

class shortcode_suggest {

	public function __construct() {

		# Actions
		//add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ss_js' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'ss_css' ) );
		add_action( 'wp_ajax_shortcode-suggest', array( $this, 'ss_ajax' ) );

		# Filters
		add_filter( 'mce_external_plugins', array( $this , 'mce_autocomplete' ) );
		//add_filter( 'mce_css', array( $this, 'mce_css' ) );
	}


	/**
	 *
	 * Boot the plugin in wp-admin
	 *
	 */
	function admin_init() {
		global $shortcode_tags;
		print_r($shortcode_tags);
	}

	/**
	 *
	 * Admin JS
	 *
	 */
	function ss_js() {

		wp_localize_script(
			'jquery',
			'autocomplete',
			array(
				'nonce'  => wp_create_nonce( 'autocomplete_nonce' ),
				'action' => 'shortcode-suggest',
			)
		);
	}

	/**
	 *
	 * Admin CSS
	 *
	 */
	function ss_css() {
		wp_register_style( 'ss_css', SS_URL . '/autocomplete/autocomplete.css', array(), '1', 'screen' );
		wp_enqueue_style( 'ss_css' );
	}

	/**
	 *
	 * Add the autocomplete plugin to TinyMCE
	 *
	 */
	function mce_autocomplete( $plugin_array ) {
		$plugin_array['autocomplete'] = SS_URL . '/autocomplete/autocomplete.js';
		return $plugin_array;
	}

	/**
	 *
	 * Handle the AJAX requests and return the shortcodes
	 *
	 */
	function ss_ajax() {

		check_ajax_referer( 'autocomplete_nonce', 'autocomplete_nonce' );

		# Load some libs (3rd party - may need some work)
		if (!class_exists('ShortcodeReferenceService')) {
			require_once(SS_PATH . '/shortcode-reference/lib/ShortcodeReference.php');
			require_once(SS_PATH . '/shortcode-reference/lib/ShortcodeReferenceService.php');
		}

		$shortcodes = ShortcodeReferenceService::getList();

		$results = array();

		foreach($shortcodes as $key => $shortcode) {

			$results[$key] = array(
				'description'   => $shortcode->getDescription(),
				'tags'  => $shortcode->getTags(),
				'params' => $shortcode->getParameters(),
			);
		}

		if ( empty( $results ) ) {
			wp_send_json_error( 'No results' );
		} else {
			wp_send_json_success( $results );
		}

	}

	/**
	 *
	 * Add custom css to TinyMCE
	 *
	 */
	function mce_css( $mce_css ) {
		
		if ( ! empty( $mce_css ) ){
			$mce_css .= ',';
		}

		$mce_css .= SS_URL . '/autocomplete/css/autocomplete.css';

		return $mce_css;
	}

}

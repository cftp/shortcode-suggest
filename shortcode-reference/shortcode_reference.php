<?php
 /**
  * Plugin Name: Shortcode Reference
  * Plugin URI: http://www.linkedin.com/in/bartstroeken
  * Version: 0.2
  * Author: Bart Stroeken
  * Author URI: http://www.linkedin.com/in/bartstroeken
  * Description: This plugin will provide the details about all available shortcodes when you'll need it most: when you're editing your content  
  **/
if (version_compare(phpversion(),'5.0.0','gt')) {
	require_once 'conf/include.php';

	$dir = dirname(__FILE__).'/lib';
	shortcode_overview_util_require_files($dir);
	/**
	 * Add an extra meta-box
	 */
	add_action('admin_head-post.php', 'shortcode_reference_scripts');
	add_action('admin_head-post-new.php', 'shortcode_reference_scripts');
	
	add_action('add_meta_boxes','shortcode_reference_render_meta_box');
	add_action('wp_ajax_shortcode_reference_find_shortcode', 'shortcode_reference_get_reference');
}

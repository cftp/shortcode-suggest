<?php
 /**
  * This file contains some utilities to get the extension working. 
  **/

/**
 * Utility-function to automatically include a dir recursively
 * 
 * @param string $path
 */
function shortcode_overview_util_require_files($path) {
	$filelist = scandir($path);
	$filelist = array_diff($filelist,array('..','.'));
	foreach($filelist as $file) {
		if (is_file($path.'/'.$file)){
			include_once($path.'/'.$file);
		} else {
			shortcode_overview_util_require_files($path.'/'.$file);
		}
	}
}

/**
 * Render the meta-boxes on the correct places
 */
function shortcode_reference_render_meta_box(){
	$ShortcodeReferenceUIManager = new ShortcodeReferenceUIManager();
	foreach (get_post_types( array('public' => true) ) as $posttype){
		add_meta_box('shortcode_overview_container',__('Shortcode reference','ShortcodesAutoreference'), array(&$ShortcodeReferenceUIManager,'showReferencePanel'), $posttype, 'side');
	}
}

/**
* Function to include static files for using this plugins. 
* 
**/
function shortcode_reference_scripts() {
	$referrer = realpath(dirname(__FILE__));
	wp_enqueue_style( 'shortcode-reference-style', plugins_url('/css/shortcode-reference.css', $referrer) );
	wp_enqueue_script( 'shortcode-reference-js', plugins_url('/js/shortcode-reference.js', $referrer) );
}

/**
 * A little utility-function to retrieve the shortcode's details. Executed in a custom action
 */
function shortcode_reference_get_reference(){
	$ShortcodeReferenceUIManager = new ShortcodeReferenceUIManager();
	$shortcode = $_POST['shortcode'];
	$ShortcodeReferenceUIManager->getReference($shortcode);	
}

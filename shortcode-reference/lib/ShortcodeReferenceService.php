<?php
/**
 * This class will autofill and retain the list of Shortcode References.
 * You can also "ask" this class for the desired reference  
 * 
 * @author Bart Stroeken
 */
class ShortcodeReferenceService {
	
	/**
	 * Array
	 * @var unknown_type
	 */
	private static $references;
	
	/**
	 * Return the list of references.
	 * 
	 * @return array;
	 */
	public static function getList($searchkey = null) {
		self::fillList();
		return self::$references;
	}

	/**
	 * Get a specific shortcode reference
	 * 
	 * @param string $shortcode
	 * @return ShortcodeReference
	 */
	public static function getReference($shortcode) {
		self::fillList();
		if (!key_exists($shortcode, self::$references)){
			return false;
		}
		return self::$references[$shortcode];
	}
	
	/**
	 * Seed the list with the references of registered shortcodes. 
	 *  
	 * @see ShortcodeReference
	 */
	private static function fillList(){
		if (!isset(self::$references)){
			/**
			 * The shorttags 
			 */
			global $shortcode_tags;
			
			self::$references = array();
			foreach($shortcode_tags as $tag => $function) {
				$name = $tag.'_Reflection';
				$$name = new ShortcodeReference($tag);
				self::$references[$tag] = $$name;
			}
		}
	}
}
<?php
/**
 * DataContainer for shortcodes. 
 * 
 * @author Bart Stroeken
 */
class ShortcodeReference {
	
	/**
	 * Shortcode
	 * @var string
	 */
	private $_shortcode;
	
	/**
	 * @var ReflectionFunction
	 */
	private $_function_reflection;

	/**
	 * @var string
	 */
	private $_filepath;
	
	/**
	 * Flat DocComments.
	 * @var string
	 */
	private $_description;
	
	/**
	 * @var array
	 */
	private $_attributes;
	
	/**
	 * @var string
	 */
	private $_function_name;
	
	/**
	 * The tags that were found in the documentation
	 * @var array
	 */
	private $_known_tags;

	/**
	 * - function name
	 * - attribute(s)
	 * - plugin or core
	 */	
	public function __construct($shortcode){
		global $shortcode_tags;
		
		if (!key_exists($shortcode, $shortcode_tags)){
			return false;
		}
		
		$this->_shortcode = $shortcode;
		$this->_function_name = $shortcode_tags[$shortcode];
		
		if (is_string($this->_function_name)){
			$this->_function_reflection = new ReflectionFunction($this->_function_name);
		} else if (is_array($this->_function_name)) {
			$this->_function_reflection = new ReflectionMethod ($this->_function_name[0],$this->_function_name[1]);
		}
	}

	/**
	 * If no description for the function is found, it will parse the DocComment of the function and return it as a string. 
	 */
	public function getDescription(){
		if ($this->_description == ''){
			$this->_known_tags = array();
			$desc = $this->_function_reflection->getDocComment();
			$parsed_desc = '';
			if ($desc){
				$matches = preg_split('/\n/',$desc);
				$start_pattern = '/w*\/\*\*w*/';
				foreach ($matches as $match) {
					if (preg_match($start_pattern, $match,$submatch)){
						// skip it
					} else if (preg_match('/w*\*\//',$match,$submatch)){
						$offset = strpos($match,'*/')-1;
						$final_line = '';
						$final_line.= trim(substr($match,0,-$offset)).'';
						if ($final_line != ''){
							$parsed_desc .= $final_line;
						}
					} else if (preg_match('/w*\*/',$match,$submatch)){
						if (preg_match('/@/',$match,$submatch)){
							$offset = strpos($match,'@')+1;
							$tag = trim(substr($match,$offset,strlen($match)-$offset));
							$this->addTagFromString($tag);
						} else {
							$offset = strpos($match,'*')+1;
							$parsed_desc .= trim(substr($match,$offset,strlen($match)-$offset)).'
		';
						}
					}
				}
			}
			if ($parsed_desc == ''){
				$parsed_desc = __('No documentation found. ','Shortcode Reference');
			}
			$this->_description = $parsed_desc;
		}
		return $this->_description;
	}
	
	/**
	 * Will find where the targeted function is defined.
	 * @return string
	 */
	public function getReference(){
		$absolute_path = $this->_function_reflection->getFileName();
		$this->_filepath = $absolute_path;
		if (strpos($absolute_path, ABSPATH)>=0){
			/**
			 * Yay, it's from Wordpress!
			 */
			$relative_path = str_replace(ABSPATH,'',$absolute_path);
			$is_native = strpos($relative_path, 'wp-includes/');
			$is_plugin = strpos($relative_path, 'wp-content/plugins/');
			if ($is_native !== false){
				return 'WordPress function';
			} else if ($is_plugin !== false){
				$plugin_path = explode('/',str_replace('wp-content/plugins/','',$relative_path));
				return 'Plugin: '.$plugin_path[0];
			}
		}
		return 'PHP native';
	}
	
	/**
	 * Retrieve the absolute file path
	 *
	 * @return string
	 */
	public function getFilePath(){
		return $this->_filepath;
	}
	
	/**
	 * Get the options for the function
	 * 
	 * @return array
	 */
	public function getParameters(){
		$options = $this->_function_reflection->getParameters();
	}
	
	/**
	 * Parse a string to a tag
	 * @param string $string
	 */
	private function addTagFromString($string){
		$tag = explode(' ',$string);
		$tagname = array_shift($tag);
		$this->_known_tags[$tagname] = implode(' ',$tag);
	}
	
	/**
	 * Get the tags for the current documentation
	 */
	public function getTags(){
		if (!is_array($this->_known_tags)){
			$desc = $this->getDescription();
		}
		return $this->_known_tags;
	}
	
	/**
	 * Get the URL where you can find more information about the shortcode.
	 * 
	 * @return url
	 */
	public function getUrl(){
		
		if (!$this->_url){
			
			$is_plugin = strpos($this->getReference(),'Plugin:');
			if ($this->getReference() == 'WordPress function'){

				$this->_url ='http://codex.wordpress.org/index.php?title=Special:Search&search='.$this->_shortcode.'_Shortcode';
			} else if ($is_plugin !== false){
				$plugin_info = get_plugin_data($this->_filepath);
				
				if (is_array($plugin_info) && key_exists('PluginURI',$plugin_info)){
					/**
					 * If you can find the plugin-url, use that
					 */
					$this->_url = $plugin_info['PluginURI'];
				} else if (is_array($plugin_info) && key_exists('AuthorURI',$plugin_info)){
					/**
					 * Else use the author-URI
					 */
					$this->_url = $plugin_info['AuthorURI'];
				} else {
					/**
					 * If all else fails, Google is your friend
					 */
					$this->_url = 'http://www.google.com/search?q=Wordpress+'.$plugin_path.'+'.$this->_shortcode;
				}
			} else {
				$this->_url = 'http://www.php.net/'.$this->_shortcode;
			}
		}
		return $this->_url;
	}
}

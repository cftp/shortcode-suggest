<?php
/**
 * This user interface manager will provide all HTML necessary for the custom box.
 * The data is provided by the ShortcodeReferenceService, 
 * and will be provided as a list of ShortcodeReference - objects
 * 
 * @author Bart Stroeken
 */
class ShortcodeReferenceUIManager {
	
	/**
	 * Constructor - nothing in particular to be done here
	 */
	function __construct() {
	}
	
	/**
	 * Setting it up - appending the right styles and scripts
	 */
	protected function setup(){
		wp_enqueue_style('shortcode-reference-style');	
	}
	
	/**
	 * Show the full panel
	 */
	public function showReferencePanel(){
		$this->setup();
		
		$content = $this->renderSearchbox();
		
		$items = ShortcodeReferenceService::getList();
		$content .= $this->renderList($items);
		$reference_text = __('Reference');
		$content .= '<h3>'.$reference_text.'</h3>';
		$content .= '<div id="shortcode_reference_details" class="shortcode_reference_container"></div>';
		echo $content; 
	}
	
	/**
	 * Render the searchbox
	 * 
	 */
	protected function renderSearchBox(){
		return '';
		$text = __('find');
		$result = '
<div class="searchbox">
	<input type="text" id="shortcode_reference_searchkey" size="25" name="shortcode_reference_searchkey"/>
	<input type="submit" id="shortcode_reference_searchbutton" value="'.$text.'" class="button-secondary"/>
</div>
';
		return $result;
	}

	/**
	 * Render the list of items
	 */
	protected function renderList($items){
		$shortcode_text = __('Shortcode');
		/** 
		 * headertje maken
		 */
		
		$result = '
<h3>'.$shortcode_text.'</h3>
<div class="shortcode_reference_list">';
		
		foreach ($items as $shortcode => $item){
			if ($item instanceof ShortcodeReference){
			$result .= '
	<div class="shortcode_reference_item" title="'.$item->getReference().'" id="'.$shortcode.'">'.$shortcode.'</div>';
			}
		}
		$result .='	
</div>';
		return $result;
	}
	
	/**
	 * Render a referenced item
	 * 
	 * @param ShortcodeReference $item
	 */
	public function getReference($string){
		$reference = ShortcodeReferenceService::getReference($string);
		if (!$reference){
			echo '<div class="shortcode_reference_description">'.__('Could not find any reference!','Shortcode Reference').'</div>';
			exit;
		}
		$url = $reference->getUrl();
		$reference_label = __('Reference', 'Shortcode Reference');
		$result = '
	<div class="shortcode_reference_item">
		<b><a href="'.$url.'" target="_blank">'.$reference->getReference().'</a></b>
	</div>';
		$result .= '<div class="shortcode_reference_description">'.nl2br($reference->getDescription()).'</div>';
		$tags = $reference->getTags();
		if (is_array($tags) && count($tags) > 0 ){
			
			$details = __('The nerdy details','Shortcode Reference');
			$result .= '<h4>'.$details.'</h4>';

			foreach ($tags as $tag => $description){
				$result .= '
	<div class="shortcode_reference_item">
		<div class="shortcode_key">'.$tag.'</div>
		<div class="shortcode_value">'.$description.'</div>
	</div>';
			}
		}
		echo $result;
		exit;
	}
}
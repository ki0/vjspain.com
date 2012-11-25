<?php

class UberOptions extends UberSparkOptions{
	
	
	function __construct( $id, $config = array() , $links = array() ){
		
		parent::__construct( $id, $config , $links );
		
		$this->options_key = self::generateOptionsKey( $this->id );
		
	}
	
	
	public static function generateOptionsKey( $id ){
		return $id;
	}
	
	public function previewButton(){
		global $uberMenu;
		return '<input type="submit" value="Preview" name="ubermenu-preview-button" id="ubermenu-preview-button" class="button reset-button" />'.
				'<div id="ubermenu-style-preview"></div>'.
				'<input type="submit" value="Show/Hide CSS" name="ubermenu-style-viewer-button" id="ubermenu-style-viewer-button" class="button reset-button" />'.
				'<div id="ubermenu-style-viewer"><textarea disabled>'.$uberMenu->getGeneratorCSS().'</textarea></div>';
		
	}

	function ubermenu_update_notifier() { 
		$xml = ubermenu_get_latest_plugin_version(129600);
		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/ubermenu/ubermenu.php' ); // Get plugin data (current version is what we want)
		?>

		<div class="wrap">
		
			<div id="icon-tools" class="icon32"></div>
			<div id="message" class="updated below-h2"><p><strong>There is a new version of UberMenu available.</strong> You have version <?php echo $plugin_data['Version']; ?> installed. Update to version <?php echo $xml->latest; ?>.</p></div>
			<br/>
			<a href="http://wpmegamenu.com/help/#updates" class="button" target="_blank">Update Instructions</a>
			<a href="http://codecanyon.net/item/ubermenu-wordpress-mega-menu-plugin/154703" class="button" target="_blank">Download update from CodeCanyon</a>
			<br/>
			<div class="clear"></div>
			
			<h3 class="title">Changelog</h3>
			<?php echo $xml->changelog; ?>

		</div>
		
	<?php } 
	
}
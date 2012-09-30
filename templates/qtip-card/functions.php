<?php
if ( ! class_exists('qTipCard') )
{
	class qTipCard
	{
		/**
		 * Load the template filters.
		 * 
		 * @author Steven A. Zahm
		 * @version 1.0
		 */
		public function __construct()
		{
			//Update the permitted shortcode attribute the user may use and overrride the template defaults as needed.
			add_filter( 'cn_list_atts_permitted-qtip-card' , array(&$this, 'initShortcodeAtts') );
			add_filter( 'cn_list_atts-qtip-card' , array(&$this, 'initTemplateOptions') );
			
			$printqTip = create_function( '' , 'wp_print_scripts("jquery-qtip");' );
			add_action( 'wp_footer', $printqTip );
		}
		
		/**
		 * Initiate the permitted template shortcode options and load the default values.
		 * 
		 * @author Steven A. Zahm
		 * @version 1.0
		 */
		public function initShortcodeAtts( $permittedAtts = array() )
		{
			//$permittedAtts['cnvcard_test'] ='init';
			
			return $permittedAtts;
		}
		
		/**
		 * Initiate the template options using the user supplied shortcode option values.
		 * 
		 * @author Steven A. Zahm
		 * @version 1.0
		 */
		public function initTemplateOptions($atts)
		{
			//$convert = new cnFormatting();
			//$atts['cnvcard-test'] ='true';
			return $atts;
		}
	}
	
	//print_r($this);
	$this->qTipCard = new qTipCard();
}
?>
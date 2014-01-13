<?php
/*
	Still needs a good refactor
	noted inline
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class shortcode {
    public $single;
	public static $atts;
	public static $template;
	public static $previousLetter;
	public static $filterRegistry;
	public static $card;
	public static $entry;
	public static $cards;
	public static $alternate;
	
    function __construct() {
        $this->register_template_shortcodes();
        add_shortcode('connections', array( $this, 'apply_download_button' ));
    }
	
	/*
    * Return array
	* @attr
	*/
	public static function build_shortcodes(){ //this is a temp way
		$shortcodes = array(
			'connections'=> array('dis'=>__('Connections')),
			'connections_list'=> array('dis'=>__('Connections')),//for backwards
			
			'attr'=> array('dis'=>__('Attributes at time of running')),
			'cards'=> array('dis'=>__('Cards')),
			'header'=> array('dis'=>__('Header')),
			'content'=> array('dis'=>__('Content')),
			'footer'=> array('dis'=>__('Footer')),
			'version'=> array('dis'=>__('Version')), 
			'dbversion'=> array('dis'=>__('Database version')), 
			'return_to_target'=>array('dis'=>__('Return to top')), 
			'tmp_slug'=>array('dis'=>__('Template Slug')), 
			'tmp_version'=>array('dis'=>__('Template Version')), 
			
			'entry_end'=>array('dis'=>__('End of entry')), 
			'entry_start'=>array('dis'=>__('Start of entry')), 
			'entry_before'=>array('dis'=>__('Before entry')), 
			'entry_after'=>array('dis'=>__('After entry')), 			
		
			'card'=>array('dis'=>__('Card')), 		
			'alternate'=>array('dis'=>__('Row alternate')), 
			
			'entry_type'=>array('dis'=>__('Enrty type')), 
			'entry_CategoryClass'=>array('dis'=>__('Entry Category Class')), 
			'entry_slug'=>array('dis'=>__('Entry Slug')), 
			'a_z_index'=>array('dis'=>__('A-Z index')), 
			
			
				
		);
		return $shortcodes;
	}

    /*
    * Register template shortcodes
	* should be a little more robust here... 
    */
    public function register_template_shortcodes() {
        $shortcodes = shortcode::build_shortcodes();
		foreach($shortcodes as $code=>$props){
			$_func = $code.'_func';
			if( method_exists($this,$_func) ){
				add_shortcode($code, array( $this, $_func ));
			}
		}
    }
	
	public function get_template_sections(){
		$sections = array('list','cards');
		return $sections;
	}


	public static function get_default_templates(){
		return array(
			'list' => array( 'tmp'=>'
						<div class="cn-list" id="cn-list" data-connections-version="[version]-[dbversion]"  style="[attr key="list-style"]" >
							<div class="cn-template cn-[tmp_slug]" id="cn-[tmp_slug]" data-template-version="[tmp_version]">
								[return_to_target]
								<div class="cn-list-head cn-clear" id="cn-list-head">
									[header]
								</div><!-- END #cn-list-head -->
								<div class="connections-list cn-clear" id="cn-list-body">
									[cards]
								</div><!-- END #cn-list-body -->
								<div class="cn-clear" id="cn-list-foot">
									[footer]
								</div><!-- END #cn-list-foot -->
							</div><!-- END #cn-[tmp_slug] -->
						</div><!-- END #cn-list -->
					' ),
			'cards' => array( 'tmp'=>'<div>[content]</div>' ),
			'card' => array( 'tmp'=>'
				[a_z_index]
				[entry_start]
				<div class="cn-list-row[alternate] vcard [entry_type] [entry_CategoryClass]" id="[entry_slug]">
					[entry_before]
						[card]
					[entry_after]
				</div><!-- END #[entry_slug] -->
				[entry_end]
			' )
		);
	}

	public static function get_section_template($template='list'){
		//would be pulled from a reg
		$registered_codes = shortcode::get_default_templates();

		//pulls from template -- this would be from the wsu-cbn template which needs no interference
		$template_registered_codes =array(
			'list'	=> array( 'tmp' => '[cards]' ),
			'cards'	=> array( 'tmp' => '[content]' ),
			'card'	=> array( 'tmp' => '[card]')
		);
		
		$registered_codes = array_merge($registered_codes,$template_registered_codes);
		
		if (isset( $registered_codes[$template] ) ) return $registered_codes[$template]['tmp'];
		return array();
	}


	public static function get_template_section_shortcodes($template='list'){
		//would be pulled from a reg
		$registered_codes = array(
			'list' => array(
					'cards','header','footer',
					'attr','version','dbversion','return_to_target','tmp_slug','tmp_version'//core stuff
				),
			'cards' => array(
					'content',
					'attr','version','dbversion','return_to_target','tmp_slug','tmp_version'//core stuff
			),
			'card' => array(
					'entry_end','entry_start','entry_before','entry_after',
					'card','alternate','entry_type','entry_CategoryClass','entry_slug','a_z_index',
					'attr','version','dbversion','return_to_target','tmp_slug','tmp_version'//core stuff
			)
				
		);
		if (isset( $registered_codes[$template] ) ) return $registered_codes[$template];
		return array();
	}

	public static function get_template_shortcodes($template='list'){
		$shortcodes = shortcode::build_shortcodes();
		$usingCodes = shortcode::get_template_section_shortcodes($template);
		$returning = array();
		foreach($shortcodes as $code=>$props){
			if(in_array($code,$usingCodes)){
				$returning[$code]= $props['dis'];
			}
		}
		return $returning;
	}
	
    /*
     * Return html with filtered shortcodes
     * @tmp_type - string
	 * needs to be reworked
	 * also move to class.shortcuts
     */
    public static function filter_shortcodes($attr=NULL,$tmp_type=NULL) {
		if($tmp_type==NULL) return false;
        $pattern       = get_shortcode_regex();
		$arr = array_keys(shortcode::get_template_shortcodes($tmp_type));
		$template      = shortcode::get_section_template($tmp_type);

        preg_match_all('/' . $pattern . '/s', $template, $matches);
        $html = $template;
        foreach ($arr as $code) {
            if (is_array($matches) && in_array($code, $matches[2])) {
                foreach ($matches[0] as $match) {
                    $html = str_replace($match, do_shortcode($match), $html);
                }
            }
        }
        return $html;
    }





		
	/******************
	* Functions
	*******************/
		/*
		* Return post content
		*/
	
	
	/**
	 * Register the [connections] shortcode
	 *
	 * Filters:
	 * 		cn_list_results					=> Filter the returned results before being processed for display.
	 *                                         Return indexed array of entry objects.   The entry list results
	 *                                         are passed. Return string.
	 *
	 * @access public
	 * @since unknown
	 * @param (array) $atts
	 * @param (string) $content [optional]
	 * @param (string) $tag [optional] When called as the callback for add_shortcode, the shortcode tag is
	 *                                 passed automatically. Manually setting the shortcode tag so the function
	 *                                 can be called independently.
	 * @return (string)
	 */
	public static function connections_list($atts, $content = NULL, $tag = 'connections'){
		return shortcode::connections( $atts, $content,$tag);
	}
	public static function connectionsList($atts, $content = NULL, $tag = 'connections'){
		return shortcode::connections( $atts, $content,$tag);
	}
	public static function connections( $atts, $content = NULL, $tag = 'connections' ) {
		global $wpdb, $wp_filter, $current_user, $connections;
	
		$cn_list_html   = '';
		$form           = new cnFormObjects();
		$convert        = new cnFormatting();
		$format         =& $convert;
	
		shortcode::$template = prep_template($atts);
		
		do_action( 'cn_action_include_once-' . shortcode::$template->getSlug() );
		do_action( 'cn_action_js-' . shortcode::$template->getSlug() );
		
		
		$atts = connectionsListData( $atts, shortcode::$template, $tag );
		$atts['list-style']=empty( $atts['width'] ) ? '' : 'width: ' . $atts['width'] . 'px;';
		shortcode::$atts=$atts;
		$cards = $connections->retrieve->entries( $atts );
				
		// Apply any registered filters to the results.
		if ( ! empty( $cards ) ) {
			$cards = apply_filters( 'cn_list_results', $cards );
			$cards = apply_filters( 'cn_list_results-' . shortcode::$template->getSlug() , $cards );
			shortcode::$filterRegistry[] = 'cn_list_results-' . shortcode::$template->getSlug();
		}
		shortcode::$cards=$cards;
		
		ob_start();
			// Prints the template's CSS file.
			do_action( 'cn_action_css-' . shortcode::$template->getSlug() , $atts );
			$cn_list_html .= ob_get_contents();
		ob_end_clean();
	
		//proccess template with shortcodes	
		$cn_list_html .= shortcode::filter_shortcodes($atts,'list');
	
		/*
		 * Remove any filters a template may have added
		 * so it is not run again if more than one template
		 * is in use on the same page.
		 */
		foreach ( shortcode::$filterRegistry as $filter ) {
			if ( isset( $wp_filter[ $filter ] ) ) unset( $wp_filter[ $filter ] );
		}
	
		if ( cnSettingsAPI::get( 'connections', 'connections_compatibility', 'strip_rnt' ) ) {
			$search = array( "\r\n", "\r", "\n", "\t" );
			$replace = array( ' ', ' ', ' ', ' ' );
			$cn_list_html = str_replace( $search , $replace , $cn_list_html);
		}
		
		return $cn_list_html;
	}
	
	
	/**
	 * Register the [connections] shortcode
	 *
	 * Filters:
	 * 		cn_list_no_result_message		=> Change the 'no results message'.
	 *
	 * @access public
	 * @since unknown
	 * @param (array) $atts
	 * @param (string) $content [optional]
	 * @param (string) $tag [optional] When called as the callback for add_shortcode, the shortcode tag
	 *                                  is passed automatically. Manually setting the shortcode tag so
	 *                                  the function can be called independently.
	 * @return (string)
	 */	
    /*
    * Return card loop html
    */
    public function cards_func() {
        global $card;
		$template	= shortcode::$template;
		$atts		= shortcode::$atts;
		$cards		= shortcode::$cards;
		
		//var_dump($atts);
		$html="";
		// If there are no results no need to proceed and output message.
		if ( empty( $cards ) ) {
			// The no results message.
			ob_start();
				do_action( 'cn_action_no_results', $atts , $template->getSlug() );
				shortcode::$filterRegistry[] = 'cn_list_no_result_message-' . $template->getSlug();
				$html .= ob_get_contents();
			ob_end_clean();
		} else {
			$previousLetter = '';
			$alternate      = '';
			foreach ( $cards as $row ) {
				shortcode::$card = $row;
				$postHtml = shortcode::filter_shortcodes($atts,'cards');
				$html .= $postHtml;
			}
		}
        return $html;
    }
	
	
	/**
	 * Register the [connections] shortcode
	 *
	 * Filters:
	 * 		cn_list_entry_before			=> Can be used to add content before the output of the entry.
	 * 										   The entry data is passed. Return string.
	 * 		cn_list_entry_after				=> Can be used to add content after the output of the entry.
	 * 										   The entry data is passed. Return string.
	 *
	 * @access public
	 * @since unknown
	 * @param (array) $atts
	 * @param (string) $content [optional]
	 * @param (string) $tag [optional] When called as the callback for add_shortcode, the shortcode tag
	 *                                  is passed automatically. Manually setting the shortcode tag so
	 *                                  the function can be called independently.
	 * @return (string)
	 */	
    /*
    * Return loop html
    */
    public function content_func() {
		$template	= shortcode::$template;
		$atts		= shortcode::$atts;
		$previousLetter = shortcode::$previousLetter;
		
		
		$skipEntry = array();
		$card_html = '';

		$entry = new cnvCard( shortcode::$card );
		shortcode::$entry=$entry;
		$vCard =& shortcode::$entry;

		// Configure the page where the entry link to.
		$entry->directoryHome( array( 'page_id' => $atts['home_id'], 'force_home' => $atts['force_home'] ) );

		// @TODO --> Fix this somehow in the query, see comment above for $skipEntry.
		if ( in_array( $entry->getId() , $skipEntry ) ) continue;
		$skipEntry[] = $entry->getId();

		// Display the Entry Actions.
		if ( get_query_var( 'cn-entry-slug' ) ) {
			// List actions template part.
			ob_start();
				do_action( 'cn_action_entry_actions', $atts , $entry );
				$card_html .= ob_get_contents();
			ob_end_clean();

		}

		$card_html .= shortcode::filter_shortcodes($atts,'card');

		return $card_html;
    }

	public function card_func(){
		$entry		= shortcode::$entry;
		$template	= shortcode::$template;
		$atts		= shortcode::$atts;
		$html="";
		
		ob_start();
			if ( get_query_var( 'cn-entry-slug' ) && has_action( 'cn_action_card_single-' . $template->getSlug() ) ) {
				do_action( 'cn_action_card_single-' . $template->getSlug(), $entry, $template, $atts );
			} else {
				do_action( 'cn_action_card-' . $template->getSlug(), $entry, $template, $atts );
			}
			$html .= ob_get_contents();
		ob_end_clean();
		
		return $html;
	}

	public function a_z_index_func(){
		$entry		= shortcode::$entry;
		$template	= shortcode::$template;
		$atts		= shortcode::$atts;
		$html="";
		
		$currentLetter = strtoupper( mb_substr( $entry->getSortColumn(), 0, 1 ) );
		if ( $currentLetter != shortcode::$previousLetter ) {
			$out .= sprintf( '<div class="cn-list-section-head cn-clear" id="cn-char-%1$s">', $currentLetter );
				if ( $atts['show_alphaindex'] && $atts['repeat_alphaindex'] ) $out .= $charIndex;
				if ( $atts['show_alphahead'] ) $out .= sprintf( '<h4 class="cn-alphahead">%1$s</h4>', $currentLetter );
			$html .= '</div>' . ( WP_DEBUG ? '<!-- END #cn-char-' . $currentLetter . ' -->' : '' );
			shortcode::$previousLetter = $currentLetter;
		}	
		
		return $html;
	}
	
	public function entry_before_func(){
		$entry		= shortcode::$entry;
		$template	= shortcode::$template;
		$html="";

		$html .= apply_filters( 'cn_list_entry_before' , '' , $entry );
		$html .= apply_filters( 'cn_list_entry_before-' . $template->getSlug() , '' , $entry );
		shortcode::$filterRegistry[] = 'cn_list_entry_before-' . $template->getSlug();

		return $html;
	}
		
	public function entry_after_func(){
		$entry		= shortcode::$entry;
		$template	= shortcode::$template;
		$html="";

		$html .= apply_filters( 'cn_list_entry_after' , '' , $entry );
		$html .= apply_filters( 'cn_list_entry_after-' . $template->getSlug() , '' , $entry );
		shortcode::$filterRegistry[] = 'cn_list_entry_after-' . $template->getSlug();

		return $html;
	}	
	
	public function entry_end_func(){
		$entry		= shortcode::$entry;
		$atts		= shortcode::$atts;
		$template	= shortcode::$template;
		$out="";

		// After entry actions.
		ob_start();
			do_action( 'cn_action_entry_after' , $atts , $entry );
			do_action( 'cn_action_entry_after-' . $template->getSlug() , $atts , $entry );
			shortcode::$filterRegistry[] = 'cn_action_entry_after-' . $template->getSlug();

			do_action( 'cn_action_entry_both' , $atts , $entry  );
			do_action( 'cn_action_entry_both-' . $template->getSlug() , $atts ,$entry );
			shortcode::$filterRegistry[] = 'cn_action_entry_both-' . $template->getSlug();

			$out .= ob_get_contents();
		ob_end_clean();

		return $out;
	}
	
	public function entry_start_func(){
		$entry=shortcode::$entry;
		$atts = shortcode::$atts;
		$template = shortcode::$template;	
		$out="";

		// before entry actions.
		ob_start();
			do_action( 'cn_action_entry_before' , $atts , $entry );
			do_action( 'cn_action_entry_before-' . $template->getSlug() , $atts , $entry );
			shortcode::$filterRegistry[] = 'cn_action_entry_before-' . $template->getSlug();

			do_action( 'cn_action_entry_both' , $atts , $entry  );
			do_action( 'cn_action_entry_both-' . $template->getSlug() , $atts , $entry );
			shortcode::$filterRegistry[] = 'cn_action_entry_both-' . $template->getSlug();

			$out .= ob_get_contents();
		ob_end_clean();

		return $out;
	}	
	
	
	
	
	/**
	 * Register the [connections] shortcode
	 *
	 * Filters:
	 * 		cn_list_after					=> Can be used to add content after the output of the list.
	 * 										   The entry list results are passed. Return string.
	 *
	 * @access public
	 * @since unknown
	 * @param (array) $atts
	 * @param (string) $content [optional]
	 * @param (string) $tag [optional] When called as the callback for add_shortcode, the shortcode tag
	 *                                 is passed automatically. Manually setting the shortcode tag so the
	 *                                 function can be called independently.
	 * @return (string)
	 */
	public function footer_func(){
		$cards=shortcode::$cards;
		$atts = shortcode::$atts;
		$template = shortcode::$template;
	
		$out="";
	
		$out .= apply_filters( 'cn_list_after' , '' , $cards );
		$out .= apply_filters( 'cn_list_after-' . $template->getSlug() , '' , $cards );
		shortcode::$filterRegistry[] = 'cn_list_after-' . $template->getSlug();
		ob_start();
			do_action( 'cn_action_list_both' , $atts , $cards  );
			do_action( 'cn_action_list_both-' . $template->getSlug() , $atts , $cards );
			shortcode::$filterRegistry[] = 'cn_action_list_both-' . $template->getSlug();
	
			do_action( 'cn_action_list_after' , $atts , $cards );
			do_action( 'cn_action_list_after-' . $template->getSlug() , $atts , $cards );
			shortcode::$filterRegistry[] = 'cn_action_list_after-' . $template->getSlug(); 
			$out .= ob_get_contents();
		ob_end_clean();
	
		return $out;
	}
	/**
	 * Register the [connections] shortcode
	 *
	 * Filters:
	 * 		cn_list_before					=> Can be used to add content before the output of the list.
	 * 										   The entry list results are passed. Return string.
	 * 		cn_list_index					=> Can be used to modify the index before the output of the list.
	 * 										   The entry list results are passed. Return string.
	 *
	 * @access public
	 * @since unknown
	 * @param (array) $atts
	 * @param (string) $content [optional]
	 * @param (string) $tag [optional] When called as the callback for add_shortcode, the shortcode tag is 
	 *                                 passed automatically. Manually setting the shortcode tag so the function 
	 *                                 can be called independently.
	 * @return (string)
	 */
	public function header_func(){
		$cards		= shortcode::$cards;
		$atts		= shortcode::$atts;
		$template	= shortcode::$template;
		
		$out="";
		// Display the List Actions.
		if ( ! get_query_var( 'cn-entry-slug' ) ) {
			// List actions.
			ob_start();
				do_action( 'cn_action_list_actions', $atts );
				$out .= ob_get_contents();
			ob_end_clean();
		}
		ob_start();
			do_action( 'cn_action_list_before' , $atts , $cards );
			do_action( 'cn_action_list_before-' . $template->getSlug() , $atts , $cards );
			shortcode::$filterRegistry[] = 'cn_action_list_before-' . $template->getSlug();
	
			do_action( 'cn_action_list_both' , $atts , $cards );
			do_action( 'cn_action_list_both-' . $template->getSlug() , $atts , $cards );
			shortcode::$filterRegistry[] = 'cn_action_list_both-' . $template->getSlug();
	
			$out .= ob_get_contents();
		ob_end_clean();
	
		$out .= apply_filters( 'cn_list_before' , '' , $cards );
		$out .= apply_filters( 'cn_list_before-' . $template->getSlug() , '' , $cards );
		shortcode::$filterRegistry[] = 'cn_list_before-' . $template->getSlug();
	
		// The character index template part.
		ob_start();
			do_action( 'cn_action_character_index' , $atts );
			$charIndex = ob_get_contents();
		ob_end_clean();
	
		$charIndex = apply_filters( 'cn_list_index' , $charIndex , $cards );
		$charIndex = apply_filters( 'cn_list_index-' . $template->getSlug() , $charIndex , $cards );
		shortcode::$filterRegistry[] = 'cn_list_index-' . $template->getSlug();
	
		/*
		 * The alpha index is only displayed if set to true and not set to repeat.
		 * If alpha index is set to repeat, that is handled separately.
		 */
		if ( $atts['show_alphaindex'] && ! $atts['repeat_alphaindex'] ) $out .= $charIndex;	
		return $out;
	}
	
	public function tmp_slug_func(){
		$tmp_slug	= shortcode::$template->getSlug();
		return $tmp_slug;
	}
	public function tmp_version_func(){
		$tmp_version	= shortcode::$template->getVersion();
		return $tmp_version;
	}
	public function return_to_target_func(){
		$atts		= shortcode::$atts;
		ob_start();
			// The return to top anchor
			do_action( 'cn_action_return_to_target', $atts );
			$return_to_target = ob_get_contents();
		ob_end_clean();
		return $return_to_target;
	}
	
	
	public function verision_func(){
		global $connections;
		$version	= $connections->options->getVersion();
		return $version;
	}
	public function dbversion_func(){
		global $connections;
		$dbversion	= $connections->options->getDBVersion();
		return $dbversion;
	}

	public function alternate_func(){
		shortcode::$alternate = shortcode::$alternate == '' ? '-alternate' : '';
		return shortcode::$alternate;
	}
	public function entry_type_func(){
		$entry_type	= shortcode::$entry->getEntryType();
		return $entry_type;
	}
	public function entry_CategoryClass_func(){
		$entry_CategoryClass = shortcode::$entry->getCategoryClass(TRUE);
		return $entry_CategoryClass;
	}
	public function entry_slug_func(){
		$entry_slug	= shortcode::$entry->getSlug();
		return $entry_slug;
	}
	public function attr_func($attrs){
		$atts		= shortcode::$atts;
		extract(shortcode_atts(array(
				'key'=>'-fail-'
			), $atts));
		return isset($atts[$key])?$atts[$key]:'';
	}
	
	
}
?>
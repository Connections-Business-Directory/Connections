<?php

/**
 * Create custom HTML forms.
 */
class cnFormObjects
{
	private $nonceBase = 'connections';
	private $validate;
	private $visibiltyOptions = array('Public'=>'public','Private'=>'private','Unlisted'=>'unlisted');
	
	public function __construct()
	{
		//if ( is_admin() ) $this->registerMetaboxes();
		
		// Load the validation class.
		$this->validate = new cnValidate();
		
		/*
		 * Create the visibility option array based on the current user capability.
		 */
		foreach ( $this->visibiltyOptions as $key => $option )
		{
			if ( ! $this->validate->userPermitted( $option ) ) unset( $this->visibiltyOptions[$key] );
		}
	}
	
	/**
	 * The form open tag.
	 * 
	 * @todo Finish adding form tag attributes.
	 * @param array
	 * @return string
	 */
	 public function open($attr)
	{
		if ( isset($attr['name']) ) $attr['name'] = 'name="' . $attr['name'] . '" ';
		if ( isset($attr['action']) ) $attr['action'] = 'action="' . $attr['action'] . '" ';
		if ( isset($attr['accept']) ) $attr['accept'] = 'accept="' . $attr['accept'] . '" ';
		if ( isset($attr['accept-charset']) ) $attr['accept-charset'] = 'accept-charset="' . $attr['accept-charset'] . '" ';
		if ( isset($attr['enctype']) ) $attr['enctype'] = 'enctype="' . $attr['enctype'] . '" ';
		if ( isset($attr['method']) ) $attr['method'] = 'method="' . $attr['method'] . '" ';
				
		$out = '<form ';
		
		foreach ($attr as $key => $value)
		{
			$out .= $value;
		}
		
		echo $out , '>';
	}
	
	/**
	 * @return string HTML close tag
	 */
	public function close()
	{
		echo '</form>';
	}
		
	//Function inspired from:
	//http://www.melbournechapter.net/wordpress/programming-languages/php/cman/2006/06/16/php-form-input-and-cross-site-attacks/
	/**
	 * Creates a random token.
	 * 
	 * @param string $formId The form ID
	 * 
	 * @return string
	 */
	public function token($formId)
	{
		$token = md5(uniqid(rand(), true));
		
		return $token;
	}
	
	public function tokenCheck($tokenID, $token)
	{
		global $connections;
		$token = attribute_escape($token);
		
		/**
		 * @TODO: Check for $tokenID.
		 */
		
		if (isset($_SESSION['cn_session']['formTokens'][$tokenID]['token']))
		{
			$sessionToken = esc_attr($_SESSION['cn_session']['formTokens'][$tokenID]['token']);
		}
		else
		{
			$connections->setErrorMessage('form_no_session_token');
			$error = TRUE;
		}
		
		if (empty($token))
		{
			$connections->setErrorMessage('form_no_token');
			$error = TRUE;
		}
		
		if ($sessionToken === $token && !$error)
		{
			unset($_SESSION['cn_session']['formTokens']);
			return TRUE;
		}
		else
		{
			$connections->setErrorMessage('form_token_mismatch');
			return FALSE;
		}
				
	}
	
	/**
	 * Retrieves or displays the nonce field for forms using wp_nonce_field.
	 * 
	 * @param string $action Action name.
	 * @param string $item [optional] Item name. Use when protecting multiple items on the same page.
	 * @param string $name [optional] Nonce name.
	 * @param bool $referer [optional] Whether to set and display the refer field for validation.
	 * @param bool $echo [optional] Whether to display or return the hidden form field.
	 * 
	 * @return string
	 */
	public function tokenField($action, $item = FALSE, $name = '_cn_wpnonce', $referer = TRUE, $echo = TRUE)
	{
		$name = esc_attr($name);
		
		if ($item == FALSE)
		{
			$token = wp_nonce_field($this->nonceBase . '_' . $action, $name, TRUE, FALSE);
		}
		else
		{
			$token = wp_nonce_field($this->nonceBase . '_' . $action . '_' . $item, $name, TRUE, FALSE);
		}
		
		if ($echo) echo $token;
		if ($referer) wp_referer_field($echo, 'previous');
		
		return $token;
	}
	
	/**
	 * Retrieves URL with nonce added to the query string.
	 * 
	 * @param string $actionURL URL to add the nonce to.
	 * @param string $item Nonce action name.
	 * @return string
	 */
	public function tokenURL($actionURL, $item)
	{
		return wp_nonce_url($actionURL, $item);
	}
	
	/**
	 * Generate the complete nonce string, from the nonce base, the action and an item.
	 * 
	 * @param string $action Action name.
	 * @param string $item [optional] Item name. Use when protecting multiple items on the same page.
	 * @return string
	 */
	public function getNonce($action, $item = FALSE)
	{
		if ($item == FALSE)
		{
			$nonce = $this->nonceBase . '_' . $action;
		}
		else
		{
			$nonce = $this->nonceBase . '_' . $action . '_' . $item;
		}
		
		return $nonce;
	}
	
	/**
	 * Builds an alpha index.
	 * @return string
	 */
	public function buildAlphaIndex()
	{
		$linkindex = '';
		$alphaindex = range("A","Z");
		
		foreach ($alphaindex as $letter) {
			$linkindex .= '<a href="#' . $letter . '">' . $letter . '</a> ';
		}
		
		return $linkindex;
	}
	
	/**
	 * Builds a form select list
	 * @return HTML form select
	 * @param string $name
	 * @param array $value_options Associative array where the key is the name visible in the HTML output and the value is the option attribute value
	 * @param string $selected[optional]
	 */
	public function buildSelect($name, $value_options, $selected=null)
	{
		
		$select = "<select name='" . $name . "'> \n";
		foreach($value_options as $key=>$value)
		{
			$select .= "<option ";
			if ($value != null)
			{
				$select .= "value='" . $key . "'";
			}
			else
			{
				$select .= "value=''";
			}
			if ($selected == $key) $select .= " SELECTED";
			
			$select .= ">";
			$select .= $value;
			$select .= "</option> \n";
		}
		$select .= "</select> \n";
		
		return $select;
	}
	
	/**
	 * Builds and returns radio groups. 
	 * 
	 * @param object $name
	 * @param object $id
	 * @param object $value_labels associative string array label name [key] and value [value]
	 * @param object $checked[optional] value to be selected by default
	 * 
	 * @return string
	 */
	public function buildRadio($name, $id, $value_labels, $checked=null)
	{
		$selected = NULL;
		$radio = NULL;
		$count = 0;
		
		foreach ($value_labels as $label => $value)
		{
			$idplus = $id . '_' . $count;
			
			if ($checked == $value) $selected = 'CHECKED';
			
			$radio .= '<label for="' . $idplus . '">';
			$radio .= '<input id="' . $idplus . '" type="radio" name="' . $name . '" value="' . $value . '" ' . $selected . '>';
			$radio .= $label . '</label>';
			
			$selected = null;
			$idplus = null;
			$count = $count + 1;
		}
		
		return $radio;
	}
	
	/**
	 * Registers the entry edit form meta boxes
	 * 
	 * @param string $pageHook The page hook to add the entry edit metaboxes to.
	 */
	public function registerEditMetaboxes( $pageHook )
	{
		/*
		 * Interestingly if either 'submitdiv' or 'linksubmitdiv' is used as 
		 * the $id in the add_meta_box function it will show up as a meta box 
		 * that can not be hidden when the Screen Options tab is output via the 
		 * meta_box_prefs function
		 */
		add_meta_box('submitdiv', 'Publish', array(&$this, 'metaboxPublish'), $pageHook, 'side', 'core');
		//add_meta_box('metabox-fields', 'Fields', array(&$this, 'metaboxFields'), $pageHook, 'side', 'core');
		add_meta_box('categorydiv', 'Categories', array(&$this, 'metaboxCategories'), $pageHook, 'side', 'core');
		//add_meta_box('metabox-name', 'Name', array(&$this, 'metaboxName'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-image', 'Image', array(&$this, 'metaboxImage'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-logo', 'Logo', array(&$this, 'metaboxLogo'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-address', 'Addresses', array(&$this, 'metaboxAddress'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-phone', 'Phone Numbers', array(&$this, 'metaboxPhone'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-email', 'Email Addresses', array(&$this, 'metaboxEmail'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-messenger', 'Messenger IDs', array(&$this, 'metaboxMessenger'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-social-media', 'Social Media IDs', array(&$this, 'metaboxSocialMedia'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-links', 'Links', array(&$this, 'metaboxLinks'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-birthday', 'Birthday', array(&$this, 'metaboxBirthday'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-anniversary', 'Anniversary', array(&$this, 'metaboxAnniversary'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-bio', 'Biographical Info', array(&$this, 'metaboxBio'), $pageHook, 'normal', 'core');
		add_meta_box('metabox-note', 'Notes', array(&$this, 'metaboxNotes'), $pageHook, 'normal', 'core');
	}
	
	/**
	 * Registers the Dashboard meta boxes
	 * 
	 * @since 0.7.1.6
	 */
	public function registerDashboardMetaboxes()
	{
		global $connections;
		
		add_meta_box('metabox-news', 'News', array(&$this, 'metaboxNews'), $connections->pageHook->dashboard, 'left', 'core', array('feed' => 'http://connections-pro.com/category/connections/feed/') );
		add_meta_box('metabox-upgrade-modules', 'Pro Modules Update Notices', array(&$this, 'metaboxNews'), $connections->pageHook->dashboard, 'left', 'core', array('feed' => 'http://feeds.feedburner.com/ConnectionsProModules') );
		add_meta_box('metabox-upgrade-templates', 'Template Update Notices', array(&$this, 'metaboxNews'), $connections->pageHook->dashboard, 'left', 'core', array('feed' => 'http://feeds.feedburner.com/ConnectionsTemplates') );
		
		add_meta_box('metabox-recent-added', 'Recently Added', array(&$this, 'metaboxRecentAdded'), $connections->pageHook->dashboard, 'left', 'core');
		add_meta_box('metabox-recent-modified', 'Recently Modified', array(&$this, 'metaboxRecentModified'), $connections->pageHook->dashboard, 'left', 'core');
		
		add_meta_box('metabox-quick-links', 'Quick Links', array(&$this, 'metaBoxButtons'), $connections->pageHook->dashboard, 'right', 'core');
		add_meta_box('metabox-anniversary-today', 'Today\'s Anniversaries', array(&$this, 'metaboxAnniversaryToday'), $connections->pageHook->dashboard, 'right', 'core');
		add_meta_box('metabox-birthday-today', 'Today\'s Birthdays', array(&$this, 'metaboxBirthdayToday'), $connections->pageHook->dashboard, 'right', 'core');
		add_meta_box('metabox-anniversary-upcoming', 'Upcoming Anniversaries', array(&$this, 'metaboxAnniversaryUpcoming'), $connections->pageHook->dashboard, 'right', 'core');
		add_meta_box('metabox-birthday-upcoming', 'Upcoming Birthdays', array(&$this, 'metaboxBirthdayUpcoming'), $connections->pageHook->dashboard, 'right', 'core');
		
		add_meta_box('metabox-system', 'System', array(&$this, 'metaboxSystem'), $connections->pageHook->dashboard, 'right', 'core');
	}
	
	public function metaBoxButtons()
	{
		?>
		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/connections/connections-pro/"><span>Pro Modules</span></a>
			</p>
		</div>
		
		<div class="two-third last">
			<p>Extend Connections with the Pro Module Addons.</p>
		</div>
		<div class="clearboth"></div>
		
		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/connections/templates/"><span>Templates</span></a>
			</p>
		</div>
		
		<div class="two-third last">
			<p>Connections comes with several templates to get you started, something, something, bla, bla.</p>
		</div>
		<div class="clearboth"></div>
		
		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/trac/?bugcatid=16&amp;bugtypeid=74"><span>Bug Report</span></a>
			</p>
		</div>
		
		<div class="two-third last">
			<p>Dig find a bug, please take the time to report it. Thanks.</p>
		</div>
		<div class="clearboth"></div>
		
		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/trac/?bugcatid=16&amp;bugtypeid=75"><span>Feature Request</span></a>
			</p>
		</div>
		
		<div class="two-third last">
			<p>Need a feature, request the feature.</p>
		</div>
		<div class="clearboth"></div>
		
		<div class="one-third">
			<p class="center">
				<a class="button large blue full" href="http://connections-pro.com/connections/faq/"><span>FAQs</span></a>
			</p>
		</div>
		
		<div class="two-third last">
			<p>Have a question, maybe someone else had the same question, please check the FAQs.</p>
		</div>
		<div class="clearboth"></div>
		<?php
	}
	
	/**
	 * Outputs Connections Blog/News Feed.
	 * 
	 * @author Alex Rabe (http://alexrabe.de/)
	 * @since 0.7.1.6
	 */
	public function metaboxNews($post, $metabox)
	{
		?>
		<div class="rss-widget">
		    <?php
		    $rss = @fetch_feed( $metabox['args']['feed'] );
		      
		    if ( is_object($rss) ) {
		
		        if ( is_wp_error($rss) )
				{
		            echo '<p>' , sprintf(__('Newsfeed could not be loaded.  Check the <a href="%s">blog</a> to check for updates.', 'connections'), $metabox['args']['feed']) , '</p>';
		    		echo '</div>'; //close out the rss-widget before returning.
					return;
		        }
				elseif ( $rss->get_item_quantity() > 0 	)
				{
			        echo '<ul>';
					foreach ( $rss->get_items(0, 3) as $item )
					{
			    		$link = $item->get_link();
			    		while ( stristr($link, 'http') != $link )
			    			$link = substr($link, 1);
			    		$link = esc_url(strip_tags($link));
			    		$title = esc_attr(strip_tags($item->get_title()));
			    		if ( empty($title) )
			    			$title = __('Untitled');
			    
			    		$desc = str_replace( array("\n", "\r"), ' ', esc_attr( strip_tags( @html_entity_decode( $item->get_description(), ENT_QUOTES, get_option('blog_charset') ) ) ) );
			    		$desc = wp_html_excerpt( $desc, 360 );
			    
			    		// Append ellipsis. Change existing [...] to [&hellip;].
			    		/*if ( '[...]' == substr( $desc, -5 ) )
			    			$desc = substr( $desc, 0, -5 ) . '[&hellip;]';
			    		elseif ( '[&hellip;]' != substr( $desc, -10 ) )
			    			$desc .= ' [&hellip;]';*/
			    
			    		$desc = esc_html( $desc );
			            
						$date = $item->get_date();
			            $diff = '';
			            
						if ( $date ) {
						    
			                $diff = human_time_diff( strtotime($date, time()) );
			                 
							if ( $date_stamp = strtotime( $date ) )
								$date = ' <span class="rss-date">' . date_i18n( get_option( 'date_format' ), $date_stamp ) . '</span>';
							else
								$date = '';
						}            
			        ?>
			          <li>
			          	<h4 class="rss-title"><a title="" href='<?php echo $link; ?>'><?php echo $title; ?></a></h4>
					  	<div class="rss-date"><?php echo $date; ?></div> 
			          	<div class="rss-summary"><strong><?php echo $diff; ?> ago</strong> - <?php echo $desc; ?></div>
					  </li>
			        <?php
			        }
			        echo '</ul>';
				}
				else
				{
					echo '<p>No updates at this time</p>';
				}
		      }
		    ?>
		</div>
		<?php
	}	
	
	/**
	 * Outputs the Dashboard Today's Birthday Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxBirthdayToday($data = NULL)
	{
		$message = create_function('', 'return "No Birthdays Today";');
		add_filter( 'cn_upcoming_no_result_message', $message );
		
		$atts = array(
				'list_type' => 'birthday',
				'days' => '0',
				'private_override' => FALSE,
				'date_format' => 'F jS',
				'show_lastname' => TRUE,
				'list_title' => NULL,
				'show_title' => FALSE,
				'template' => 'dashboard-upcoming'
				);
		
		connectionsUpcomingList($atts);
		
		remove_filter( 'cn_upcoming_no_result_message', $message );
	}
	
	/**
	 * Outputs the Dashboard Upcoming Birthdays Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxBirthdayUpcoming($data = NULL)
	{
		$atts = array(
				'list_type' => 'birthday',
				'days' => '30',
				'include_today' => FALSE,
				'private_override' => FALSE,
				'date_format' => 'F jS',
				'show_lastname' => TRUE,
				'list_title' => NULL,
				'show_title' => FALSE,
				'template' => 'dashboard-upcoming'
				);
		
		connectionsUpcomingList($atts);
	}
	
	/**
	 * Outputs the Dashboard Today's Anniversary Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxAnniversaryToday($data = NULL)
	{
		$message = create_function('', 'return "No Anniversaries Today";');
		add_filter( 'cn_upcoming_no_result_message', $message );
		
		$atts = array(
				'list_type' => 'anniversary',
				'days' => '0',
				'private_override' => FALSE,
				'date_format' => 'F jS',
				'show_lastname' => TRUE,
				'list_title' => NULL,
				'show_title' => FALSE,
				'template' => 'dashboard-upcoming'
				);
		
		connectionsUpcomingList($atts);
		
		remove_filter( 'cn_upcoming_no_result_message', $message );
	}
	
	/**
	 * Outputs the Dashboard Upcoming Anniversary Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxAnniversaryUpcoming($data = NULL)
	{
		$atts = array(
				'list_type' => 'anniversary',
				'days' => '30',
				'include_today' => FALSE,
				'private_override' => FALSE,
				'date_format' => 'F jS',
				'show_lastname' => TRUE,
				'list_title' => NULL,
				'show_title' => FALSE,
				'template' => 'dashboard-upcoming'
				);
		
		connectionsUpcomingList($atts);
	}
	
	/**
	 * Outputs the Dashboard Recently Added Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxRecentAdded($data = NULL)
	{
		global $connections;
		
		add_filter( 'cn_list_results', array($connections->retrieve, 'removeUnknownDateAdded'), 9 );
		add_filter( 'cn_list_results', array($connections->retrieve, 'limitList'), 10 );
		
		$atts = array(
				'order_by' => 'date_added|SORT_DESC',
				'template' => 'dashboard-recent-added'
				);
		
		connectionsEntryList($atts);
		
		remove_filter( 'cn_list_results', array($connections->retrieve, 'removeUnknownDateAdded'), 9 );
	}
	
	/**
	 * Outputs the Dashboard Recently Modified Widget.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $data
	 */
	public function metaboxRecentModified($data = NULL)
	{
		global $connections;
		
		add_filter( 'cn_list_results', array($connections->retrieve, 'limitList'), 10 );
		
		$atts = array(
				'order_by' => 'date_modified|SORT_DESC',
				'template' => 'dashboard-recent-modified'
				);
		
		connectionsEntryList($atts);
	}
	
	/**
	 * Outputs the Server information.
	 * 
	 * @author GamerZ (http://www.lesterchan.net) && Alex Rabe (http://alexrabe.de/)
	 * @since 0.7.1.6
	 */
	public function metaboxSystem()
	{
		global $wpdb, $connections;
		$convert = new cnFormatting();
		
		// Get MYSQL Version
		$sqlversion = $wpdb->get_var("SELECT VERSION() AS version");
		// GET SQL Mode
		$mysqlinfo = $wpdb->get_results("SHOW VARIABLES LIKE 'sql_mode'");
		if (is_array($mysqlinfo)) $sql_mode = $mysqlinfo[0]->Value;
		if (empty($sql_mode)) $sql_mode = __('Not set', 'connections');
		// Get PHP Safe Mode
		if(ini_get('safe_mode')) $safe_mode = __('On', 'connections');
		else $safe_mode = __('Off', 'connections');
		// Get PHP allow_url_fopen
		if(ini_get('allow_url_fopen')) $allow_url_fopen = __('On', 'connections');
		else $allow_url_fopen = __('Off', 'connections'); 
		// Get PHP Max Upload Size
		if(ini_get('upload_max_filesize')) $upload_max = ini_get('upload_max_filesize');	
		else $upload_max = __('N/A', 'connections');
		// Get PHP Output buffer Size
		if(ini_get('pcre.backtrack_limit')) $backtrack_limit = ini_get('pcre.backtrack_limit');	
		else $backtrack_limit = __('N/A', 'connections');
		// Get PHP Max Post Size
		if(ini_get('post_max_size')) $post_max = ini_get('post_max_size');
		else $post_max = __('N/A', 'connections');
		// Get PHP Max execution time
		if(ini_get('max_execution_time')) $max_execute = ini_get('max_execution_time');
		else $max_execute = __('N/A', 'connections');
		// Get PHP Memory Limit 
		if(ini_get('memory_limit')) $memory_limit = $connections->phpMemoryLimit;
		else $memory_limit = __('N/A', 'connections');
		// Get actual memory_get_usage
		if (function_exists('memory_get_usage')) $memory_usage = round(memory_get_usage() / 1024 / 1024, 2) . __(' MByte', 'connections');
		else $memory_usage = __('N/A', 'connections');
		// required for EXIF read
		if (is_callable('exif_read_data')) $exif = __('Yes', 'connections'). " ( V" . substr(phpversion('exif'),0,4) . ")" ;
		else $exif = __('No', 'connections');
		// required for meta data
		if (is_callable('iptcparse')) $iptc = __('Yes', 'connections');
		else $iptc = __('No', 'connections');
		// required for meta data
		if (is_callable('xml_parser_create')) $xml = __('Yes', 'connections');
		else $xml = __('No', 'connections');
			
		?>
			<h4>Server Configuration</h4>
			
			<ul class="settings">
				<li><?php _e('Operating System', 'connections'); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo (PHP_INT_SIZE * 8) ?>&nbsp;Bit)</span></li>
				<li><?php _e('Server', 'connections'); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
				<li><?php _e('Memory usage', 'connections'); ?> : <span><?php echo $memory_usage; ?></span></li>
				<li><?php _e('MYSQL Version', 'connections'); ?> : <span><?php echo $sqlversion; ?></span></li>
				<li><?php _e('SQL Mode', 'connections'); ?> : <span><?php echo $sql_mode; ?></span></li>
				<li><?php _e('PHP Version', 'connections'); ?> : <span><?php echo PHP_VERSION; ?></span></li>
				<li><?php _e('PHP Safe Mode', 'connections'); ?> : <span><?php echo $safe_mode; ?></span></li>
				<li><?php _e('PHP Allow URL fopen', 'connections'); ?> : <span><?php echo $allow_url_fopen; ?></span></li>
				<li><?php _e('PHP Memory Limit', 'connections'); ?> : <span><?php echo $memory_limit; ?></span></li>
				<li><?php _e('PHP Max Upload Size', 'connections'); ?> : <span><?php echo $upload_max; ?></span></li>
				<li><?php _e('PHP Max Post Size', 'connections'); ?> : <span><?php echo $post_max; ?></span></li>
				<li><?php _e('PCRE Backtracking Limit', 'connections'); ?> : <span><?php echo $backtrack_limit; ?></span></li>
				<li><?php _e('PHP Max Script Execute Time', 'connections'); ?> : <span><?php echo $max_execute; ?>s</span></li>
				<li><?php _e('PHP Exif support', 'connections'); ?> : <span><?php echo $exif; ?></span></li>
				<li><?php _e('PHP IPTC support', 'connections'); ?> : <span><?php echo $iptc; ?></span></li>
				<li><?php _e('PHP XML support', 'connections'); ?> : <span><?php echo $xml; ?></span></li>
			</ul>
			
			<h4><strong><?php _e('Graphic Library', 'nggallery'); ?></strong></h4>
            
		<?php
		
		if(function_exists("gd_info"))
		{
			$info = gd_info();
			$keys = array_keys($info);
			echo '<ul class="settings">';
			for($i=0; $i<count($keys); $i++)
			{
				if(is_bool($info[$keys[$i]]))
					echo "<li> " . $keys[$i] ." : <span>" . $convert->toYesNo($info[$keys[$i]]) . "</span></li>\n";
				else
					echo "<li> " . $keys[$i] ." : <span>" . $info[$keys[$i]] . "</span></li>\n";
			}
			echo '</ul>';
		}
		else
		{
			echo '<h4>'.__('No GD support', 'connections').'!</h4>';
		}
		
		?>
		
		<h4>Folder Permissions</h4>
		
		<?php
		
		echo '<ul class="settings">';
			echo '<li>Image Path Exists: ' , $convert->toYesNo( is_dir(CN_IMAGE_PATH) ) , '</li>';
			if ( is_dir(CN_IMAGE_PATH) ) echo '<li>Image Path Writeable: ' , $convert->toYesNo( is_writeable(CN_IMAGE_PATH) ) , '</li>';
			
			echo '<li>Template Path Exists: ' , $convert->toYesNo( is_dir(CN_CUSTOM_TEMPLATE_PATH) ) , '</li>';
			if ( is_dir(CN_CUSTOM_TEMPLATE_PATH) ) echo '<li>Template Path Writeable: ' , $convert->toYesNo( is_writeable(CN_CUSTOM_TEMPLATE_PATH) ) , '</li>';
			
			echo '<li>Cache Path Exists: ' , $convert->toYesNo( is_dir(CN_CACHE_PATH) ) , '</li>';
			if ( is_dir(CN_CUSTOM_TEMPLATE_PATH) ) echo '<li>Cache Path Writeable: ' , $convert->toYesNo( is_writeable(CN_CACHE_PATH) ) , '</li>';
		echo '</ul>';
	}
	
	/**
	 * Outputs the publish meta box.
	 * 
	 * @since 0.7.1.6
	 * 
	 * @param array $entry
	 */
	public function metaboxPublish($entry = NULL)
	{
		if ( isset($_GET['action']) )
		{
			$action = esc_attr($_GET['action']);
		}
		else
		{
			$action = NULL;
		}
		
		( $entry->getVisibility() ) ? $visibility = $entry->getVisibility() : $visibility = 'unlisted';
		( $entry->getEntryType() ) ? $type = $entry->getEntryType() : $type = 'individual';
		
		
		echo '<div id="minor-publishing">';
			echo '<div id="entry-type">';
				echo $this->buildRadio("entry_type","entry_type",array("Individual"=>"individual","Organization"=>"organization","Family"=>"family"), $type);
			echo '</div>';
			
			if ( current_user_can('connections_edit_entry') )
			{
				echo '<div id="visibility">';
					echo '<span class="radio_group">' . $this->buildRadio('visibility','vis',array('Public'=>'public','Private'=>'private','Unlisted'=>'unlisted'), $visibility) . '</span>';
					echo '<div class="clear"></div>';
				echo '</div>';
			}
		echo '</div>';
		
		echo '<div id="major-publishing-actions">';
			
			switch ($action)
			{
				case 'edit':
					echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">Cancel</a></div><div id="publishing-action"><input  class="button-primary" type="submit" name="update" value="Update" /></div>';
				break;
				
				case 'copy':
					echo '<div id="cancel-button"><a href="admin.php?page=connections_manage" class="button button-warning">Cancel</a></div><div id="publishing-action"><input class="button-primary" type="submit" name="save" value="Add Entry" /></div>';;
				break;
				
				default:
					echo '<div id="publishing-action"><input class="button-primary" type="submit" name="save" value="Add Entry" /></div>';
				break;
			}
									
			echo '<div class="clear"></div>';
		echo '</div>';
	}
	
	/**
	 * Outputs the add field meta box. NOT USED
	 * 
	 * @since 0.7.1.5
	 */
	public function metaboxFields()
	{
		echo '<p><a id="add_address" class="button">Add Address</a></p>';
		echo '<p><a id="add_phone_number" class="button">Add Phone Number</a></p>';
		echo '<p><a id="add_email_address" class="button">Add Email Address</a></p>';
		echo '<p><a id="add_im_id" class="button">Add Messenger ID</a></p>';
		echo '<p><a id="add_social_media" class="button">Add Social Media ID</a></p>';
		echo '<p><a id="add_website_address" class="button">Add Website Address</a></p>';
	}
	
	/**
	 * Outputs the category meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxCategories($entry = NULL)
	{
		global $connections;
		
		$categoryObjects = new cnCategoryObjects();
		
		echo '<div class="categorydiv" id="taxonomy-category">';
			echo '<div id="category-all" class="tabs-panel">';
				echo '<ul id="categorychecklist">';
					echo $categoryObjects->buildCategoryRow('checklist', $connections->retrieve->categories(), NULL, $connections->term->getTermRelationships($entry->getId()));
				echo '</ul>';
			echo '</div>';
		echo '</div>';
	}
	
	/**
	 * Outputs the name meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxName($entry = NULL)
	{
		global $connections;
		
		echo '<div id="family" class="form-field">';
				
					echo '<label for="family_name">Family Name:</label>';
					echo '<input type="text" name="family_name" value="' . $entry->getFamilyName() . '" />';
					echo '<div id="relations">';
							
						// --> Start template for Connection Group <-- \\
						echo '<textarea id="relation_row_base" style="display: none">';
							echo $this->getEntrySelect('family_member[::FIELD::][entry_id]');
							echo $this->buildSelect('family_member[::FIELD::][relation]', $connections->options->getDefaultFamilyRelationValues());
						echo '</textarea>';
						// --> End template for Connection Group <-- \\
						
						if ($entry->getFamilyMembers())
						{
							foreach ($entry->getFamilyMembers() as $key => $value)
							{
								$relation = new cnEntry();
								$relation->set($key);
								$token = $this->token($relation->getId());
								
								echo '<div id="relation_row_' . $token . '" class="relation_row">';
									echo $this->getEntrySelect('family_member[' . $token . '][entry_id]', $key);
									echo $this->buildSelect('family_member[' . $token . '][relation]', $connections->options->getDefaultFamilyRelationValues(), $value);
									echo '<a href="#" id="remove_button_' . $token . '" class="button button-warning" onClick="removeEntryRow(\'#relation_row_' . $token . '\'); return false;">Remove</a>';
								echo '</div>';
								
								unset($relation);
							}
						}						
						
					echo '</div>';
					echo '<p class="add"><a id="add_relation" class="button">Add Relation</a></p>';
					
				echo '
			</div>
			
			<div class="form-field namefield">
					<div class="">';
						
						echo '
						<div style="float: left; width: 8%">
							<label for="honorific_prefix">Prefix:</label>
							<input type="text" name="honorific_prefix" value="' . $entry->getHonorificPrefix() . '" />
						</div>';
					
						echo '
						<div style="float: left; width: 30%">
							<label for="first_name">First Name:</label>
							<input type="text" name="first_name" value="' . $entry->getFirstName() . '" />
						</div>
						
						<div style="float: left; width: 24%">
							<label for="middle_name">Middle Name:</label>
							<input type="text" name="middle_name" value="' . $entry->getMiddleName() . '" />
						</div>
					
						<div style="float: left; width: 30%">
							<label for="last_name">Last Name:</label>
							<input type="text" name="last_name" value="' . $entry->getLastName() . '" />
						</div>';
					
						echo '
						<div style="float: left; width: 8%">
							<label for="honorific_suffix">Suffix:</label>
							<input type="text" name="honorific_suffix" value="' . $entry->getHonorificSuffix() . '" />
						</div>';
						
						echo '
						<label for="title">Title:</label>
						<input type="text" name="title" value="' . $entry->getTitle() . '" />
					</div>
				</div>
				
				<div class="form-field">
					<div class="organization">
						<label for="organization">Organization:</label>
						<input type="text" name="organization" value="' . $entry->getOrganization() . '" />
						
						<label for="department">Department:</label>
						<input type="text" name="department" value="' . $entry->getDepartment() . '" />';
						
						echo '
						<div id="contact_name">
							<div class="input inputhalfwidth">
								<label for="contact_first_name">Contact First Name:</label>
								<input type="text" name="contact_first_name" value="' . $entry->getContactFirstName() . '" />
							</div>
							<div class="input inputhalfwidth">
								<label for="contact_last_name">Contact Last Name:</label>
								<input type="text" name="contact_last_name" value="' . $entry->getContactLastName() . '" />
							</div>
							
							<div class="clear"></div>
						</div>';
					echo '
					</div>
			</div>';
	}
	
	/**
	 * Outputs the image meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxImage($entry = NULL)
	{
		echo '<div class="form-field">';
					
			if ( $entry->getImageLinked() )
			{
				( $entry->getImageDisplay() ) ? $selected = 'show' : $selected = 'hidden';
				
				$imgOptions = $this->buildRadio('imgOptions', 'imgOptionID_', array('Display'=>'show', 'Not Displayed'=>'hidden', 'Remove'=>'remove'), $selected);
				echo '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getImageNameProfile() . '" /> <br /> <span class="radio_group">' . $imgOptions . '</span></div> <br />';
			}
			
			echo '<div class="clear"></div>';
			echo '<label for="original_image">Select Image:
			<input type="file" value="" name="original_image" size="25" /></label>
				
		</div>';
	}
	
	/**
	 * Outputs the logo meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxLogo($entry = NULL)
	{
		echo '<div class="form-field">';
					
			if ( $entry->getLogoLinked() )
			{
				( $entry->getLogoDisplay() ) ? $selected = 'show' : $selected = 'hidden';
				
				$logoOptions = $this->buildRadio('logoOptions', 'logoOptionID_', array('Display'=>'show', 'Not Displayed'=>'hidden', 'Remove'=>'remove'), $selected);
				echo '<div style="text-align: center;"> <img src="' . CN_IMAGE_BASE_URL . $entry->getLogoName() . '" /> <br /> <span class="radio_group">' . $logoOptions . '</span></div> <br />'; 
			}
			
			echo '<div class="clear"></div>';
			echo '<label for="original_logo">Select Logo:
			<input type="file" value="" name="original_logo" size="25" /></label>
			
		</div>';
	}
	
	/**
	 * Outputs the address meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxAddress( &$entry = NULL )
	{
		global $connections;
			
		echo  '<div class="widgets-sortables ui-sortable form-field" id="addresses">' , "\n";
			
			// --> Start template <-- \\
			echo  '<textarea id="address_row_base" style="display: none;">' , "\n";
				
				echo '<div class="widget-top">' , "\n";
					echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
					
					echo '<div class="widget-title"><h4>' , "\n";
						echo 'Address Type: ' , $this->buildSelect('address[::FIELD::][type]', $connections->options->getDefaultAddressValues() ) , "\n";
						echo '<label><input type="radio" name="address[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
						echo '<span class="visibility">Visibility: ' , $this->buildRadio('address[::FIELD::][visibility]', 'address_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
					echo '</h4></div>'  , "\n";
					
				echo '</div>' , "\n";
				
				echo '<div class="widget-inside">';
				
					echo '<div class="address-local">';
						echo '<div class="address-line">';
							echo  '<label for="address">Address Line 1:</label>';
							echo  '<input type="text" name="address[::FIELD::][line_1]" value="">';
						echo  '</div>';
						
						echo '<div class="address-line">';
							echo  '<label for="address">Address Line 2:</label>';
							echo  '<input type="text" name="address[::FIELD::][line_2]" value="">';
						echo  '</div>';
						
						echo '<div class="address-line">';
							echo  '<label for="address">Address Line 3:</label>';
							echo  '<input type="text" name="address[::FIELD::][line_3]" value="">';
						echo  '</div>';
						
					echo  '</div>';
					
					echo '<div class="address-region">';
						echo  '<div class="input address-city">';
							echo  '<label for="address">City:</label>';
							echo  '<input type="text" name="address[::FIELD::][city]" value="">';
						echo  '</div>';
						echo  '<div class="input address-state">';
							echo  '<label for="address">State:</label>';
							echo  '<input type="text" name="address[::FIELD::][state]" value="">';
						echo  '</div>';
						echo  '<div class="input address-zipcode">';
							echo  '<label for="address">Zipcode:</label>';
							echo  '<input type="text" name="address[::FIELD::][zipcode]" value="">';
						echo  '</div>';
					echo  '</div>';
					
					echo '<div class="address-country">';
						echo  '<label for="address">Country</label>';
						echo  '<input type="text" name="address[::FIELD::][country]" value="">';
					echo  '</div>';
					
					echo '<div class="address-geo">';
						echo  '<div class="input address-latitude">';
							echo  '<label for="latitude">Latitude</label>';
							echo  '<input type="text" name="address[::FIELD::][latitude]" value="">';
						echo  '</div>';
						echo  '<div class="input address-longitude">';
							echo  '<label for="longitude">Longitude</label>';
							echo  '<input type="text" name="address[::FIELD::][longitude]" value="">';
						echo  '</div>';
					echo  '</div>';
					
					echo  '<div class="clear"></div>';
					echo  '<br>';
					echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#address_row_::FIELD::\'); return false;">Remove</a></p>';
				
				echo  '</div>' , "\n";
				
			echo  '</textarea>' , "\n";
			// --> End template <-- \\
			
			
			$addresses = $entry->getAddresses( array(), FALSE );
			//print_r($addresses);
			
			if ( ! empty($addresses) )
			{
				foreach ( $addresses as $address )
				{
					$token = $this->token( $entry->getId() );
					$selectName = 'address['  . $token . '][type]';
					( $address->preferred ) ? $preferredAddress = 'CHECKED' : $preferredAddress = '';
					
					echo '<div class="widget address" id="address_row_'  . $token . '">' , "\n";
						echo '<div class="widget-top">' , "\n";
							echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
							
							echo '<div class="widget-title"><h4>' , "\n";
								echo 'Address Type: ' , $this->buildSelect($selectName, $connections->options->getDefaultAddressValues(), $address->type) , "\n";
								echo '<label><input type="radio" name="address[preferred]" value="' , $token , '" ' , $preferredAddress , '> Preferred</label>' , "\n";
								echo '<span class="visibility">Visibility: ' , $this->buildRadio('address[' . $token . '][visibility]', 'address_visibility_'  . $token , $this->visibiltyOptions, $address->visibility) , '</span>' , "\n";
							echo '</h4></div>'  , "\n";
							
						echo '</div>' , "\n";
					
						echo '<div class="widget-inside">' , "\n";
							
							echo '<div class="address-local">' , "\n";
								echo '<div class="address-line">' , "\n";
									echo  '<label for="address">Address Line 1:</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][line_1]" value="' , $address->line_1 , '">' , "\n";
								echo '</div>' , "\n";
									
								echo '<div class="address-line">' , "\n";
									echo  '<label for="address">Address Line 2:</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][line_2]" value="' , $address->line_2 , '">' , "\n";
								echo '</div>' , "\n";
									
								echo '<div class="address-line">' , "\n";
									echo  '<label for="address">Address Line 3:</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][line_3]" value="' , $address->line_3 , '">' , "\n";
								echo '</div>' , "\n";
							echo '</div>' , "\n";
							
							echo '<div class="address-region">' , "\n";
								echo  '<div class="input address-city">' , "\n";
									echo  '<label for="address">City:</label>';
									echo  '<input type="text" name="address[' , $token . '][city]" value="' , $address->city , '">' , "\n";
								echo  '</div>' , "\n";
								echo  '<div class="input address-state">' , "\n";
									echo  '<label for="address">State:</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][state]" value="' , $address->state , '">' , "\n";
								echo  '</div>' , "\n";
								echo  '<div class="input address-zipcode">' , "\n";
									echo  '<label for="address">Zipcode:</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][zipcode]" value="' , $address->zipcode , '">' , "\n";
								echo  '</div>' , "\n";
							echo  '</div>' , "\n";
							
							echo '<div class="address-country">' , "\n";
								echo  '<label for="address">Country</label>' , "\n";
								echo  '<input type="text" name="address[' , $token , '][country]" value="' , $address->country , '">' , "\n";
							echo  '</div>' , "\n";
							
							echo '<div class="address-geo">' , "\n";
								echo  '<div class="input address-latitude">' , "\n";
									echo  '<label for="latitude">Latitude</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][latitude]" value="' , $address->latitude , '">' , "\n";
								echo  '</div>' , "\n";
								echo  '<div class="input address-longitude">' , "\n";
									echo  '<label for="longitude">Longitude</label>' , "\n";
									echo  '<input type="text" name="address[' , $token , '][longitude]" value="' , $address->longitude , '">' , "\n";
								echo  '</div>' , "\n";
							echo  '</div>' , "\n";
							
							echo  '<input type="hidden" name="address[' , $token , '][id]" value="' , $address->id , '">' , "\n";
						
							echo  '<div class="clear"></div>' , "\n";
							
							echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#address_row_' , $token , '\'); return false;">Remove</a></p>' , "\n";
							
						echo  '</div>' , "\n";
					echo  '</div>' , "\n";
					
				}
			}
			
		echo  '</div>' , "\n";
		echo  '<p class="add"><a id="add_address" class="button">Add Address</a></p>' , "\n";
	}
	
	/**
	 * Outputs the phone meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxPhone($entry = NULL)
	{
		global $connections;
		
		echo  '<div class="widgets-sortables ui-sortable form-field" id="phone-numbers">';
			
			// --> Start template <-- \\
			echo  '<textarea id="phone_number_row_base" style="display: none">';
				
				echo '<div class="widget-top">' , "\n";
					echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
					
					echo '<div class="widget-title"><h4>' , "\n";
						echo 'Phone Type: ' , $this->buildSelect('phone[::FIELD::][type]', $connections->options->getDefaultPhoneNumberValues() ) , "\n";
						echo '<label><input type="radio" name="phone[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
						echo '<span class="visibility">Visibility: ' , $this->buildRadio('phone[::FIELD::][visibility]', 'phone_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
					echo '</h4></div>'  , "\n";
					
				echo '</div>' , "\n";
				
				echo '<div class="widget-inside">' , "\n";
				
					echo  '<label>Phone Number</label><input type="text" name="phone[::FIELD::][number]" value="" style="width: 30%"/>' , "\n";
					echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#phone-row-::FIELD::\'); return false;">Remove</a></p>' , "\n";
					
				echo '</div>' , "\n";
				
			echo  '</textarea>';
			// --> End template <-- \\
			
			$phoneNumbers = $entry->getPhoneNumbers( array(), FALSE );
			
			if ( ! empty($phoneNumbers) )
			{
				
				foreach ($phoneNumbers as $phone)
				{
					$token = $this->token( $entry->getId() );
					$selectName = 'phone['  . $token . '][type]';
					( $phone->preferred ) ? $preferredPhone = 'CHECKED' : $preferredPhone = '';
					
					echo '<div class="widget phone" id="phone-row-'  . $token . '">' , "\n";
						echo '<div class="widget-top">' , "\n";
							echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
							
							echo '<div class="widget-title"><h4>' , "\n";
								echo 'Phone Type: ' , $this->buildSelect($selectName, $connections->options->getDefaultPhoneNumberValues(), $phone->type) , "\n";
								echo '<label><input type="radio" name="phone[preferred]" value="' , $token , '" ' , $preferredPhone , '> Preferred</label>' , "\n";
								echo '<span class="visibility">Visibility: ' , $this->buildRadio('phone[' . $token . '][visibility]', 'phone_visibility_'  . $token , $this->visibiltyOptions, $phone->visibility) , '</span>' , "\n";
							echo '</h4></div>'  , "\n";
							
						echo '</div>' , "\n";
					
						echo '<div class="widget-inside">' , "\n";
					
							echo  '<label>Phone Number</label><input type="text" name="phone[' , $token , '][number]" value="' , $phone->number , '" style="width: 30%"/>';
							echo  '<input type="hidden" name="phone[' , $token , '][id]" value="' , $phone->id , '">' , "\n";
							echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#phone-row-' , $token , '\'); return false;">Remove</a></p>';
							
						echo '</div>' , "\n";
					echo '</div>' , "\n";
				}
				
			}
			
		echo  '</div>';
		echo  '<p class="add"><a id="add_phone_number" class="button">Add Phone Number</a></p>';
	}
	
	/**
	 * Outputs the email meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxEmail($entry = NULL)
	{
		global $connections;
		
		echo  '<div class="widgets-sortables ui-sortable form-field" id="email-addresses">';
			
			// --> Start template <-- \\
			echo  '<textarea id="email_address_row_base" style="display: none">';
				
				echo '<div class="widget-top">' , "\n";
					echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
					
					echo '<div class="widget-title"><h4>' , "\n";
						echo 'Email Type: ' , $this->buildSelect('email[::FIELD::][type]', $connections->options->getDefaultEmailValues() ) , "\n";
						echo '<label><input type="radio" name="email[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
						echo '<span class="visibility">Visibility: ' , $this->buildRadio('email[::FIELD::][visibility]', 'email_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
					echo '</h4></div>'  , "\n";
					
				echo '</div>' , "\n";
				
				echo '<div class="widget-inside">' , "\n";
				
					echo  '<label>Email Address</label><input type="text" name="email[::FIELD::][address]" value="" style="width: 30%"/>' , "\n";
					echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#email-row-::FIELD::\'); return false;">Remove</a></p>' , "\n";
					
				echo '</div>' , "\n";
				
			echo  '</textarea>';
			// --> End template <-- \\
			
			$emailAddresses = $entry->getEmailAddresses( array(), FALSE );
			
			if ( ! empty($emailAddresses) )
			{
				
				foreach ($emailAddresses as $email)
				{
					$token = $this->token( $entry->getId() );
					$selectName = 'email['  . $token . '][type]';
					( $email->preferred ) ? $preferredEmail = 'CHECKED' : $preferredEmail = '';
					
					echo '<div class="widget email" id="email-row-'  . $token . '">' , "\n";
						echo '<div class="widget-top">' , "\n";
							echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
							
							echo '<div class="widget-title"><h4>' , "\n";
								echo 'Email Type: ' , $this->buildSelect($selectName, $connections->options->getDefaultEmailValues(), $email->type) , "\n";
								echo '<label><input type="radio" name="email[preferred]" value="' , $token , '" ' , $preferredEmail , '> Preferred</label>' , "\n";
								echo '<span class="visibility">Visibility: ' , $this->buildRadio('email[' . $token . '][visibility]', 'email_visibility_'  . $token , $this->visibiltyOptions, $email->visibility) , '</span>' , "\n";
							echo '</h4></div>'  , "\n";
							
						echo '</div>' , "\n";
					
						echo '<div class="widget-inside">' , "\n";
					
							echo  '<label>Email Address</label><input type="text" name="email[' , $token , '][address]" value="' , $email->address , '" style="width: 30%"/>';
							echo  '<input type="hidden" name="email[' , $token , '][id]" value="' , $email->id , '">' , "\n";
							echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#email-row-' , $token , '\'); return false;">Remove</a></p>';
							
						echo '</div>' , "\n";
					echo '</div>' , "\n";
				}
				
			}
			
		echo  '</div>';
		echo  '<p class="add"><a id="add_email_address" class="button">Add Email Address</a></p>';
	}
	
	/**
	 * Outputs the messenger meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxMessenger($entry = NULL)
	{
		global $connections;
		
		echo  '<div class="widgets-sortables ui-sortable form-field" id="im-ids">';
			
			// --> Start template.  <-- \\
			echo  '<textarea id="im_row_base" style="display: none">';
				
				echo '<div class="widget-top">' , "\n";
					echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
					
					echo '<div class="widget-title"><h4>' , "\n";
						echo 'IM Type: ' , $this->buildSelect('im[::FIELD::][type]', $connections->options->getDefaultIMValues() ) , "\n";
						echo '<label><input type="radio" name="im[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
						echo '<span class="visibility">Visibility: ' , $this->buildRadio('im[::FIELD::][visibility]', 'im_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
					echo '</h4></div>'  , "\n";
					
				echo '</div>' , "\n";
				
				echo '<div class="widget-inside">' , "\n";
				
					echo  '<label>IM Network ID</label><input type="text" name="im[::FIELD::][id]" value="" style="width: 30%"/>' , "\n";
					echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#im-row-::FIELD::\'); return false;">Remove</a></p>' , "\n";
					
				echo '</div>' , "\n";
				
			echo  '</textarea>';
			// --> End template. <-- \\
			
			$imIDs = $entry->getIm( array(), FALSE );
			
			if ( ! empty($imIDs) )
			{
				foreach ($imIDs as $network)
				{
					$token = $this->token( $entry->getId() );
					$selectName = 'im['  . $token . '][type]';
					( $network->preferred ) ? $preferredIM = 'CHECKED' : $preferredIM = '';
					
					echo '<div class="widget im" id="im-row-'  . $token . '">' , "\n";
						echo '<div class="widget-top">' , "\n";
							echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
							
							echo '<div class="widget-title"><h4>' , "\n";
								echo 'IM Type: ' , $this->buildSelect($selectName, $connections->options->getDefaultIMValues(), $network->type) , "\n";
								echo '<label><input type="radio" name="im[preferred]" value="' , $token , '" ' , $preferredIM , '> Preferred</label>' , "\n";
								echo '<span class="visibility">Visibility: ' , $this->buildRadio('im[' . $token . '][visibility]', 'im_visibility_'  . $token , $this->visibiltyOptions, $network->visibility) , '</span>' , "\n";
							echo '</h4></div>'  , "\n";
							
						echo '</div>' , "\n";
					
						echo '<div class="widget-inside">' , "\n";
					
							echo  '<label>IM Network ID</label><input type="text" name="im[' , $token , '][id]" value="' , $network->id , '" style="width: 30%"/>';
							echo  '<input type="hidden" name="im[' , $token , '][uid]" value="' , $network->uid , '">' , "\n";
							echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#im-row-' , $token , '\'); return false;">Remove</a></p>';
							
						echo '</div>' , "\n";
					echo '</div>' , "\n";
				}
				
			}
			
		echo  '</div>';
		echo  '<p class="add"><a id="add_im_id" class="button">Add Messenger ID</a></p>';
	}
	
	/**
	 * Outputs the social media meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxSocialMedia($entry = NULL)
	{
		global $connections;
			
		echo  '<div class="widgets-sortables ui-sortable form-field" id="social-media">';
		
		// --> Start template <-- \\
		echo  '<textarea id="social_media_row_base" style="display: none">';
			
			echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
				
				echo '<div class="widget-title"><h4>' , "\n";
					echo 'Social Network: ' , $this->buildSelect('social[::FIELD::][type]', $connections->options->getDefaultSocialMediaValues() ) , "\n";
					echo '<label><input type="radio" name="social[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
					echo '<span class="visibility">Visibility: ' , $this->buildRadio('social[::FIELD::][visibility]', 'social_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";
				
			echo '</div>' , "\n";
			
			echo '<div class="widget-inside">' , "\n";
			
				echo  '<label>URL</label><input type="text" name="social[::FIELD::][url]" value="http://" style="width: 30%"/>' , "\n";
				echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#social-row-::FIELD::\'); return false;">Remove</a></p>' , "\n";
				
			echo '</div>' , "\n";
			
		echo  '</textarea>';
		// --> End template <-- \\
		
		$socialNetworks = $entry->getSocialMedia( array(), FALSE );
		
		if ( ! empty($socialNetworks) )
		{
			
			foreach ($socialNetworks as $network)
			{
				$token = $this->token( $entry->getId() );
				$selectName = 'social['  . $token . '][type]';
				( $network->preferred ) ? $preferredNetwork = 'CHECKED' : $preferredNetwork = '';
				
				echo '<div class="widget social" id="social-row-'  . $token . '">' , "\n";
					echo '<div class="widget-top">' , "\n";
						echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
						
						echo '<div class="widget-title"><h4>' , "\n";
							echo 'Social Network: ' , $this->buildSelect($selectName, $connections->options->getDefaultSocialMediaValues(), $network->type) , "\n";
							echo '<label><input type="radio" name="social[preferred]" value="' , $token , '" ' , $preferredNetwork , '> Preferred</label>' , "\n";
							echo '<span class="visibility">Visibility: ' , $this->buildRadio('social[' . $token . '][visibility]', 'social_visibility_'  . $token , $this->visibiltyOptions, $network->visibility) , '</span>' , "\n";
						echo '</h4></div>'  , "\n";
						
					echo '</div>' , "\n";
				
					echo '<div class="widget-inside">' , "\n";
				
						echo  '<label>URL</label><input type="text" name="social[' , $token , '][url]" value="' , $network->url , '" style="width: 30%"/>';
						echo  '<input type="hidden" name="social[' , $token , '][id]" value="' , $network->id , '">' , "\n";
						echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#social-row-' , $token , '\'); return false;">Remove</a></p>';
						
					echo '</div>' , "\n";
				echo '</div>' , "\n";
			}
			
		}
			
		echo  '</div>';
		echo  '<p class="add"><a id="add_social_media" class="button">Add Social Media ID</a></p>';
	}
	
	/**
	 * Outputs the image website box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxLinks($entry = NULL)
	{
		global $connections;
			
		echo  '<div class="widgets-sortables ui-sortable form-field" id="links">';
		
		// --> Start template <-- \\
		echo  '<textarea id="link_row_base" style="display: none">';
			
			echo '<div class="widget-top">' , "\n";
				echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
				
				echo '<div class="widget-title"><h4>' , "\n";
					echo 'Type: ' , $this->buildSelect('link[::FIELD::][type]', $connections->options->getDefaultLinkValues() ) , "\n";
					echo '<label><input type="radio" name="link[preferred]" value="::FIELD::"> Preferred</label>' , "\n";
					echo '<span class="visibility">Visibility: ' , $this->buildRadio('link[::FIELD::][visibility]', 'website_visibility_::FIELD::' , $this->visibiltyOptions, 'public' ) , '</span>' , "\n";
				echo '</h4></div>'  , "\n";
				
			echo '</div>' , "\n";
			
			echo '<div class="widget-inside">' , "\n";
				
				echo '<div>' , "\n";
					echo  '<label>Title</label><input type="text" name="link[::FIELD::][title]" value="" style="width: 30%"/>' , "\n";
					echo  '<label>URL</label><input type="text" name="link[::FIELD::][url]" value="http://" style="width: 30%"/>' , "\n";
				echo '</div>' , "\n";
				
				echo '<div>' , "\n";
					echo '<span class="target">Target: ' , $this->buildSelect('link[::FIELD::][target]', array( 'new' => 'New Window', 'same' => 'Same Window' ), 'same' ) , '</span>' , "\n";
					echo '<span class="follow">' , $this->buildSelect('link[::FIELD::][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), 'nofollow' ) , '</span>' , "\n";
				echo '</div>' , "\n";
				
				echo  '<p class="remove-button"><a href="#" id="remove_button_::FIELD::" class="button button-warning" onClick="removeEntryRow(\'#link-row-::FIELD::\'); return false;">Remove</a></p>' , "\n";
				
			echo '</div>' , "\n";
			
		echo  '</textarea>';
		// --> End template <-- \\
		
		$links = $entry->getLinks( array(), FALSE );
		
		if ( ! empty($links) )
		{
			
			foreach ( $links as $link )
			{
				$token = $this->token( $entry->getId() );
				$selectName = 'link['  . $token . '][type]';
				( $link->preferred ) ? $preferredLink = 'CHECKED' : $preferredLink = '';
				//var_dump($link);
				
				echo '<div class="widget link" id="link-row-'  . $token . '">' , "\n";
					echo '<div class="widget-top">' , "\n";
						echo '<div class="widget-title-action"><a class="widget-action"></a></div>' , "\n";
						
						echo '<div class="widget-title"><h4>' , "\n";
							echo 'Type: ' , $this->buildSelect($selectName, $connections->options->getDefaultLinkValues(), $link->type) , "\n";
							echo '<label><input type="radio" name="link[preferred]" value="' , $token , '" ' , $preferredLink , '> Preferred</label>' , "\n";
							echo '<span class="visibility">Visibility: ' , $this->buildRadio('link[' . $token . '][visibility]', 'link_visibility_'  . $token , $this->visibiltyOptions, $link->visibility ) , '</span>' , "\n";
						echo '</h4></div>'  , "\n";
						
					echo '</div>' , "\n";
				
					echo '<div class="widget-inside">' , "\n";
				
						echo '<div>' , "\n";
							echo  '<label>Title</label><input type="text" name="link[' , $token , '][title]" value="' , $link->title , '" style="width: 30%"/>' , "\n";
							echo  '<label>URL</label><input type="text" name="link[' , $token , '][url]" value="' , $link->url , '" style="width: 30%"/>';
						echo '</div>' , "\n";
						
						echo '<div>' , "\n";
							echo '<span class="target">Target: ' , $this->buildSelect('link[' . $token . '][target]', array( 'new' => 'New Window', 'same'  => 'Same Window' ), $link->target ) , '</span>' , "\n";
							echo '<span class="follow">' , $this->buildSelect('link[' . $token . '][follow]', array( 'nofollow' => 'nofollow', 'dofollow' => 'dofollow' ), $link->followString ) , '</span>' , "\n";
						echo '</div>' , "\n";
						
						echo  '<input type="hidden" name="link[' , $token , '][id]" value="' , $link->id , '">' , "\n";
						echo  '<p class="remove-button"><a href="#" id="remove_button_' , $token , '" class="button button-warning" onClick="removeEntryRow(\'#link-row-' , $token , '\'); return false;">Remove</a></p>';
						
					echo '</div>' , "\n";
				echo '</div>' , "\n";
			}
			
		}
			
		echo  '</div>';
		echo  '<p class="add"><a id="add_link" class="button">Add Link</a></p>';
	}
	
	/**
	 * Outputs the birthday meta box.
	 * 
	 * @since 0.7.1.58
	 * 
	 * @param array $entry
	 */
	public function metaboxBirthday($entry = NULL)
	{
		$date = new cnDate();
		
		echo "<div class='form-field celebrate'>
				<span class='selectbox'>Birthday: " . $this->buildSelect('birthday_month',$date->months,$date->getMonth($entry->getBirthday())) . "</span>
				<span class='selectbox'>" . $this->buildSelect('birthday_day',$date->days,$date->getDay($entry->getBirthday())) . "</span>
		</div>";
	}
	
	/**
	 * Outputs the anniversary meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxAnniversary($entry = NULL)
	{
		$date = new cnDate();
		
		echo "<div class='form-field celebrate'>
				<span class='selectbox'>Anniversary: " . $this->buildSelect('anniversary_month',$date->months,$date->getMonth($entry->getAnniversary())) . "</span>
				<span class='selectbox'>" . $this->buildSelect('anniversary_day',$date->days,$date->getDay($entry->getAnniversary())) . "</span>
		</div>";
	}
	
	/**
	 * Outputs the bio meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxBio($entry = NULL)
	{
		if( version_compare($GLOBALS['wp_version'], '3.2.999', '<') )
		{
			echo "<div class='form-field'>
					
					<a class='button alignright' id='toggleBioEditor'>Toggle Editor</a>
					
					<textarea class='tinymce' id='bio' name='bio' rows='15'>" . $entry->getBio() . "</textarea>
					
			</div>";
		}
		else
		{
			wp_editor(	$entry->getBio(),
						'bio',
						array
						(
							'media_buttons' => FALSE,
							'tinymce' =>	array
											(
												'editor_selector' => 'tinymce',
												'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
												'theme_advanced_buttons2' => '',
												'inline_styles' => TRUE,
												'relative_urls' => FALSE,
												'remove_linebreaks' => FALSE,
												'plugins' => 'inlinepopups,spellchecker,tabfocus,paste,wordpress,wpdialogs'
											)
						)
					 );
		}
		
	}
	
	/**
	 * Outputs the notes meta box.
	 * 
	 * @since 0.7.1.5
	 * 
	 * @param array $entry
	 */
	public function metaboxNotes($entry = NULL)
	{
		if( version_compare($GLOBALS['wp_version'], '3.2.999', '<') )
		{
			echo "<div class='form-field'>
					
					<a class='button alignright' id='toggleNoteEditor'>Toggle Editor</a>
					
					<textarea class='tinymce' id='note' name='notes' rows='15'>" . $entry->getNotes() . "</textarea>
					
			</div>";
		}
		else
		{
			wp_editor(	$entry->getNotes(),
						'notes',
						array
						(
							'media_buttons' => FALSE,
							'tinymce' =>	array
											(
												'editor_selector' => 'tinymce',
												'theme_advanced_buttons1' => 'bold, italic, underline, |, bullist, numlist, |, justifyleft, justifycenter, justifyright, |, link, unlink, |, pastetext, pasteword, removeformat, |, undo, redo',
												'theme_advanced_buttons2' => '',
												'inline_styles' => TRUE,
												'relative_urls' => FALSE,
												'remove_linebreaks' => FALSE,
												'plugins' => 'inlinepopups,spellchecker,tabfocus,paste,wordpress,wpdialogs'
											)
						)
					 );
		}
	}
	
	private function getEntrySelect($name, $selected = NULL)
	{
		global $wpdb, $connections;
		
		$atts['list_type'] = 'individual';
		$atts['category'] = NULL;
		$atts['visibility'] = NULL;
		
		$results = $connections->retrieve->entries($atts);
		
	    $out = '<select name="' . $name . '">';
			$out .= '<option value="">Select Entry</option>';
			foreach($results as $row)
			{
				$entry = new cnEntry($row);
				$out .= '<option value="' . $entry->getId() . '"';
				if ($selected == $entry->getId()) $out .= ' SELECTED';
				$out .= '>' . $entry->getFullLastFirstName() . '</option>';
			}
		$out .= '</select>';
		
		return $out;
	}
}


class cnCategoryObjects
{
	private $rowClass = '';
		
	public function buildCategoryRow($type, $parents, $level = 0, $selected = NULL)
	{
		$out = NULL;
		
		foreach ($parents as $child)
		{
			$category = new cnCategory($child);
			
			if ($type === 'table') $out .= $this->buildTableRowHTML($child, $level);
			if ($type === 'option') $out .= $this->buildOptionRowHTML($child, $level, $selected);
			if ($type === 'checklist') $out .= $this->buildCheckListHTML($child, $level, $selected);
			
			if (is_array($category->getChildren()))
			{
				++$level;
				if ($type === 'table') $out .= $this->buildCategoryRow('table', $category->getChildren(), $level);
				if ($type === 'option') $out .= $this->buildCategoryRow('option', $category->getChildren(), $level, $selected);
				if ($type === 'checklist') $out .= $this->buildCategoryRow('checklist', $category->getChildren(), $level, $selected);
				--$level;
			}
			
		}
		
		$level = 0;
		return $out;
	}
	
	private function buildTableRowHTML($term, $level)
	{
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory($term);
		$pad = str_repeat('&#8212; ', max(0, $level));
		$this->rowClass = 'alternate' == $this->rowClass ? '' : 'alternate';
		
		/*
		 * Genreate the edit & delete tokens.
		 */
		$editToken = $form->tokenURL('admin.php?page=connections_categories&action=edit&id=' . $category->getId(), 'category_edit_' . $category->getId());
		$deleteToken = $form->tokenURL('admin.php?connections_process=true&process=category&action=delete&id=' . $category->getId(), 'category_delete_' . $category->getId());
		
		$out = '<tr id="cat-' . $category->getId() . '" class="' . $this->rowClass . '">';
			$out .= '<th class="check-column">';
				$out .= '<input type="checkbox" name="category[]" value="' . $category->getId() . '"/>';
			$out .= '</th>';
			$out .= '<td class="name column-name"><a class="row-title" href="' . $editToken . '">' . $pad . $category->getName() . '</a><br />';
				$out .= '<div class="row-actions">';
					$out .= '<span class="edit"><a href="' . $editToken . '">Edit</a> | </span>';
					$out .= '<span class="delete"><a onclick="return confirm(\'You are about to delete this category. \\\'Cancel\\\' to stop, \\\'OK\\\' to delete\');" href="' . $deleteToken . '">Delete</a></span>';
				$out .= '</div>';
			$out .= '</td>';
			$out .= '<td class="description column-description">' . $category->getDescription() . '</td>';
			$out .= '<td class="slug column-slug">' . $category->getSlug() . '</td>';
			$out .= '<td>';
				/*
				 * Genreate the category link token URL.
				 */
				$categoryFilterURL = $form->tokenURL('admin.php?connections_process=true&process=manage&action=filter&category_id=' . $category->getId(), 'filter');
				
				if ( (integer) $category->getCount() > 0 )
				{
					$out .= '<strong>Count:</strong> ' . '<a href="' . $categoryFilterURL . '">' . $category->getCount() . '</a><br />';
				}
				else
				{
					$out .= '<strong>Count:</strong> ' . $category->getCount() . '<br />';
				}
				
				$out .= '<strong>ID:</strong> ' . $category->getId();
			$out .= '</td>';
		$out .= '</tr>';
		
		return $out;
	}
	
	private function buildOptionRowHTML($term, $level, $selected)
	{
		global $rowClass;
		$selectString = NULL;
		
		$category = new cnCategory($term);
		$pad = str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $level));
		if ($selected == $category->getId()) $selectString = ' SELECTED ';
		
		$out = '<option value="' . $category->getId() . '"' . $selectString . '>' . $pad . $category->getName() . '</option>';
		
		return $out;
	}
	
	private function buildCheckListHTML($term, $level, $checked)
	{
		global $rowClass;
		
		$category = new cnCategory($term);
		$pad = str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $level));
		
		if (!empty($checked))
		{
			if (in_array($category->getId(), $checked))
			{
				$checkString = ' CHECKED ';
			}
			else
			{
				$checkString = NULL;
			}
		}
		else
		{
			$checkString = NULL;
		}
		
		$out = '<li id="category-' . $category->getId() . '" class="category"><label class="selectit">' . $pad . '<input id="check-category-' . $category->getId() . '" type="checkbox" name="entry_category[]" value="' . $category->getId() . '" ' . $checkString . '> ' . $category->getName() . '</input></label></li>';
		
		return $out;
	}
	
	public function showForm($data = NULL)
	{
		global $connections;
		$form = new cnFormObjects();
		$category = new cnCategory($data);
		$parent = new cnCategory($connections->retrieve->category($category->getParent()));
		$level = NULL;
		
		$out = '<div class="form-field form-required connectionsform">';
			$out .= '<label for="cat_name">Category Name</label>';
			$out .= '<input type="text" aria-required="true" size="40" value="' . $category->getName() . '" id="category_name" name="category_name"/>';
			$out .= '<input type="hidden" value="' . $category->getID() . '" id="category_id" name="category_id"/>';
		$out .= '</div>';
		
		$out .= '<div class="form-field connectionsform">';
			$out .= '<label for="category_nicename">Category Slug</label>';
			$out .= '<input type="text" size="40" value="' . $category->getSlug() . '" id="category_slug" name="category_slug"/>';
		$out .= '</div>';
		
		$out .= '<div class="form-field connectionsform">';
			$out .= '<label for="category_parent">Category Parent</label>';
			$out .= '<select class="postform" id="category_parent" name="category_parent">';
				$out .= '<option value="0">None</option>';
				$out .= $this->buildCategoryRow('option', $connections->retrieve->categories(), $level, $parent->getID());
			$out .= '</select>';
		$out .= '</div>';
		
		$out .= '<div class="form-field connectionsform">';
			$out .= '<label for="category_description">Description</label>';
			$out .= '<textarea cols="40" rows="5" id="category_description" name="category_description">' . $category->getDescription() . '</textarea>';
		$out .= '</div>';
		
		echo $out;
	}
}

?>
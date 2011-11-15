<?php
/**
 * Template tag to call the entry list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 * 
 * EXAMPLE:   connectionsEntryList( array('id' => 325) );
 * 
 * @param array $atts
 * @return string
 */
function connectionsEntryList($atts)
{
	echo _connections_list($atts);
}

/**
 * Register the [connections] shortcode
 * 
 * Filters:
 * 		cn_list_template_init			=> Change the list type [affects the default loaded template] or template to be loaded and intialized.
 * 										   The shortcode atts are passed, However the associative array will be limited to list_type and template so only these values can / should be altered.
 * 		cn_list_atts_permitted			=> The permitted shortcode attributes validated using the WordPress function shortcode_atts().
 * 										   The permitted shortcode associative array is passed. Return associative array.
 * 		cn_list_atts					=> Alter the shortcode attributes before validation via the WordPress function shortcode_atts().
 * 										   The shortcode atts are passed. Return associative array.
 * 		cn_list_retrieve_atts			=> Alter the query attributes to be used.
 * 										   The shortcode atts are passed. however the retrieve method will filter and use only the valid atts. Return associative array.
 * 		cn_list_results					=> Filter the returned results before being processed for display. Return indexed array of entry objects.
 * 		cn_list_before					=> Can be used to add content before the output of the list.
 * 										   The entry list results are passed. Return string.
 * 		cn_list_after					=> Can be used to add content after the output of the list.
 * 										   The entry list results are passed. Return string.
 * 		cn_list_entry_before			=> Can be used to add content before the output of the entry.
 * 										   The entry data is passed. Return string.
 * 		cn_list_entry_after				=> Can be used to add content after the output of the entry.
 * 										   The entry data is passed. Return string.
 * 		cn_list_no_result_message		=> Change the 'no results message'.
 * 		cn_list_index					=> Can be used to modify the index before the output of the list.
 * 										   The entry list results are passed. Return string.
 * 
 */
add_shortcode('connections_list', '_connections_list'); /** @deprecated since version 0.7.0.4 */
add_shortcode('connections', '_connections_list'); /** @since version 0.7.1.0 */
function _connections_list($atts, $content = NULL)
{
	global $wpdb, $wp_filter, $current_user, $connections;
	
	$out = '';
	$form = new cnFormObjects();
	$convert = new cnFormatting();
	$format =& $convert;
	$filterRegistry = array();
	
	if ( ! isset($connections->template) ) $connections->template = new cnTemplate();
	$template =& $connections->template;
	
	$previousLetter = '';
	$alternate = '';
	
	/*
	 * Parse the user supplied shortcode atts for the values only required to load the template.
	 * This will permit templates to apply a filter to alter the permitted shortcode atts.
	 */
	$preLoadAtts = $atts;
	
	$preLoadAtts = apply_filters( 'cn_list_template_init', $preLoadAtts);
	
	$preLoadAtts = shortcode_atts( array(
				'list_type' => NULL,
				'template' => NULL, /** @since version 0.7.1.0 */
				'template_name' => NULL /** @deprecated since version 0.7.0.4 */
				), $preLoadAtts ) ;
	
	
	if ( ! empty($preLoadAtts['list_type']) )
	{
		$permittedListTypes = array('individual', 'organization', 'family', 'connection_group');
		
		// Convert to array. Trim the space characters if present.
		$preLoadAtts['list_type'] = explode( ',' , str_replace(' ', '', $preLoadAtts['list_type']) );
		
		// Set the template type to the first in the entry type from the supplied if multiple list types are provided.
		if ( (bool) array_intersect( (array) $preLoadAtts['list_type'], $permittedListTypes) )
		{
			$templateType = $preLoadAtts['list_type'][0];
			
			// Change the list type to family from connection_group to maintain compatibility with versions 0.7.0.4 and earlier.
			if ( $templateType == 'connection_group' ) $templateType = 'family';
		}
	}
	else
	{
		// If no list type was specified, set the default ALL template.
		$templateType = 'all';
	}
	
	
	/*
	 * As of version 0.7.0.5 the $atts['template_name'] is deprecated.
	 */
	if ( isset($preLoadAtts['template_name']) )
	{
		$template->load($atts['template_name']);
	}
	else
	{
		if ( isset( $preLoadAtts['template'] ) )
		{
			$template->load($atts['template']);
		}
		else
		{
			$template->init( $connections->options->getActiveTemplate( $templateType ) );
		}
	}
	
	//$out .= print_r($template , TRUE);
	
	// If no template was found, exit return an error message.
	if ( ! isset($template->file) ) return '<p style="color:red; font-weight:bold; text-align:center;">ERROR: Template "' . $preLoadAtts['template_name'] . $preLoadAtts['template'] . '" not found.</p>' . print_r($template , TRUE);
	
	
	/*
	 * Now that the template has been loaded, Validate the user supplied shortcode atts.
	 */
	$permittedAtts = array(
							'id' => NULL,
							'slug' => NULL,
							'category' => NULL,
							'category_in' => NULL,
							'exclude_category' => NULL,
							'category_name' => NULL,
							'category_slug' => NULL,
							'wp_current_category' => 'false',
							'allow_public_override' => 'false',
							'private_override' => 'false',
							'show_alphaindex' => 'false',
							'repeat_alphaindex' => 'false',
							'show_alphahead' => 'false',
							'list_type' => NULL,
							/*'limit' => NULL,*/
							/*'offset' => NULL,*/
							'order_by' => NULL,
							'family_name' => NULL,
							'last_name' => NULL,
							'title' => NULL,
							'organization' => NULL,
							'department' => NULL,
							'city' => NULL,
							'state' => NULL,
							'zip_code' => NULL,
							'country' => NULL,
							'template' => NULL, /** @since version 0.7.1.0 */
							'template_name' => NULL /** @deprecated since version 0.7.0.4 */
						);
	
	$permittedAtts = apply_filters( 'cn_list_atts_permitted' , $permittedAtts );
	$permittedAtts = apply_filters( 'cn_list_atts_permitted-' . $template->slug , $permittedAtts );
	
	$atts = shortcode_atts( $permittedAtts , $atts ) ;
	
	$atts = apply_filters( 'cn_list_atts' , $atts );
	$atts = apply_filters( 'cn_list_atts-' . $template->slug , $atts );
	//$filterRegistry[] = 'cn_list_atts-' . $template->slug;
	
	/*
	 * Convert some of the $atts values in the array to boolean.
	 */
	$convert->toBoolean($atts['allow_public_override']);
	$convert->toBoolean($atts['private_override']);
	$convert->toBoolean($atts['show_alphaindex']);
	$convert->toBoolean($atts['repeat_alphaindex']);
	$convert->toBoolean($atts['show_alphahead']);
	$convert->toBoolean($atts['wp_current_category']);
	
	/*
	 * The WP post editor encodes the post text we have to decode it
	 * so a match can be made when the query is run.
	 */
	$atts['family_name'] = html_entity_decode($atts['family_name']);
	$atts['last_name'] = html_entity_decode($atts['last_name']);
	$atts['title'] = html_entity_decode($atts['title']);
	$atts['organization'] = html_entity_decode($atts['organization']);
	$atts['department'] = html_entity_decode($atts['department']);
	$atts['city'] = html_entity_decode($atts['city']);
	$atts['state'] = html_entity_decode($atts['state']);
	$atts['zip_code'] = html_entity_decode($atts['zip_code']);
	$atts['country'] = html_entity_decode($atts['country']);
	
	$atts = apply_filters('cn_list_retrieve_atts' , $atts );
	$atts = apply_filters('cn_list_retrieve_atts-' . $template->slug , $atts );
	
	$results = $connections->retrieve->entries($atts);
	//$out .= print_r($connections->lastQuery , TRUE);
	//$out .= print_r($results , TRUE);
	
	if ( ! empty($results) ) $results = apply_filters( 'cn_list_results', $results );
	if ( ! empty($results) ) $results = apply_filters( 'cn_list_results-' . $template->slug , $results );
	if ( ! empty($results) ) $filterRegistry[] = 'cn_list_results-' . $template->slug;
	
	// Prints the template's CSS file.
	if ( method_exists($template, 'printCSS') ) $out .= $template->printCSS();
	
	$out .= "\n" . '<div class="connections template ' . $template->slug . '" id="' . $template->slug . '" data-connections-version="' . $connections->options->getVersion() . '-' . $connections->options->getDBVersion() . '">' . "\n";
	
				
		$out .= "\n" . '<div class="cn-clear" id="connections-list-head">' . "\n";
		
			ob_start();
				do_action( 'cn_action_list_before' );
				do_action( 'cn_action_list_before-' . $template->slug );
				$filterRegistry[] = 'cn_action_list_before-' . $template->slug;
				
				do_action( 'cn_action_list_both' );
				do_action( 'cn_action_list_both-' . $template->slug );
				$filterRegistry[] = 'cn_action_list_both-' . $template->slug;
				
				$out .= ob_get_contents();
			ob_end_clean();
			
			$out .= apply_filters( 'cn_list_before' , '' , $results );
			$out .= apply_filters( 'cn_list_before-' . $template->slug , '' , $results );
			$filterRegistry[] = 'cn_list_before-' . $template->slug;
		
		$out .= "\n" . '</div>' . "\n";
		
		$out .= '<div class="connections-list">' . "\n";
		
		/*
		 * The alpha index is only displayed if set set to true and not set to repeat using the shortcode attributes.
		 * If a alpha index is set to repeat, that is handled separately.
		 */
		if ( $atts['show_alphaindex'] && ! $atts['repeat_alphaindex'] )
		{
			$index = "\n" . '<div class="cn-alphaindex" style="text-align:right;font-size:larger;font-weight:bold">' . $form->buildAlphaIndex(). '</div>' . "\n";
			$index = apply_filters( 'cn_list_index' , '' , $results );
			$index = apply_filters( 'cn_list_index-' . $template->slug , '' , $results );
			$filterRegistry[] = 'cn_list_index-' . $template->slug;
			
			$out .= $index;
		}
		
		// If there are no results no need to proceed and output message.
		if ( empty($results) )
		{
			$noResultMessage = apply_filters( 'cn_list_no_result_message' , 'No results' );
			$noResultMessage = apply_filters( 'cn_list_no_result_message-' . $template->slug , 'No results' );
			$filterRegistry[] = 'cn_list_no_result_message-' . $template->slug;
			
			$out .=  "\n" . '<p class="cn-list-no-results">' . $noResultMessage . '</p>' . "\n";
		}
		else
		{
			foreach ( (array) $results as $row )
			{
				$entry = new cnvCard($row);
				$vCard =& $entry;
				$repeatIndex = '';
				$setAnchor = '';
				
				/*
				 * Checks the first letter of the last name to see if it is the next
				 * letter in the alpha array and sets the anchor.
				 * 
				 * If the alpha index is set to repeat it will append to the anchor.
				 * 
				 * If the alpha head set to true it will append the alpha head to the anchor.
				 */
				$currentLetter = strtoupper(mb_substr($entry->getSortColumn(), 0, 1));
				
				if ($currentLetter != $previousLetter && $atts['id'] == NULL)
				{
					if ($atts['show_alphaindex']) $setAnchor = '<a class="cn-index-head" name="' . $currentLetter . '"></a>';
					
					if ($atts['show_alphaindex'] && $atts['repeat_alphaindex'])
					{
						$repeatIndex = "\n" . "<div class='cn-alphaindex' style='text-align:right;font-size:larger;font-weight:bold'>" . $form->buildAlphaIndex() . "</div>" . "\n";
						$repeatIndex = apply_filters( 'cn_list_index' , '' , $results );
						$repeatIndex = apply_filters( 'cn_list_index-' . $template->slug , '' , $results );
						$filterRegistry[] = 'cn_list_index-' . $template->slug;
					}
					
					if ($atts['show_alphahead']) $setAnchor .= "\n" . '<h4 class="cn-alphahead">' . $currentLetter . '</h4>' . "\n";
					
					$previousLetter = $currentLetter;
				}
				
				/*
				 * The anchor and/or the alpha head is displayed if set to true using the shortcode attributes.
				 */
				if ($atts['show_alphaindex'] || $atts['show_alphahead']) $out .= $setAnchor . $repeatIndex;
				
				$alternate == '' ? $alternate = '-alternate' : $alternate = '';
				
				
				$out .= "\n" . '<div class="cn-list-row' . $alternate . ' vcard ' . $entry->getCategoryClass(TRUE) . '" id="' . $entry->getSlug() . '">' . "\n";
					
					$out .= apply_filters( 'cn_list_entry_before' , '' , $entry );
					$out .= apply_filters( 'cn_list_entry_before-' . $template->slug , '' , $entry );
					$filterRegistry[] = 'cn_list_entry_before-' . $template->slug;
					
					ob_start();
						include($template->file);
					    $out .= ob_get_contents();
				    ob_end_clean();
					
					$out .= apply_filters( 'cn_list_entry_after' , '' , $entry );
					$out .= apply_filters( 'cn_list_entry_after-' . $template->slug , '' , $entry );
					$filterRegistry[] = 'cn_list_entry_after-' . $template->slug;
					
				$out .= "\n" . '</div>' . "\n";
							
			}
		}
		
		$out .= "\n" . '</div>' . "\n";
		
		$out .= "\n" . '<div class="cn-clear" id="connections-list-foot">' . "\n";
		
			$out .= apply_filters( 'cn_list_after' , '' , $results );
			$out .= apply_filters( 'cn_list_after-' . $template->slug , '' , $results );
			$filterRegistry[] = 'cn_list_after-' . $template->slug;
			
			ob_start();
				do_action( 'cn_action_list_after' );
				do_action( 'cn_action_list_after-' . $template->slug );
				$filterRegistry[] = 'cn_action_list_after-' . $template->slug;
				
				do_action( 'cn_action_list_both' );
				do_action( 'cn_action_list_both-' . $template->slug );
				$filterRegistry[] = 'cn_action_list_both-' . $template->slug;
				
				$out .= ob_get_contents();
			ob_end_clean();
		
		$out .= "\n" . '</div>' . "\n";
	
	$out .= "\n" . '</div>' . "\n";
	
	$template->reset();
	
	/*
	 * Remove any filters a template may have added
	 * so it is not run again if more than one template
	 * is in use on the same page.
	 */
	
	foreach ( $filterRegistry as $filter )
	{
		if ( isset( $wp_filter[$filter] ) ) unset( $wp_filter[$filter] );
	}
	
	return $out;
}

/**
 * Template tag to call the upcoming list. All options can be passed as an
 * associative array. The options are identical to those available to the
 * shortcode.
 * 
 * EXAMPLE:   connectionsUpcomingList(array('days' => 30));
 * 
 * @param array $atts
 * @return string
 */
function connectionsUpcomingList($atts)
{
	echo _upcoming_list($atts);
}

add_shortcode('upcoming_list', '_upcoming_list');
function _upcoming_list($atts, $content=null) {
    global $connections, $wpdb;
	
	$template = new cnTemplate();
	$convert = new cnFormatting();
	
	$atts = shortcode_atts( array(
			'list_type' => 'birthday',
			'days' => '30',
			'include_today' => TRUE,
			'private_override' => FALSE,
			'date_format' => 'F jS',
			'show_lastname' => FALSE,
			'show_title' => TRUE,
			'list_title' => NULL,
			'template' => $connections->options->getActiveTemplate('birthday')
			), $atts ) ;
	
	/*
	 * Convert some of the $atts values in the array to boolean.
	 */
	$convert->toBoolean(&$atts['include_today']);
	$convert->toBoolean(&$atts['private_override']);
	$convert->toBoolean(&$atts['show_lastname']);
	$convert->toBoolean(&$atts['repeat_alphaindex']);
	$convert->toBoolean(&$atts['show_title']);
	
	if (is_user_logged_in() || $atts['private_override'] != FALSE) { 
		$visibilityfilter = " AND (visibility='private' OR visibility='public') AND (".$atts['list_type']." != '')";
	} else {
		$visibilityfilter = " AND (visibility='public') AND (`".$atts['list_type']."` != '')";
	}
	
	if ($atts['list_title'] == NULL)
	{
		switch ($atts['list_type'])
		{
			case 'birthday':
				if ( $atts['days'] >= 1 )
				{
					$list_title = 'Upcoming Birthdays the next ' . $atts['days'] . ' days';
				}
				else
				{
					$list_title = 'Today\'s Birthdays';
				}
			break;
			
			case 'anniversary':
				if ( $atts['days'] >= 1 )
				{
					$list_title = 'Upcoming Anniversaries the next ' . $atts['days'] . ' days';
				}
				else
				{
					$list_title = 'Today\'s Anniversaries';
				}
			break;
		}
	}
	else
	{
		$list_title = $atts['list_title'];
	}
	
	/*
	 * $atts['template'] can be either a string or an object. It is a string when set
	 * with the shortcode attribute. If it is a string, the template will be loaded
	 * via the cnTemplate class.
	 * 
	 * If the attribute is not set, it will be the object returned from the
	 * cnOptions::getActiveTemplate() method which stores the default template
	 * per list style.
	 */
	if ( isset($atts['template']) && !is_object($atts['template']) )
	{
		$template->load($atts['template']);
		//$template->includeFunctions();
	}
	else
	{
		$template->init( $connections->options->getActiveTemplate( $atts['list_type'] ) );
		//$template->includeFunctions();
	}
		
	/* Old and busted query!2
	$sql = "SELECT id, ".$atts['list_type'].", last_name, first_name FROM ".$wpdb->prefix."connections where (YEAR(DATE_ADD(CURRENT_DATE, INTERVAL ".$atts['days']." DAY))"
        . " - YEAR(FROM_UNIXTIME(".$atts['list_type'].")) )"
        . " - ( MID(DATE_ADD(CURRENT_DATE, INTERVAL ".$atts['days']." DAY),5,6)"
        . " < MID(FROM_UNIXTIME(".$atts['list_type']."),5,6) )"
        . " > ( YEAR(CURRENT_DATE)"
        . " - YEAR(FROM_UNIXTIME(".$atts['list_type'].")) )"
        . " - ( MID(CURRENT_DATE,5,6)"
        . " < MID(FROM_UNIXTIME(".$atts['list_type']."),5,6) )"
		. $visibilityfilter
		. " ORDER BY FROM_UNIXTIME(".$atts['list_type'].") ASC";
	*/
	
	// Get the current date from WP which should have the current time zone offset.
	$wpCurrentDate = date( 'Y-m-d', $connections->options->wpCurrentTime );
	
	( $atts['include_today'] ) ? $includeToday = '<=' : $includeToday = '<';
	
	/*
	 * 
	 */
	$newSQL = "SELECT * FROM ".CN_ENTRY_TABLE." WHERE"
		. "  (YEAR(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY))"
        . " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND)) )"
        . " - ( MID(DATE_ADD('$wpCurrentDate', INTERVAL ".$atts['days']." DAY),5,6)"
        . " < MID(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND),5,6) )"
        . " > ( YEAR('$wpCurrentDate')"
        . " - YEAR(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND)) )"
        . " - ( MID('$wpCurrentDate',5,6)"
        . " ".$includeToday." MID(DATE_ADD(FROM_UNIXTIME(`".$atts['list_type']."`), INTERVAL ".$connections->options->sqlTimeOffset." SECOND),5,6) )"
		. $visibilityfilter;
	
	$results = $wpdb->get_results($newSQL);
	
	// If there are no results no need to proceed and output message.
	if ( empty($results) )
	{
		$noResultMessage = 'No results';
		$noResultMessage = apply_filters('cn_upcoming_no_result_message', $noResultMessage);
		return '<p class="cn-upcoming-no-results">' . $noResultMessage . '</p>';
	}
	
	if ($results != NULL)
	{
		/*The SQL returns an array sorted by the birthday and/or anniversary date. However the year end wrap needs to be accounted for.
		Otherwise earlier months of the year show before the later months in the year. Example Jan before Dec. The desired output is to show
		Dec then Jan dates.  This function checks to see if the month is a month earlier than the current month. If it is the year is changed to the following year rather than the current.
		After a new list is built, it is resorted based on the date.*/
		foreach ($results as $key => $row)
		{
			if ( gmmktime(23, 59, 59, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) ) < $connections->options->wpCurrentTime )
			{
				$dateSort[] = $row->$atts['list_type'] = gmmktime(0, 0, 0, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) + 1 );
			}
			else
			{
				$dateSort[] = $row->$atts['list_type'] = gmmktime(0, 0, 0, gmdate('m', $row->$atts['list_type']), gmdate('d', $row->$atts['list_type']), gmdate('Y', $connections->options->wpCurrentTime) );
			}
		}
		
		array_multisort($dateSort, SORT_ASC, $results);
		
		
		$out = '';
		
		// Prints the template's CSS file.
		if ( method_exists($template, 'printCSS') ) $out .= $template->printCSS();
		
		// Prints the javascript tag in the footer if $template->js path is set
		if ( method_exists($template, 'printJS') ) $template->printJS();
		
		
		$out .= '<div class="connections-list cn-upcoming '. $atts['list_type'] . ' ' . $template->slug . '">' . "\n";
		if ( $atts['show_title'] ) $out .= '<div class="cn-upcoming-title">' . $list_title  . '</div>';
				
		foreach ($results as $row)
		{
			$entry = new cnvCard($row);
			$vCard =& $entry;
			
			$entry->name = '';
			
			$alternate == '' ? $alternate = '-alternate' : $alternate = '';
			
			!$atts['show_lastname'] ? $entry->name = $entry->getFirstName() : $entry->name = $entry->getFullFirstLastName();
			
			if (isset($template->file))
			{
				$out .= '<div class="cn-upcoming-row' . $alternate . ' vcard ' . '">' . "\n";
					ob_start();
					include($template->file);
				    $out .= ob_get_contents();
				    ob_end_clean();
				$out .= '</div>' . "\n";
			}
			else
			{
				// If no template is found, return an error message.
				return '<p style="color:red; font-weight:bold; text-align:center;">ERROR: Template "' . $atts['template_name'] . '" not found.</p>';
			}
		
		}
		
		$out .= "</div>\n";
		
		return $out;
	}
}
?>
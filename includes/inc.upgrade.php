<?php
function connectionsShowUpgradePage()
{
	/*
	 * Check whether user can access.
	 */
	if (!current_user_can('connections_manage'))
	{
		wp_die('<p id="error-page" style="-moz-background-clip:border;
				-moz-border-radius:11px;
				background:#FFFFFF none repeat scroll 0 0;
				border:1px solid #DFDFDF;
				color:#333333;
				display:block;
				font-size:12px;
				line-height:18px;
				margin:25px auto 20px;
				padding:1em 2em;
				text-align:center;
				width:700px">You do not have sufficient permissions to access this page.</p>');
	}
	else
	{
		global $connections;
		
		?>
			
			<div class="wrap nosubsub">
				<div class="icon32" id="icon-connections"><br/></div>
				<h2>Connections : Upgrade</h2>
				<?php echo $connections->displayMessages(); ?>
				<div id="connections-upgrade">
				
					<?php
						$urlPath = admin_url() . 'admin.php?page=' . $_GET['page'];
						
						if ( isset($_GET['upgrade-db']) && $_GET['upgrade-db'] === 'do')
						{
							cnRunDBUpgrade();
						}
						else
						{
							?>
								<h3>Upgrade Required!</h3>
								<p>Your database tables for Connections is out of date and must be upgraded before you can continue.</p>
								<p>If you would like to downgrade later, please first make a complete backup of your database tables.</p>
								<h4><a class="button-primary" href="<?php echo $urlPath;?>&amp;upgrade-db=do">Start Upgrade</a></h4>
							<?php
						}
					
					?>
				
				</div>
			</div>
			
		<?php
	}
}

function cnRunDBUpgrade()
{
	global $wpdb, $connections;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$urlPath = admin_url() . 'admin.php?page=' . $_GET['page'];
	
	if ( ! empty($wpdb->charset) )
		$charsetCollate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty($wpdb->collate) )
		$charsetCollate .= " COLLATE $wpdb->collate";
	
	// Check to ensure that the table exists
	if ($wpdb->get_var("SHOW TABLES LIKE 'CN_ENTRY_TABLE'") != CN_ENTRY_TABLE)
	{
		echo '<h3>Upgrade the database structure...</h3>' . "\n";
		$wpdb->show_errors();
		
		$dbVersion = $connections->options->getDBVersion();
		
		if (version_compare($dbVersion, '0.1.0', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version 0.1.0 ' . ".</h4>\n";
			echo '<ul>';
			
			echo '<li>Changing "id" type-length/values to BIGINT(20)' . "</li>\n";
			if (!$wpdb->query("ALTER TABLE " . CN_ENTRY_TABLE . " CHANGE id id BIGINT(20) NOT NULL AUTO_INCREMENT")) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "date_added"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'date_added', 'tinytext NOT NULL AFTER ts')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "entry_type"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'entry_type', 'tinytext NOT NULL AFTER date_added')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "honorable_prefix"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'honorable_prefix', 'tinytext NOT NULL AFTER entry_type')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "middle_name"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'middle_name', 'tinytext NOT NULL AFTER first_name')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "honorable_suffix"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'honorable_suffix', 'tinytext NOT NULL AFTER last_name')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "social"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'social', 'longtext NOT NULL AFTER im')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "added_by"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'added_by', 'bigint(20) NOT NULL AFTER options')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "edited_by"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'edited_by', 'bigint(20) NOT NULL AFTER added_by')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "owner"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'owner', 'bigint(20) NOT NULL AFTER edited_by')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "status"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'status', 'varchar(20) NOT NULL AFTER owner')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "contact_first_name"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'contact_first_name', 'tinytext NOT NULL AFTER department')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "contact_last_name"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'contact_last_name', 'tinytext NOT NULL AFTER contact_first_name')) echo '<ul><li>SUCCESS</li></ul>';
			echo '</ul>';
		
			echo '<h4>Adding default term relationship.</h4>';
			
			// Add the Uncategorized category to all previous entries.
			$term = $connections->term->getTermBy('slug', 'uncategorized', 'category');
			
			$entryIDs = $wpdb->get_col( "SELECT id FROM " . CN_ENTRY_TABLE );
			
			$termID[] = $term->term_taxonomy_id;
			
			foreach ($entryIDs as $entryID)
			{
				$connections->term->setTermRelationships($entryID, $termID, 'category');
			}
			
			$connections->options->setDBVersion('0.1.0');
		}
		
		if (version_compare($dbVersion, '0.1.1', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version 0.1.1 ' . ".</h4>\n";
			
			echo '<h4>Setting all current entries to the "approved" status.' . "</h4>\n";
			
			$wpdb->query( 'UPDATE ' . CN_ENTRY_TABLE . ' SET status = \'approved\'' );
			
			$connections->options->setDBVersion('0.1.1');
		}
		
		if (version_compare($dbVersion, '0.1.2', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version 0.1.2 ' . ".</h4>\n";
			
			echo '<h4>Setting all current entries `entry_type` column.' . "</h4>\n";
			
			$sql = 'SELECT DISTINCT `id`, `options` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				$options = array();
				$options = unserialize($result->options);
				
				if ( isset($options['entry']['type']) )
				{
					$entryType = $options['entry']['type'];
				}
				else
				{
					$entryType = 'individual';
				}
				
				$wpdb->query( 'UPDATE ' . CN_ENTRY_TABLE . ' SET entry_type = \'' . $entryType . '\' WHERE id = ' . $result->id );
			}
			
			$connections->options->setDBVersion('0.1.2');
		}
		
		if (version_compare($dbVersion, '0.1.3', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version ' . CN_DB_VERSION . ".</h4>\n";
			
			echo '<ul>';
			echo '<li>Changing column name from group_name to family_name...' . "</li>\n";
			if (cnAlterTable(CN_ENTRY_TABLE, 'family_name' , 'CHANGE COLUMN group_name family_name tinytext NOT NULL')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '</ul>';
			
			$connections->options->setDBVersion('0.1.3');
		}
		
		if (version_compare($dbVersion, '0.1.4', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version ' . CN_DB_VERSION . ".</h4>\n";
			
			echo '<ul>';
			echo '<li>Changing column name from honorable_prefix to honorific_prefix...' . "</li>\n";
			if (cnAlterTable(CN_ENTRY_TABLE, 'honorific_prefix' , 'CHANGE COLUMN honorable_prefix honorific_prefix tinytext NOT NULL')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Changing column name from honorable_suffix to honorific_suffix...' . "</li>\n";
			if (cnAlterTable(CN_ENTRY_TABLE, 'honorific_suffix' , 'CHANGE COLUMN honorable_suffix honorific_suffix tinytext NOT NULL')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '</ul>';
			
			$connections->options->setDBVersion('0.1.4');
		}
		
		if (version_compare($dbVersion, '0.1.5', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version ' . CN_DB_VERSION . ".</h4>\n";
			
			echo '<ul>';
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_TABLE_META . "'") != CN_ENTRY_TABLE_META)
			{
				echo '<li>Add the entry meta table.' , "</li>\n";
				
				$entryTableMeta = "CREATE TABLE " . CN_ENTRY_TABLE_META . " (
			        meta_id bigint(20) unsigned NOT NULL auto_increment,
					entry_id bigint(20) unsigned NOT NULL default '0',
					meta_key varchar(255) default NULL,
					meta_value longtext,
					PRIMARY KEY  (meta_id),
					KEY entry_id (entry_id),
					KEY meta_key (meta_key)
			    ) $charsetCollate;";
			    
			    dbDelta($entryTableMeta);
			}
			
			echo '</ul>';
			
			$connections->options->setDBVersion('0.1.5');
		}
		
		if (version_compare($dbVersion, '0.1.6', '<'))
		{
			echo '<h4>Upgrade from database version ' . $connections->options->getDBVersion() . ' to database version ' . CN_DB_VERSION . ".</h4>\n";
			
			echo '<ul>';
			
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_ADDRESS_TABLE . "'") != CN_ENTRY_ADDRESS_TABLE)
			{
				echo '<li>Add the entry address table.' , "</li>\n";
				
				$entryTableAddress = "CREATE TABLE " . CN_ENTRY_ADDRESS_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`line_1` tinytext NOT NULL,
					`line_2` tinytext NOT NULL,
					`line_3` tinytext NOT NULL,
					`city` tinytext NOT NULL,
					`state` tinytext NOT NULL,
					`zipcode` tinytext NOT NULL,
					`country` tinytext NOT NULL,
					`latitude` decimal(15,12) default NULL,
					`longitude` decimal(15,12) default NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableAddress);
			}
			
			echo '<li>Porting addresses...' , "</li>\n";
			
			$sql = 'SELECT DISTINCT `id`, `addresses` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->addresses) )
				{
					$addresses = unserialize($result->addresses);
					$order = 0;
					
					foreach ( (array) $addresses as $key => $address)
					{
						//if ( empty($address['address_line1']) && empty($address['address_line2']) && empty($address['city']) && empty($address['state']) && empty($address['zipcode']) ) continue;
						
						if ( empty($address['type']) ) $address['type'] = 'other';
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_ADDRESS_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`line_1`			= "%s",
												`line_2`			= "%s",
												`line_3`			= "%s",
												`city`				= "%s",
												`state`				= "%s",
												`zipcode`			= "%s",
												`country`			= "%s",
												`latitude`			= "%s",
												`longitude`			= "%s",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												$address['type'],
												$address['address_line1'],
												$address['address_line2'],
												'',
												$address['city'],
												$address['state'],
												$address['zipcode'],
												$address['country'],
												$address['latitude'],
												$address['longitude'],
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_PHONE_TABLE . "'") != CN_ENTRY_PHONE_TABLE)
			{
				echo '<li>Add the entry phone table.' , "</li>\n";
				
				$entryTablePhone = "CREATE TABLE " . CN_ENTRY_PHONE_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`number` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTablePhone);
			}
			
			echo '<li>Porting phone numbers...' , "</li>\n";
			
			$sql = 'SELECT DISTINCT `id`, `phone_numbers` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->phone_numbers) )
				{
					$phoneNumbers = unserialize($result->phone_numbers);
					$order = 0;
					
					foreach ( (array) $phoneNumbers as $key => $phone)
					{
						if ( empty($phone['number']) ) continue;
						
						if ( empty($phone['type']) ) $phone['type'] = 'homephone';
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_PHONE_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`number`			= "%s",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												$phone['type'],
												$phone['number'],
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_EMAIL_TABLE . "'") != CN_ENTRY_EMAIL_TABLE)
			{
				echo '<li>Add the entry email table.' , "</li>\n";
				
				$entryTableEmail = "CREATE TABLE " . CN_ENTRY_EMAIL_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`address` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableEmail);
			}
			
			echo '<li>Porting email addresses...' , "</li>\n";
				
			$sql = 'SELECT DISTINCT `id`, `email` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->email) )
				{
					$emailAddresses = unserialize($result->email);
					$order = 0;
					
					foreach ( (array) $emailAddresses as $key => $email	)
					{
						if ( empty($email['address']) ) continue;
						
						if ( empty($email['type']) ) $email['type'] = 'personal';
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_EMAIL_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`address`			= "%s",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												$email['type'],
												$email['address'],
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_MESSENGER_TABLE . "'") != CN_ENTRY_MESSENGER_TABLE)
			{
				echo '<li>Add the entry messenger table.' , "</li>\n";
				
				$entryTableMessenger = "CREATE TABLE " . CN_ENTRY_MESSENGER_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`uid` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableMessenger);
			}
			
			echo '<li>Porting IM IDs...' , "</li>\n";
			
			$sql = 'SELECT DISTINCT `id`, `im` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->im) )
				{
					$imIDs = unserialize($result->im);
					$order = 0;
					
					foreach ( (array) $imIDs as $key => $network )
					{
						if ( empty($network['id']) ) continue;
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_MESSENGER_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`uid`				= "%s",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												$network['type'],
												$network['id'],
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_SOCIAL_TABLE . "'") != CN_ENTRY_SOCIAL_TABLE)
			{
				echo '<li>Add the entry social media table.' , "</li>\n";
				
				$entryTableSocialMedia = "CREATE TABLE " . CN_ENTRY_SOCIAL_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`url` tinytext NOT NULL,
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableSocialMedia);
			}
			
			echo '<li>Porting Social Media IDs...' , "</li>\n";
			
			$sql = 'SELECT DISTINCT `id`, `social` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->social) )
				{
					$socialMediaIDs = unserialize($result->social);
					$order = 0;
					
					foreach ( (array) $socialMediaIDs as $key => $network )
					{
						if ( empty($network['url']) ) continue;
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_SOCIAL_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`url`				= "%s",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												$network['type'],
												$network['url'],
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			if ($wpdb->get_var("SHOW TABLES LIKE '" . CN_ENTRY_LINK_TABLE . "'") != CN_ENTRY_LINK_TABLE)
			{
				echo '<li>Add the entry link table.' , "</li>\n";
				
				$entryTableLink = "CREATE TABLE " . CN_ENTRY_LINK_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`title` tinytext NOT NULL,
					`url` tinytext NOT NULL,
					`target` tinytext NOT NULL,
					`follow` tinyint unsigned NOT NULL default '0',
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";
			    
			    dbDelta($entryTableLink);
			}
			
			echo '<li>Porting websites...' , "</li>\n";
			
			$sql = 'SELECT DISTINCT `id`, `websites` FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				if ( ! empty($result->websites) )
				{
					$websites = unserialize($result->websites);
					$order = 0;
					
					foreach ( (array) $websites as $key => $website )
					{
						if ( empty($website['address']) ) continue;
						
						$sql = $wpdb->prepare ('INSERT INTO ' . CN_ENTRY_LINK_TABLE . ' SET
												`entry_id`			= "%d",
												`order`				= "%d",
												`preferred`			= "%d",
												`type`				= "%s",
												`title`				= "%s",
												`url`				= "%s",
												`target`			= "%s",
												`follow`			= "%d",
												`visibility`		= "%s"',
												$result->id,
												$order,
												0,
												'website',
												'Visit Website',
												$website['address'],
												'new',
												0,
												'public');
						
						$wpdb->query($sql);
						$order++;
					}
				}
			}
			
			echo '<li>Changing column name from websites to links...' . "</li>\n";
			if ( cnAlterTable( CN_ENTRY_TABLE , 'links' , 'CHANGE COLUMN websites links longtext NOT NULL' ) ) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding Column... "slug"' . "</li>\n";
			if (cnAddTableColumn(CN_ENTRY_TABLE, 'slug', 'tinytext NOT NULL AFTER visibility')) echo '<ul><li>SUCCESS</li></ul>';
			
			echo '<li>Adding entry slugs.' . "</li>\n";
			
			$sql = 'SELECT id, 
						CASE entry_type 
						  WHEN \'individual\' THEN CONCAT_WS( \'-\' , LOWER(first_name) , LOWER(last_name) ) 
						  WHEN \'organization\' THEN LOWER(organization) 
						  WHEN \'connection_group\' THEN LOWER(family_name) 
						  WHEN \'family\' THEN LOWER(family_name) 
						END AS slug_temp 
					FROM ' . CN_ENTRY_TABLE;
			
			$results = $wpdb->get_results($sql);
			
			foreach ( $results as $result )
			{
				$slug = sanitize_title( $result->slug_temp );
				
				$query = $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug = %s', $slug );
				
				if ( $wpdb->get_var( $query ) )
				{
					$num = 2;
					do
					{
						$alt_slug = $slug . "-$num";
						$num++;
						$slug_check = $wpdb->get_var( $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug = %s', $alt_slug ) );
					}
					while ( $slug_check );
					
					$slug = $alt_slug;
				}
				
				$wpdb->query( 'UPDATE ' . CN_ENTRY_TABLE . ' SET slug = \'' . $slug . '\' WHERE id = ' . $result->id );
			}
			
			echo '</ul>';
			
			
			/*
			 * Not a db upgrade but we'll drop this in here to make it simple.
			 * This file is no longer needed so we'll try to remove it.
			 */
			if ( file_exists(ABSPATH . 'download.vCard.php') ) @unlink( ABSPATH . 'download.vCard.php' );
			
			$connections->options->setDBVersion('0.1.6');
		}
		
		/*echo '<h4>Updating entries to the new database stucture.' . "</h4>\n";
		
		$results = $connections->retrieve->entries();
		
		foreach ($results as $result)
		{
			$entry = new cnEntry($result);
			$entry->update();
		}*/
		
		echo '<h4>Upgrade completed.' . "</h4>\n";
		echo '<h4><a class="button-primary" href="' . $urlPath . '">Continue</a></h4>';
		
		$wpdb->hide_errors();
	}
}

function cnAlterTable($tableName, $columnName, $sql)
{
	global $wpdb;
	
	foreach ($wpdb->get_col("SHOW COLUMNS FROM $tableName") as $column )
	{
		if ($column == $columnName) return TRUE;
	}
	
	// didn't find it try to create it.
	return $wpdb->query('ALTER TABLE ' . $tableName . ' ' . $sql);
}

/**
 * Add a new column.
 * Example : cnAddTableColumn( CN_ENTRY_TABLE, 'status', "varchar(20) NOT NULL");
 * 
 * Credit WordPress plug-in NGG.
 * 
 * @param string $tableName Database table name.
 * @param string $columnName Database column name to create.
 * @param string $sql SQL statement to create column
 * @return bool
 */
function cnAddTableColumn($tableName, $columnName, $sql)
{
	global $wpdb;
	
	foreach ($wpdb->get_col("SHOW COLUMNS FROM $tableName") as $column )
	{
		if ($column == $columnName) return TRUE;
	}
	
	// didn't find it try to create it.
	$wpdb->query('ALTER TABLE ' . $tableName . ' ADD ' . $columnName . ' ' . $sql);
	
	// we cannot directly tell that whether this succeeded!
	foreach ($wpdb->get_col("SHOW COLUMNS FROM $tableName") as $column )
	{
		if ($column == $columnName) return TRUE;
	}
	
	echo("<ul><li><strong>Could not add column $columnName in table $tableName.</li></strong></ul>\n");
	
	return FALSE;
}
?>
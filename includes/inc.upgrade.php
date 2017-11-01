<?php

/**
 * Functions to upgrade the table structure.
 *
 * @package     Connections
 * @subpackage  Upgrade functions.
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function connectionsShowUpgradePage() {

	/*
	 * Check whether user can access.
	 */
	if ( ! current_user_can( 'connections_manage' ) ) {
		wp_die( '<p id="error-page" style="-moz-background-clip:border;
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
				width:700px">' . __( 'You do not have sufficient permissions to access this page.', 'connections' ) . '</p>' );
	} else {

		$url = add_query_arg(
			array(
				'page' => $_GET['page'],
				'upgrade-db' => 'do',
			),
			self_admin_url( 'admin.php' )
		);
		?>

		<div class="wrap nosubsub">
			<h2>Connections : <?php _e( 'Upgrade', 'connections' ); ?></h2>

			<div id="connections-upgrade">

				<?php if ( isset( $_GET['upgrade-db'] ) && 'do' === $_GET['upgrade-db'] ) :
					cnRunDBUpgrade();
				else : ?>
					<h3><?php esc_html_e( 'Upgrade Required!', 'connections' ); ?></h3>
					<p><?php esc_html_e( 'Your database tables are out of date and must be upgraded before you can continue.', 'connections' ); ?></p>
					<p><?php esc_html_e( 'If you would like to downgrade later, please first make a complete backup of your database tables.', 'connections' ); ?></p>
					<h4><a class="button-primary" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Start Upgrade', 'connections' ); ?></a></h4>
				<?php endif; ?>

			</div>
		</div>

		<?php
	}
}

function cnRunDBUpgrade() {

	/**
	 * @var wpdb $wpdb
	 * @var connectionsLoad $connections
	 */
	global $wpdb, $connections;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	require_once CN_PATH . 'includes/class.schema.php';

	$urlPath = admin_url() . 'admin.php?page=' . $_GET['page'];

	if ( ! empty( $wpdb->charset ) )
		$charsetCollate = "DEFAULT CHARACTER SET $wpdb->charset";
	if ( ! empty( $wpdb->collate ) )
		$charsetCollate .= " COLLATE $wpdb->collate";

	// Check to ensure that the table exists
	if ( $wpdb->get_var( "SHOW TABLES LIKE 'CN_ENTRY_TABLE'" ) != CN_ENTRY_TABLE ) {
		echo '<h3>' , __( 'Upgrade the database structure...', 'connections' ) , '</h3>' . "\n";
		$wpdb->show_errors();

		$dbVersion = $connections->options->getDBVersion();

		if ( version_compare( $dbVersion, '0.1.0', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.0.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';

			echo '<li>' , __( 'Changing "id" type-length/values to BIGINT(20)', 'connections' ) , "</li>\n";
			if ( !$wpdb->query( "ALTER TABLE " . CN_ENTRY_TABLE . " CHANGE id id BIGINT(20) NOT NULL AUTO_INCREMENT" ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "date_added"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'date_added', 'tinytext NOT NULL AFTER ts' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "entry_type"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'entry_type', 'tinytext NOT NULL AFTER date_added' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "honorable_prefix"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'honorable_prefix', 'tinytext NOT NULL AFTER entry_type' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "middle_name"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'middle_name', 'tinytext NOT NULL AFTER first_name' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "honorable_suffix"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'honorable_suffix', 'tinytext NOT NULL AFTER last_name' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "social"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'social', 'longtext NOT NULL AFTER im' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "added_by"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'added_by', 'bigint(20) NOT NULL AFTER options' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "edited_by"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'edited_by', 'bigint(20) NOT NULL AFTER added_by' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "owner"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'owner', 'bigint(20) NOT NULL AFTER edited_by' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "status"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'status', 'varchar(20) NOT NULL AFTER owner' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "contact_first_name"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'contact_first_name', 'tinytext NOT NULL AFTER department' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding Column... "contact_last_name"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'contact_last_name', 'tinytext NOT NULL AFTER contact_first_name' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';
			echo '</ul>';

			echo '<h4>' , __( 'Adding default term relationship.', 'connections' ) , '</h4>';

			// Add the Uncategorized category to all previous entries.
			$term = $connections->term->getTermBy( 'slug', 'uncategorized', 'category' );

			$entryIDs = $wpdb->get_col( "SELECT id FROM " . CN_ENTRY_TABLE );

			$termID[] = $term->term_taxonomy_id;

			foreach ( $entryIDs as $entryID ) {
				$connections->term->setTermRelationships( $entryID, $termID, 'category' );
			}

			$connections->options->setDBVersion( '0.1.0' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.1', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.1.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<h4>' , __( 'Setting all current entries to the "approved" status.', 'connections' ) , "</h4>\n";

			$wpdb->query( 'UPDATE ' . CN_ENTRY_TABLE . ' SET status = \'approved\'' );

			$connections->options->setDBVersion( '0.1.1' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.2', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.2.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<h4>' , __( 'Setting all current entries `entry_type` column.', 'connections' ) , "</h4>\n";

			$sql = 'SELECT DISTINCT `id`, `options` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				$options = array();
				$options = unserialize( $result->options );

				if ( isset( $options['entry']['type'] ) ) {
					$entryType = $options['entry']['type'];
				}
				else {
					$entryType = 'individual';
				}

				$wpdb->query( 'UPDATE ' . CN_ENTRY_TABLE . ' SET entry_type = \'' . $entryType . '\' WHERE id = ' . $result->id );
			}

			$connections->options->setDBVersion( '0.1.2' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.3', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.3.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';
			echo '<li>' , __( 'Changing column name from group_name to family_name...', 'connections' ) , "</li>\n";
			if ( cnAlterTable( CN_ENTRY_TABLE, 'family_name' , 'CHANGE COLUMN group_name family_name tinytext NOT NULL' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.3' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.4', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.4.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';
			echo '<li>' , __( 'Changing column name from honorable_prefix to honorific_prefix...', 'connections' ) , "</li>\n";
			if ( cnAlterTable( CN_ENTRY_TABLE, 'honorific_prefix' , 'CHANGE COLUMN honorable_prefix honorific_prefix tinytext NOT NULL' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Changing column name from honorable_suffix to honorific_suffix...', 'connections' ) , "</li>\n";
			if ( cnAlterTable( CN_ENTRY_TABLE, 'honorific_suffix' , 'CHANGE COLUMN honorable_suffix honorific_suffix tinytext NOT NULL' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.4' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.5', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.5.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_TABLE_META . "'" ) != CN_ENTRY_TABLE_META ) {
				echo '<li>' , __( 'Add the entry meta table.', 'connections' ) , "</li>\n";

				$entryTableMeta = "CREATE TABLE " . CN_ENTRY_TABLE_META . " (
			        meta_id bigint(20) unsigned NOT NULL auto_increment,
					entry_id bigint(20) unsigned NOT NULL default '0',
					meta_key varchar(255) default NULL,
					meta_value longtext,
					PRIMARY KEY  (meta_id),
					KEY entry_id (entry_id),
					KEY meta_key (meta_key)
			    ) $charsetCollate;";

				dbDelta( $entryTableMeta );
			}

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.5' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.6', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.6.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_ADDRESS_TABLE . "'" ) != CN_ENTRY_ADDRESS_TABLE ) {
				echo '<li>' , __( 'Add the entry address table.', 'connections' ) , "</li>\n";

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

				dbDelta( $entryTableAddress );
			}

			echo '<li>' , __( 'Porting addresses...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `addresses` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->addresses ) ) {
					$addresses = unserialize( $result->addresses );
					$order = 0;

					foreach ( (array) $addresses as $key => $address ) {
						if ( empty( $address['type'] ) ) $address['type'] = 'other';

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_ADDRESS_TABLE . ' SET
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
												`latitude`			= "%f",
												`longitude`			= "%f",
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_PHONE_TABLE . "'" ) != CN_ENTRY_PHONE_TABLE ) {
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

				dbDelta( $entryTablePhone );
			}

			echo '<li>' , __( 'Porting phone numbers...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `phone_numbers` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->phone_numbers ) ) {
					$phoneNumbers = unserialize( $result->phone_numbers );
					$order = 0;

					foreach ( (array) $phoneNumbers as $key => $phone ) {
						if ( empty( $phone['number'] ) ) continue;

						if ( empty( $phone['type'] ) ) $phone['type'] = 'homephone';

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_PHONE_TABLE . ' SET
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_EMAIL_TABLE . "'" ) != CN_ENTRY_EMAIL_TABLE ) {
				echo '<li>' , __( 'Add the entry email table.', 'connections' ) , "</li>\n";

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

				dbDelta( $entryTableEmail );
			}

			echo '<li>' , __( 'Porting email addresses...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `email` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->email ) ) {
					$emailAddresses = unserialize( $result->email );
					$order = 0;

					foreach ( (array) $emailAddresses as $key => $email ) {
						if ( empty( $email['address'] ) ) continue;

						if ( empty( $email['type'] ) ) $email['type'] = 'personal';

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_EMAIL_TABLE . ' SET
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_MESSENGER_TABLE . "'" ) != CN_ENTRY_MESSENGER_TABLE ) {
				echo '<li>' , __( 'Add the entry messenger table.', 'connections' ) , "</li>\n";

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

				dbDelta( $entryTableMessenger );
			}

			echo '<li>' , __( 'Porting IM IDs...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `im` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->im ) ) {
					$imIDs = unserialize( $result->im );
					$order = 0;

					foreach ( (array) $imIDs as $key => $network ) {
						if ( empty( $network['id'] ) ) continue;

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_MESSENGER_TABLE . ' SET
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_SOCIAL_TABLE . "'" ) != CN_ENTRY_SOCIAL_TABLE ) {
				echo '<li>' , __( 'Add the entry social media table.', 'connections' ) , "</li>\n";

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

				dbDelta( $entryTableSocialMedia );
			}

			echo '<li>' , __( 'Porting Social Media IDs...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `social` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->social ) ) {
					$socialMediaIDs = unserialize( $result->social );
					$order = 0;

					foreach ( (array) $socialMediaIDs as $key => $network ) {
						if ( empty( $network['url'] ) ) continue;

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_SOCIAL_TABLE . ' SET
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_LINK_TABLE . "'" ) != CN_ENTRY_LINK_TABLE ) {
				echo '<li>' , __( 'Add the entry link table.', 'connections' ) , "</li>\n";

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

				dbDelta( $entryTableLink );
			}

			echo '<li>' , __( 'Porting websites...', 'connections' ) , "</li>\n";

			$sql = 'SELECT DISTINCT `id`, `websites` FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				if ( ! empty( $result->websites ) ) {
					$websites = unserialize( $result->websites );
					$order = 0;

					foreach ( (array) $websites as $key => $website ) {
						if ( empty( $website['address'] ) ) continue;

						$sql = $wpdb->prepare ( 'INSERT INTO ' . CN_ENTRY_LINK_TABLE . ' SET
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
							'public' );

						$wpdb->query( $sql );
						$order++;
					}
				}
			}

			echo '<li>' , __( 'Changing column name from websites to links...', 'connections' ) , "</li>\n";
			if ( cnAlterTable( CN_ENTRY_TABLE , 'links' , 'CHANGE COLUMN websites links longtext NOT NULL' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding column... "slug"', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'slug', 'tinytext NOT NULL AFTER visibility' ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding entry slugs...', 'connections' ) , "</li>\n";

			$sql = 'SELECT id,
						CASE entry_type
						  WHEN \'individual\' THEN CONCAT_WS( \'-\' , LOWER(first_name) , LOWER(last_name) )
						  WHEN \'organization\' THEN LOWER(organization)
						  WHEN \'connection_group\' THEN LOWER(family_name)
						  WHEN \'family\' THEN LOWER(family_name)
						END AS slug_temp
					FROM ' . CN_ENTRY_TABLE;

			$results = $wpdb->get_results( $sql );

			foreach ( $results as $result ) {
				$slug = sanitize_title( $result->slug_temp );

				$query = $wpdb->prepare( 'SELECT slug FROM ' . CN_ENTRY_TABLE . ' WHERE slug = %s', $slug );

				if ( $wpdb->get_var( $query ) ) {
					$num = 2;
					do {
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
			if ( file_exists( ABSPATH . 'download.vCard.php' ) ) @unlink( ABSPATH . 'download.vCard.php' );

			// Create the cache folder.
			wp_mkdir_p( CN_CACHE_PATH );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0746 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0747 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0756 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0757 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0764 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0765 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0766 );
			if ( file_exists( CN_CACHE_PATH ) && ! is_writeable( CN_CACHE_PATH ) ) @chmod( CN_CACHE_PATH , 0767 );

			// Create the images folder.
			// wp_mkdir_p( CN_IMAGE_PATH );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0746 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0747 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0756 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0757 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0764 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0765 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0766 );
			if ( file_exists( CN_IMAGE_PATH ) && ! is_writeable( CN_IMAGE_PATH ) ) @chmod( CN_IMAGE_PATH , 0767 );

			// Create the custom template folder.
			wp_mkdir_p( CN_CUSTOM_TEMPLATE_PATH );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0746 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0747 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0756 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0757 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0764 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0765 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0766 );
			if ( file_exists( CN_CUSTOM_TEMPLATE_PATH ) && ! is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) @chmod( CN_CUSTOM_TEMPLATE_PATH , 0767 );

			$connections->options->setDBVersion( '0.1.6' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.7', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.7.', 'connections'  ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';

			echo '<li>' , __( 'Adding column "image" to the links table.', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_LINK_TABLE, 'image', "tinyint unsigned NOT NULL default '0' AFTER follow" ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding column "logo" to the links table.', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_LINK_TABLE, 'logo', "tinyint unsigned NOT NULL default '0' AFTER image" ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.7' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.8', '<' ) ) {
			$fields['fields_entry'] = array( 'family_name' ,
				'first_name' ,
				'middle_name' ,
				'last_name' ,
				'title' ,
				'organization' ,
				'department' ,
				'contact_first_name' ,
				'contact_last_name' ,
				'bio' ,
				'notes' );
			$fields['fields_address'] = array( 'line_1' ,
				'line_2' ,
				'line_3' ,
				'city' ,
				'state' ,
				'zipcode' ,
				'country' );
			$fields['fields_phone'] = array( 'number' );

			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.8.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<p><strong>' , __( 'NOTE', 'connections' ) , ':</strong> ' , __( 'You might receive this error: "The used table type doesn\'t support FULLTEXT indexes".', 'connections' ) , '</p>';

			echo '<p>' , __( 'This is not a critical error. What this means is that the database does not support FULLTEXT query statments. Connections will perform a secondary search query in order to return search results.', 'connections' ) , '</p>';
			echo '<ul>';

			echo '<li>Adding FULLTEXT to ' . CN_ENTRY_TABLE . ' ' . "</li>\n";
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' ADD FULLTEXT (' . implode( ',', $fields['fields_entry'] ) . ')' );

			echo '<li>Adding FULLTEXT to ' . CN_ENTRY_ADDRESS_TABLE . ' ' . "</li>\n";
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' ADD FULLTEXT (' . implode( ',', $fields['fields_address'] ) . ')' );

			echo '<li>Adding FULLTEXT to ' . CN_ENTRY_PHONE_TABLE . ' ' . "</li>\n";
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' ADD FULLTEXT (' . implode( ',', $fields['fields_phone'] ) . ')' );

			echo '</ul>';

			echo '<p>' , __( 'The activate action for Connections 0.7.2.2 was not properly updated which created a fatal bug for new installations of Connections. Checking for the missing table columns in the Links table and add them, if missing.', 'connections' ) , '</p>';
			echo '<ul>';

			echo '<li>' , __( 'Adding, if missing, column "image" to the links table.', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_LINK_TABLE, 'image', "tinyint unsigned NOT NULL default '0' AFTER follow" ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '<li>' , __( 'Adding, if missing, column "logo" to the links table.', 'connections' ) , "</li>\n";
			if ( cnAddTableColumn( CN_ENTRY_LINK_TABLE, 'logo', "tinyint unsigned NOT NULL default '0' AFTER image" ) ) echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.8' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.1.9', '<' ) ) {
			echo '<h4>' , sprintf( __( 'Upgrade from database version %1$s to database version 0.1.9.', 'connections' ) , $connections->options->getDBVersion() ) , "</h4>\n";

			echo '<ul>';

			if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_DATE_TABLE . "'" ) != CN_ENTRY_DATE_TABLE ) {

				echo '<li>' , __( 'Add the date table.', 'connections' ) , "</li>\n";

				$entryTableDate = "CREATE TABLE " . CN_ENTRY_DATE_TABLE . " (
			        `id` bigint(20) unsigned NOT NULL auto_increment,
					`entry_id` bigint(20) unsigned NOT NULL default '0',
					`order` tinyint unsigned NOT NULL default '0',
					`preferred` tinyint unsigned NOT NULL default '0',
					`type` tinytext NOT NULL,
					`date` date NOT NULL default '0000-00-00',
					`visibility` tinytext NOT NULL,
					PRIMARY KEY (`id`, `entry_id`)
			    ) $charsetCollate";

				// Create the table
				dbDelta( $entryTableDate );
			}

			echo '<li>' , __( 'Adding column... "user"', 'connections' ) , "</li>\n";

			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'user', 'tinytext NOT NULL AFTER owner' ) ) {

				echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';
			}

			echo '<li>' , __( 'Adding column... "dates"', 'connections' ) , "</li>\n";

			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'dates', 'longtext NOT NULL AFTER links' ) ) {

				echo '<ul><li>' , __( 'SUCCESS', 'connections' ) , '</li></ul>';
			}

			echo '</ul>';

			$connections->options->setDBVersion( '0.1.9' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.2', '<' ) ) {

			cnSchema::create();

			$connections->options->setDBVersion( '0.2' );

			// Save the options
			$connections->options->saveOptions();
		}

		if ( version_compare( $dbVersion, '0.3', '<' ) ) {

			echo '<h4>' , sprintf( esc_html__( 'Upgrade from database version %1$s to database version 0.3.', 'connections' ) , $connections->options->getDBVersion() ) , '</h4>' . PHP_EOL;

			echo '<ul>' . PHP_EOL;

			echo '<li>' , esc_html__( 'Adding column... "ordo" (Latin for order. Correct Spelling is ōrdō)', 'connections' ) , '</li>' . PHP_EOL;

			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'ordo', 'int(11) NOT NULL default \'0\' AFTER date_added' ) ) {

				echo '<ul><li>' , esc_html__( 'SUCCESS', 'connections' ) , '</li></ul>' . PHP_EOL;

				$connections->options->setDBVersion( '0.3' );

				// Save the options
				$connections->options->saveOptions();
			}

			echo '</ul>' . PHP_EOL;
		}

		if ( version_compare( $dbVersion, '0.4', '<' ) ) {

			echo '<h4>' , sprintf( esc_html__( 'Upgrade from database version %1$s to database version %2$s.', 'connections' ) , $connections->options->getDBVersion(), CN_DB_VERSION ) , '</h4>' . PHP_EOL;

			echo '<ul>' . PHP_EOL;

			echo '<li>' , esc_html__( 'Adding columns... "line_4", "district" and "county"', 'connections' ) , '</li>' . PHP_EOL;

			if ( cnAddTableColumn( CN_ENTRY_ADDRESS_TABLE, 'line_4', 'tinytext NOT NULL AFTER line_3' ) &&
			     cnAddTableColumn( CN_ENTRY_ADDRESS_TABLE, 'district', 'tinytext NOT NULL AFTER line_4' ) &&
			     cnAddTableColumn( CN_ENTRY_ADDRESS_TABLE, 'county', 'tinytext NOT NULL AFTER district' )
			     ) {

				echo '<ul><li>' , esc_html__( 'SUCCESS', 'connections' ) , '</li></ul>' . PHP_EOL;

				$connections->options->setDBVersion( '0.4' );

				// Save the options
				$connections->options->saveOptions();
			}

			echo '</ul>' . PHP_EOL;
		}

		if ( version_compare( $dbVersion, '0.5', '<' ) ) {

			echo '<h4>' , sprintf( esc_html__( 'Upgrade from database version %1$s to database version %2$s.', 'connections' ) , $connections->options->getDBVersion(), CN_DB_VERSION ) , '</h4>' . PHP_EOL;

			echo '<ul>' . PHP_EOL;

			echo '<li>' , esc_html__( 'Adding columns... "excerpt"', 'connections' ) , '</li>' . PHP_EOL;

			if ( cnAddTableColumn( CN_ENTRY_TABLE, 'excerpt', 'text NOT NULL AFTER notes' ) ) {

				echo '<ul><li>' , esc_html__( 'SUCCESS', 'connections' ) , '</li></ul>' . PHP_EOL;

				$connections->options->setDBVersion( '0.5' );

				// Save the options
				$connections->options->saveOptions();
			}

			echo '</ul>' . PHP_EOL;
		}

		if ( version_compare( $dbVersion, '0.6', '<' ) ) {

			echo '<h4>' , sprintf( esc_html__( 'Upgrade from database version %1$s to database version %2$s.', 'connections' ) , $connections->options->getDBVersion(), CN_DB_VERSION ) , '</h4>' . PHP_EOL;

			echo '<ul>' . PHP_EOL;

			echo '<li>' , esc_html__( 'Update terms slug index.', 'connections' ) , '</li>' . PHP_EOL;

			$wpdb->query( 'ALTER TABLE ' . CN_TERMS_TABLE . ' DROP INDEX slug, ADD INDEX slug(slug(191))' );

			$connections->options->setDBVersion( '0.6' );

			// Save the options
			$connections->options->saveOptions();

			echo '</ul>' . PHP_EOL;
		}

		echo '<h4>' , __( 'Upgrade completed.', 'connections' ) , "</h4>\n";
		echo '<h4><a class="button-primary" href="' . esc_url( $urlPath ) . '">' , __( 'Continue', 'connections' ) , '</a></h4>';

		$wpdb->hide_errors();
	}
}

function cnAlterTable( $tableName, $columnName, $sql ) {
	global $wpdb;

	foreach ( $wpdb->get_col( "SHOW COLUMNS FROM $tableName" ) as $column ) {
		if ( $column == $columnName ) return TRUE;
	}

	// didn't find it try to create it.
	return $wpdb->query( 'ALTER TABLE ' . $tableName . ' ' . $sql );
}

/**
 * Add a new column.
 * Example : cnAddTableColumn( CN_ENTRY_TABLE, 'status', "varchar(20) NOT NULL");
 *
 * Credit WordPress plug-in NGG.
 *
 * @param string  $tableName  Database table name.
 * @param string  $columnName Database column name to create.
 * @param string  $sql        SQL statement to create column
 * @return bool
 */
function cnAddTableColumn( $tableName, $columnName, $sql ) {
	global $wpdb;

	foreach ( $wpdb->get_col( "SHOW COLUMNS FROM $tableName" ) as $column ) {
		if ( $column == $columnName ) return TRUE;
	}

	// didn't find it try to create it.
	$wpdb->query( 'ALTER TABLE ' . $tableName . ' ADD ' . $columnName . ' ' . $sql );

	// we cannot directly tell that whether this succeeded!
	foreach ( $wpdb->get_col( "SHOW COLUMNS FROM $tableName" ) as $column ) {
		if ( $column == $columnName ) return TRUE;
	}

	echo '<ul><li><strong>' , sprintf( __( 'Could not add column %1$s in table %2$s.', 'connections' ), $columnName, $tableName ), "</li></strong></ul>\n";

	return FALSE;
}

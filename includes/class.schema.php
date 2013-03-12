<?php

/**
 * Class for building the custom schema for Connections and set its intial defaults.
 *
 * @TODO Add static methods for handling db updates.
 *
 * @package     Connections
 * @subpackage  Schema
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class cnSchema {

	/**
	 * Empty contructor.
	 */
	private function __construct() { /* Do Nothing Here. */ }

	/**
	 * Init the default db schema. Create the required tables, populate the default values and set the FULLTEXT indexes.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	public static function create() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = array();

		/*
		 * Build the query to be passed to dbDelta.
		 * The query being built is based on if the tables exists or not.
		 */
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_TABLE . "'" ) != CN_ENTRY_TABLE ) $sql[] = self::entry();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_TABLE_META . "'" ) != CN_ENTRY_TABLE_META ) $sql[] = self::entryMeta();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_TERMS_TABLE . "'" ) != CN_TERMS_TABLE ) $sql[] = self::terms();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_TERM_TAXONOMY_TABLE . "'" ) != CN_TERM_TAXONOMY_TABLE ) $sql[] = self::termTaxonomy();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_TERM_RELATIONSHIP_TABLE . "'" ) != CN_TERM_RELATIONSHIP_TABLE ) $sql[] = self::termRelationship();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_ADDRESS_TABLE . "'" ) != CN_ENTRY_ADDRESS_TABLE ) $sql[] = self::addresses();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_PHONE_TABLE . "'" ) != CN_ENTRY_PHONE_TABLE ) $sql[] = self::phone();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_EMAIL_TABLE . "'" ) != CN_ENTRY_EMAIL_TABLE ) $sql[] = self::email();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_MESSENGER_TABLE . "'" ) != CN_ENTRY_MESSENGER_TABLE ) $sql[] = self::messenger();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_SOCIAL_TABLE . "'" ) != CN_ENTRY_SOCIAL_TABLE ) $sql[] = self::socialMedia();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_LINK_TABLE . "'" ) != CN_ENTRY_LINK_TABLE ) $sql[] = self::links();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_ENTRY_DATE_TABLE . "'" ) != CN_ENTRY_DATE_TABLE ) $sql[] = self::dates();

		// Create the tables.
		if ( ! empty( $sql ) ) dbDelta( implode( ';', $sql ) );

		/*
		 * Alter the tables after they are created to add FULLTEXT support.
		 * That way if the db engine doesn't support it, at least the tables will be created.
		 *
		 * @TODO There should be a query that can be used to check the db engine before running this.
		 */
		self::addFULLTEXT();

		/*
		 * Add the default "Uncateforized" category.
		 */
		self::addDefaultCategory();
	}

	/**
	 * Add the default FULLTEXT indexes.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function addFULLTEXT() {
		global $wpdb;

		/*
		 * We'll use empty() for checking the Key_name because $wpdb->query() can return both FALSE and 0.
		 */

		$result = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_TABLE . ' WHERE Key_name = \'search\'' );
		if ( empty( $result ) )
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_TABLE . ' ADD FULLTEXT search (family_name, first_name, middle_name, last_name, title, organization, department, contact_first_name, contact_last_name, bio, notes)' );

		$result = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_ADDRESS_TABLE . ' WHERE Key_name = \'search\'' );
		if ( empty( $result ) )
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_ADDRESS_TABLE . ' ADD FULLTEXT search (line_1, line_2, line_3, city, state, zipcode, country)' );

		$result = $wpdb->query( 'SHOW INDEX FROM ' . CN_ENTRY_PHONE_TABLE . ' WHERE Key_name = \'search\'' );
		if ( empty( $result ) )
			$wpdb->query( 'ALTER TABLE ' . CN_ENTRY_PHONE_TABLE . ' ADD FULLTEXT search (number)' );
	}

	/**
	 * Add the "Uncategorized" category"
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function addDefaultCategory() {
		global $connections;

		// Check if the Uncategorized term exists and if it doesn't create it.
		$term = $connections->term->getTermBy( 'slug', 'uncategorized', 'category' );

		if ( ! $term ) {
			$attributes['slug'] = '';
			$attributes['parent'] = 0;
			$attributes['description'] = __( 'Entries not assigned to a category will automatically be assigned to this category and deleting a category which has been assigned to an entry will reassign that entry to this category. This category can not be edited or deleted.', 'connections' ) ;

			$connections->term->addTerm( __( 'Uncategorized', 'connections' ) , 'category', $attributes );
		}
	}

	/**
	 * Build the query to create the main entry table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function entry() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_TABLE;
		$sql[] = "(
			id bigint(20) NOT NULL AUTO_INCREMENT,
			ts TIMESTAMP,
			date_added tinytext NOT NULL,
			entry_type tinytext NOT NULL,
			visibility tinytext NOT NULL,
			slug tinytext NOT NULL,
			family_name tinytext NOT NULL,
			honorific_prefix tinytext NOT NULL,
			first_name tinytext NOT NULL,
			middle_name tinytext NOT NULL,
			last_name tinytext NOT NULL,
			honorific_suffix tinytext NOT NULL,
			title tinytext NOT NULL,
			organization tinytext NOT NULL,
			department tinytext NOT NULL,
			contact_first_name tinytext NOT NULL,
			contact_last_name tinytext NOT NULL,
			addresses longtext NOT NULL,
			phone_numbers longtext NOT NULL,
			email longtext NOT NULL,
			im longtext NOT NULL,
			social longtext NOT NULL,
			links longtext NOT NULL,
			dates longtext NOT NULL,
			birthday tinytext NOT NULL,
			anniversary tinytext NOT NULL,
			bio longtext NOT NULL,
			notes longtext NOT NULL,
			options longtext NOT NULL,
			added_by bigint(20) NOT NULL,
			edited_by bigint(20) NOT NULL,
			owner bigint(20) NOT NULL,
			user bigint(20) NOT NULL,
			status varchar(20) NOT NULL,
			PRIMARY KEY  (id)
			)";

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the entry meta table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function entryMeta() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_TABLE_META;
		$sql[] = "(
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY entry_id (entry_id),
			KEY meta_key (meta_key)
			)";

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the terms table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function terms() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERMS_TABLE;
		$sql[] = "(
			term_id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL,
			slug varchar(200) NOT NULL,
			term_group bigint(10) NOT NULL,
			PRIMARY KEY  (term_id),
			UNIQUE KEY slug (slug),
			INDEX name (name)
			)";

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the term taxonomy table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function termTaxonomy() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERM_TAXONOMY_TABLE;
		$sql[] = "(
			term_taxonomy_id bigint(20) NOT NULL AUTO_INCREMENT,
			term_id bigint(20) NOT NULL,
			taxonomy varchar(32) NOT NULL,
			description longtext NOT NULL,
			parent bigint(20) NOT NULL,
			count bigint(20) NOT NULL,
			PRIMARY KEY  (term_taxonomy_id),
			UNIQUE KEY term_id_taxonomy (term_id, taxonomy),
			INDEX taxonomy (taxonomy)
			)";

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the terms relationships table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function termRelationship() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERM_RELATIONSHIP_TABLE;
		$sql[] = "(
			entry_id bigint(20) NOT NULL,
			term_taxonomy_id bigint(20) NOT NULL,
			term_order int(11) NOT NULL,
			PRIMARY KEY  (entry_id,term_taxonomy_id),
			KEY term_taxonomy_id (term_taxonomy_id)
			)";

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the addresses table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function addresses() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_ADDRESS_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			line_1 tinytext NOT NULL,
			line_2 tinytext NOT NULL,
			line_3 tinytext NOT NULL,
			city tinytext NOT NULL,
			state tinytext NOT NULL,
			zipcode tinytext NOT NULL,
			country tinytext NOT NULL,
			latitude decimal(15,12) default NULL,
			longitude decimal(15,12) default NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id,entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the phone numbers table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function phone() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_PHONE_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			number tinytext NOT NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY (id,entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the email addresses table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function email() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_EMAIL_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			address tinytext NOT NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id,entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the instant messenger table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function messenger() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_MESSENGER_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			uid tinytext NOT NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id, entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the social media links table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function socialMedia() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_SOCIAL_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			url tinytext NOT NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id, entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the links table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function links() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_LINK_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			title tinytext NOT NULL,
			url tinytext NOT NULL,
			target tinytext NOT NULL,
			follow tinyint unsigned NOT NULL default 0,
			image tinyint unsigned NOT NULL default 0,
			logo tinyint unsigned NOT NULL default 0,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id, entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the dates table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since 0.7.5
	 * @return void
	 */
	private static function dates() {
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_DATE_TABLE;
		$sql[] = '(
			id bigint(20) unsigned NOT NULL auto_increment,
			entry_id bigint(20) unsigned NOT NULL default 0,
			`order` tinyint unsigned NOT NULL default 0,
			preferred tinyint unsigned NOT NULL default 0,
			type tinytext NOT NULL,
			date date NOT NULL default \'0000-00-00\',
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id, entry_id)
			)';

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

}
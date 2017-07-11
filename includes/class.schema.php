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

/**
 * Class cnSchema
 *
 * @since  0.7.5
 */
class cnSchema {

	/**
	 * Empty constructor.
	 */
	private function __construct() { /* Do Nothing Here. */ }

	/**
	 * Return default table engine based db version.
	 *
	 * Connections uses FULLTEXT indices for search.
	 * FULLTEXT indices were not supported in INNODB until version 5.6.4.
	 * Since InnoDB is preferred but require FULLTEXT indices, fallback to MyISAM
	 * as appropriate  based on the db version.
	 *
	 * @return string The table engine.
	 */
	private static function getEngine() {

		/** @var $wpdb wpdb */
		global $wpdb;

		if ( version_compare( $wpdb->db_version(), '5.6.4', '>=' ) ) {

			$engine = 'InnoDB';

		} else {

			$engine = 'MyISAM';
		}

		return $engine;
	}

	/**
	 * Init the default db schema. Create the required tables, populate the default values and set the FULLTEXT indexes.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return void
	 */
	public static function create() {

		/** @var $wpdb wpdb */
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
		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . CN_TERM_META_TABLE . "'" ) != CN_TERM_META_TABLE ) $sql[] = self::termMeta();
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
		 * Add the default "Uncategorized" category.
		 */
		self::addDefaultCategory();
	}

	/**
	 * Add the default FULLTEXT indexes.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return void
	 */
	private static function addFULLTEXT() {

		/** @var $wpdb wpdb */
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
	 * Create the default category.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return void
	 */
	private static function addDefaultCategory() {

		/**
		 * Simply calling @see cnOptions::getDefaultCategoryID() is sufficient for creating the default category.
		 * This is deu to the logic within the method. If a default category is not found or set, it will create
		 * an "Uncategorized" category and set it as the default category.
		 */
		cnOptions::getDefaultCategoryID();
	}

	/**
	 * Build the query to create the main entry table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function entry() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_ENTRY_TABLE;
		$sql[] = "(
			id bigint(20) NOT NULL AUTO_INCREMENT,
			ts TIMESTAMP,
			date_added tinytext NOT NULL,
			ordo int(11) NOT NULL default '0',
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
			excerpt text NOT NULL,
			options longtext NOT NULL,
			added_by bigint(20) NOT NULL,
			edited_by bigint(20) NOT NULL,
			owner bigint(20) NOT NULL,
			user bigint(20) NOT NULL,
			status varchar(20) NOT NULL,
			PRIMARY KEY  (id)
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the entry meta table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function entryMeta() {

		/** @var $wpdb wpdb */
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
			KEY meta_key (meta_key(191))
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the terms table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function terms() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERMS_TABLE;
		$sql[] = "(
			term_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			name varchar(200) NOT NULL default '',
			slug varchar(200) NOT NULL default '',
			term_group bigint(10) NOT NULL default 0,
			PRIMARY KEY  (term_id),
			KEY slug (slug(191)),
			KEY name (name(191))
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the term taxonomy table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function termTaxonomy() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERM_TAXONOMY_TABLE;
		$sql[] = "(
			term_taxonomy_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term_id bigint(20) NOT NULL default 0,
			taxonomy varchar(32) NOT NULL default '',
			description longtext NOT NULL,
			parent bigint(20) unsigned NOT NULL default 0,
			count bigint(20) NOT NULL default 0,
			PRIMARY KEY  (term_taxonomy_id),
			UNIQUE KEY term_id_taxonomy (term_id, taxonomy),
			INDEX taxonomy (taxonomy)
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the terms relationships table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function termRelationship() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERM_RELATIONSHIP_TABLE;
		$sql[] = "(
			entry_id bigint(20) unsigned NOT NULL default 0,
			term_taxonomy_id bigint(20) unsigned NOT NULL default 0,
			term_order int(11) NOT NULL default 0,
			PRIMARY KEY  (entry_id,term_taxonomy_id),
			KEY term_taxonomy_id (term_taxonomy_id)
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the terms  meta table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  8.1.7
	 *
	 * @return string
	 */
	private static function termMeta() {

		/** @var $wpdb wpdb */
		global $wpdb;

		$sql = array();

		$sql[] = 'CREATE TABLE';
		$sql[] = CN_TERM_META_TABLE;
		$sql[] = "(
			meta_id bigint(20) unsigned NOT NULL auto_increment,
			term_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) default NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY term_id (term_id),
			KEY meta_key (meta_key(191))
			)";

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the addresses table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function addresses() {

		/** @var $wpdb wpdb */
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
			line_4 tinytext NOT NULL,
			district tinytext NOT NULL,
			county tinytext NOT NULL,
			city tinytext NOT NULL,
			state tinytext NOT NULL,
			zipcode tinytext NOT NULL,
			country tinytext NOT NULL,
			latitude decimal(15,12) default NULL,
			longitude decimal(15,12) default NULL,
			visibility tinytext NOT NULL,
			PRIMARY KEY  (id,entry_id)
			)';

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the phone numbers table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function phone() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the email addresses table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function email() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the instant messenger table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function messenger() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the social media links table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function socialMedia() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the links table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function links() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

	/**
	 * Build the query to create the dates table.
	 * NOTE: String is formatted to be dbDelta() compatible.
	 *
	 * @access private
	 * @since  0.7.5
	 *
	 * @return string
	 */
	private static function dates() {

		/** @var $wpdb wpdb */
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

		$sql[] = 'ENGINE=' . self::getEngine();

		if ( ! empty( $wpdb->charset ) ) $sql[] = 'DEFAULT CHARACTER SET ' .  $wpdb->charset;
		if ( ! empty( $wpdb->collate ) ) $sql[] = 'COLLATE ' . $wpdb->collate;

		return implode( ' ', $sql );
	}

}

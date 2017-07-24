### Begin System Info ###

-- Site Info

Site URL:                   <?php echo site_url() . PHP_EOL; ?>
Home URL:                   <?php echo home_url() . PHP_EOL; ?>
Multisite:                  <?php echo is_multisite() ? 'Yes' : 'No'; ?>
<?php do_action( 'cn_sysinfo_after_site_info' ); ?>

<?php if ( $host ) : ?>

-- Hosting Provider

Host:                       <?php echo $host; ?>

<?php do_action( 'cn_sysinfo_after_host_info' ); ?>
<?php endif; ?>

-- Webserver Configuration
<?php

$mySQLMode = $wpdb->get_results( 'SELECT @@sql_mode' );
if ( is_array( $mySQLMode ) ) $sqlMode = $mySQLMode[0]->{'@@sql_mode'};
?>

Operating System:           <?php echo PHP_OS; ?>&nbsp;(<?php echo PHP_INT_SIZE * 8?>&nbsp;Bit)
PHP Version:                <?php echo PHP_VERSION . PHP_EOL; ?>
MySQL Version:              <?php echo $wpdb->db_version() . PHP_EOL; ?>
SQL Mode:                   <?php echo ( isset( $sqlMode ) && ! empty( $sqlMode ) ? $sqlMode : 'Not Set' ) . PHP_EOL; ?>
Webserver Info:             <?php echo $_SERVER['SERVER_SOFTWARE'] . PHP_EOL; ?>
<?php do_action( 'cn_sysinfo_after_webserver_config' ); ?>

-- PHP Configuration

Safe Mode:                  <?php echo ( ini_get( 'safe_mode' ) ? 'Enabled' : 'Disabled' ) . PHP_EOL; ?>
Memory Limit:               <?php echo ini_get( 'memory_limit' ) . PHP_EOL; ?>
Memory Used:                <?php echo ( function_exists( 'memory_get_usage' ) ? round( memory_get_usage() / 1024 / 1024, 2 ) . 'MB' : 'Unknown' ) . PHP_EOL; ?>
Upload Max Size:            <?php echo ini_get( 'upload_max_filesize' ) . PHP_EOL; ?>
Post Max Size:              <?php echo ini_get( 'post_max_size' ) . PHP_EOL; ?>
Upload Max Filesize:        <?php echo ini_get( 'upload_max_filesize' ) . PHP_EOL; ?>
Time Limit:                 <?php echo ini_get( 'max_execution_time' ) . PHP_EOL; ?>
Max Input Vars:             <?php echo ini_get( 'max_input_vars' ) . PHP_EOL; ?>
Allow URL fopen:            <?php echo cnFormatting::toYesNo( ini_get( 'allow_url_fopen' ) ) . PHP_EOL; ?>
PCRE Backtrack Limit        <?php echo ini_get( 'pcre.backtrack_limit' ). PHP_EOL; ?>
Display Errors:             <?php echo ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . PHP_EOL; ?>
PHP Arg Seperator:          <?php echo ini_get( 'arg_separator.output' ). PHP_EOL; ?>
<?php do_action( 'cn_sysinfo_after_php_config' ); ?>

-- PHP Extensions

cURL:                       <?php echo ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . PHP_EOL; ?>
fsockopen:                  <?php echo ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . PHP_EOL; ?>
SOAP Client:                <?php echo ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . PHP_EOL; ?>
Suhosin:                    <?php echo ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . PHP_EOL; ?>
Exif:                       <?php echo ( is_callable( 'exif_read_data' ) ? 'Version: ' . substr( phpversion( 'exif' ), 0, 4 ) : 'Not Installed' ) . PHP_EOL ; ?>
IPTC Parse:                 <?php echo ( is_callable( 'iptcparse' )  ? 'Installed' : 'Not Installed' ) . PHP_EOL; ?>
XML Parse:                  <?php echo ( is_callable( 'xml_parser_create' ) ? 'Installed' : 'Not Installed' ) . PHP_EOL; ?>
<?php do_action( 'cn_sysinfo_after_php_ext' ); ?>

-- GD Support

<?php

if ( function_exists( 'gd_info' ) ) {

	$info  = gd_info();
	$keys  = array_keys( $info );
	$count = count( $keys );

	for ( $i = 0; $i < $count; $i ++ ) {

		if ( is_bool( $info[ $keys[ $i ] ] ) ) {

			echo str_pad( $keys[ $i ] . ':', 34, ' ', STR_PAD_RIGHT ) . cnFormatting::toYesNo( $info[ $keys[ $i ] ] ) . PHP_EOL;

		} else {

			echo str_pad( $keys[ $i ] . ':', 34, ' ', STR_PAD_RIGHT ) . $info[ $keys[ $i ] ] . PHP_EOL;
		}
	}

} else {

	echo 'GD Not Installed.' . PHP_EOL;
}

?>

-- Session Configuration

Session:                    <?php echo ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . PHP_EOL; ?>
<?php if ( isset( $_SESSION ) ) : ?>
Session Name:               <?php echo esc_html( ini_get( 'session.name' ) ) . PHP_EOL; ?>
Cookie Path:                <?php echo esc_html( ini_get( 'session.cookie_path' ) ) . PHP_EOL; ?>
Save Path:                  <?php echo esc_html( ini_get( 'session.save_path' ) ) . PHP_EOL; ?>
Use Cookies:                <?php echo ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . PHP_EOL; ?>
Use Only Cookies:           <?php echo ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . PHP_EOL; ?>
<?php endif; ?>
<?php do_action( 'cn_sysinfo_after_session_config' ); ?>

-- User Browser
<?php /** @var Browser $browser */ ?>

Platform:                   <?php echo esc_html( $browser->getPlatform() ) . PHP_EOL; ?>
Name:                       <?php echo esc_html( $browser->getBrowser() ) . PHP_EOL; ?>
Version:                    <?php echo esc_html( $browser->getVersion() ) . PHP_EOL; ?>
User Agent String:          <?php echo esc_html( $browser->getUserAgent() ) . PHP_EOL; ?>
<?php do_action( 'cn_sysinfo_after_user_browser' ); ?>

-- WordPress Configuration
<?php $locale = get_locale(); ?>
Version:                    <?php echo get_bloginfo( 'version' ) . PHP_EOL; ?>
Language:                   <?php echo ( ! empty( $locale ) ? $locale : 'en_US' ) . PHP_EOL; ?>
ABSPATH                     <?php echo ABSPATH . PHP_EOL ?>
Permalink Structure:        <?php echo ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . PHP_EOL; ?>
Active Theme:               <?php echo $theme . PHP_EOL; ?>
<?php
if ( $parent_theme !== $theme ) : ?>
Parent Theme:               <?php echo $parent_theme . PHP_EOL; ?>
<?php endif; ?>
Show On Front:              <?php echo get_option( 'show_on_front' ) . PHP_EOL; ?>
<?php
//Only show page specs if front page is set to 'page'
if ( 'page' == get_option( 'show_on_front' ) ) :
$front_page_id = get_option( 'page_on_front' );
$blog_page_id  = get_option( 'page_for_posts' );
?>
Page On Front:              <?php echo ( $front_page_id != 0 ? get_the_title( $front_page_id ) . ' (#' . $front_page_id . ')' : 'Unset' ) . PHP_EOL; ?>
Page For Posts:             <?php echo ( $blog_page_id != 0 ? get_the_title( $blog_page_id ) . ' (#' . $blog_page_id . ')' : 'Unset' ) . PHP_EOL; ?>
<?php endif; ?>
<?php
// Make sure wp_remote_post() is working
$params = array(
	'sslverify'  => cnHTTP::verifySSL(),
	'timeout'    => 60,
	'user-agent' => 'CN/' . CN_CURRENT_VERSION,
	'body'       => '_notify-validate'
);

$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
	$WP_REMOTE_POST = 'wp_remote_post() works';
} else {
	$WP_REMOTE_POST = 'wp_remote_post() does not work';
} ?>
Remote Post:                <?php echo $WP_REMOTE_POST . PHP_EOL; ?>
Table Prefix:               <?php echo 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . PHP_EOL; ?>
Admin AJAX:                 <?php echo /*( cn_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . */PHP_EOL; ?>
WP_DEBUG:                   <?php echo ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . PHP_EOL; ?>
Memory Limit:               <?php echo WP_MEMORY_LIMIT . PHP_EOL; ?>
Max Memory Limit:           <?php echo WP_MAX_MEMORY_LIMIT . PHP_EOL; ?>
Registered Post Stati:      <?php echo implode( ', ', get_post_stati() ) . PHP_EOL; ?>
<?php do_action( 'cn_sysinfo_after_wordpress_config' ); ?>

-- Connections Configuration

Version:                    <?php echo $instance->options->getVersion() . PHP_EOL; ?>
DB Version:                 <?php echo $instance->options->getDBVersion() . PHP_EOL; ?>

CN_MULTISITE_ENABLED:       <?php echo CN_MULTISITE_ENABLED ? __( 'TRUE', 'connections') . PHP_EOL : __( 'FALSE', 'connections' ) . PHP_EOL; ?>
CN_DIR_NAME:                <?php echo CN_DIR_NAME . PHP_EOL; ?>
CN_BASE_NAME:               <?php echo CN_BASE_NAME . PHP_EOL; ?>
CN_PATH:                    <?php echo CN_PATH . PHP_EOL; ?>
CN_URL:                     <?php echo CN_URL . PHP_EOL; ?>
CN_RELATIVE_URL:            <?php echo CN_RELATIVE_URL . PHP_EOL; ?>
CN_IMAGE_PATH:              <?php echo CN_IMAGE_PATH . PHP_EOL; ?>
CN_IMAGE_BASE_URL:          <?php echo CN_IMAGE_BASE_URL . PHP_EOL; ?>
CN_IMAGE_RELATIVE_URL:      <?php echo CN_IMAGE_RELATIVE_URL . PHP_EOL; ?>
CN_TEMPLATE_PATH:           <?php echo CN_TEMPLATE_PATH . PHP_EOL; ?>
CN_TEMPLATE_URL:            <?php echo CN_TEMPLATE_URL . PHP_EOL; ?>
CN_TEMPLATE_RELATIVE_URL:   <?php echo CN_TEMPLATE_RELATIVE_URL . PHP_EOL; ?>

-- Connections Table Structure

CN_ENTRY_TABLE:             <?php echo CN_ENTRY_TABLE . PHP_EOL; ?>
CN_ENTRY_ADDRESS_TABLE:     <?php echo CN_ENTRY_ADDRESS_TABLE . PHP_EOL; ?>
CN_ENTRY_PHONE_TABLE:       <?php echo CN_ENTRY_PHONE_TABLE . PHP_EOL; ?>
CN_ENTRY_EMAIL_TABLE:       <?php echo CN_ENTRY_EMAIL_TABLE . PHP_EOL; ?>
CN_ENTRY_MESSENGER_TABLE:   <?php echo CN_ENTRY_MESSENGER_TABLE . PHP_EOL; ?>
CN_ENTRY_SOCIAL_TABLE:      <?php echo CN_ENTRY_SOCIAL_TABLE . PHP_EOL; ?>
CN_ENTRY_LINK_TABLE:        <?php echo CN_ENTRY_LINK_TABLE . PHP_EOL; ?>
CN_ENTRY_DATE_TABLE:        <?php echo CN_ENTRY_DATE_TABLE . PHP_EOL; ?>
CN_ENTRY_TABLE_META:        <?php echo CN_ENTRY_TABLE_META . PHP_EOL; ?>
CN_TERMS_TABLE:             <?php echo CN_TERMS_TABLE . PHP_EOL; ?>
CN_TERM_TAXONOMY_TABLE:     <?php echo CN_TERM_TAXONOMY_TABLE . PHP_EOL; ?>
CN_TERM_RELATIONSHIP_TABLE: <?php echo CN_TERM_RELATIONSHIP_TABLE . PHP_EOL; ?>

DESCRIBE <?php echo CN_ENTRY_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_ADDRESS_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_ADDRESS_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_PHONE_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_PHONE_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_EMAIL_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_EMAIL_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_MESSENGER_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_MESSENGER_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_SOCIAL_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_SOCIAL_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_LINK_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_LINK_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_DATE_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_DATE_TABLE ); ?>

DESCRIBE <?php echo CN_ENTRY_TABLE_META . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_ENTRY_TABLE_META ); ?>

DESCRIBE <?php echo CN_TERMS_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_TERMS_TABLE ); ?>

DESCRIBE <?php echo CN_TERM_TAXONOMY_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_TERM_TAXONOMY_TABLE ); ?>

DESCRIBE <?php echo CN_TERM_RELATIONSHIP_TABLE . PHP_EOL; ?>
<?php echo cnSystem_Info::describeTable( CN_TERM_RELATIONSHIP_TABLE ); ?>

-- Connections Folder Permissions

Image Path Exists:          <?php echo cnFormatting::toYesNo( is_dir( CN_IMAGE_PATH ) ) . PHP_EOL; ?>
Image Path Writeable:       <?php echo cnFormatting::toYesNo( is_writeable( CN_IMAGE_PATH ) ) . PHP_EOL; ?>

Template Path Exists:       <?php echo cnFormatting::toYesNo( is_dir( CN_CUSTOM_TEMPLATE_PATH ) ) . PHP_EOL; ?>
Template Path Writeable:    <?php echo cnFormatting::toYesNo( is_writeable( CN_CUSTOM_TEMPLATE_PATH ) ) . PHP_EOL; ?>

Cache Path Exists:          <?php echo cnFormatting::toYesNo( is_dir( CN_CACHE_PATH ) ) . PHP_EOL; ?>
Cache Path Writeable:       <?php echo cnFormatting::toYesNo( is_writeable( CN_CACHE_PATH ) ) . PHP_EOL; ?>
<?php
// Get plugins that have an update
$updates = get_plugin_updates();

// Must-use plugins
$muplugins = get_mu_plugins();

if ( 0 < count( $muplugins ) ) : ?>
-- Must-Use Plugins

<?php foreach ( $muplugins as $plugin => $plugin_data ) {
	echo $plugin_data['Name'] . ': ' . $plugin_data['Version'] . PHP_EOL;
}

do_action( 'cn_sysinfo_after_wordpress_mu_plugins' );

endif; ?>

-- WordPress Active Plugins

<?php
$plugins        = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {

	if ( ! in_array( $plugin_path, $active_plugins ) ) {
		continue;
	}

	$update = array_key_exists( $plugin_path, $updates ) ? ' (Update Available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';

	echo $plugin['Name'] . ': ' . $plugin['Version'] . $update . PHP_EOL;
}

do_action( 'cn_sysinfo_after_wordpress_plugins' );
?>

-- WordPress Inactive Plugins

<?php
foreach ( $plugins as $plugin_path => $plugin ) {

	if ( in_array( $plugin_path, $active_plugins ) ) {
		continue;
	}

	$update = array_key_exists( $plugin_path, $updates ) ? ' (Update Available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';

	echo $plugin['Name'] . ': ' . $plugin['Version'] . $update . PHP_EOL;
}

do_action( 'cn_sysinfo_after_wordpress_plugins_inactive' );

// WordPress Multisite active plugins
if ( is_multisite() ) : ?>

-- Network Active Plugins
<?php
	$plugins        = wp_get_active_network_plugins();
	$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

	foreach ( $plugins as $plugin_path ) {

		$plugin_base = plugin_basename( $plugin_path );

		if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
			continue;
		}

		$update = array_key_exists( $plugin_path, $updates ) ? ' (Update Available - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';

		$plugin = get_plugin_data( $plugin_path );
		echo $plugin['Name'] . ': ' . $plugin['Version'] . $update . PHP_EOL;
	}

	do_action( 'cn_sysinfo_after_wordpress_ms_plugins' );
endif;
?>

### End System Info ###

<?php
/**
 * Checks that the current environment for supported min/max PHP and WordPress versions.
 *
 * @since 8.20
 *
 * Credits:
 * Mark Jaquith
 * @link https://markjaquith.wordpress.com/2018/02/19/handling-old-wordpress-and-php-versions-in-your-plugin/
 * Easy Digital Downloads
 */
final class cnRequirements_Check {

	/**
	 * @since 8.20
	 * @var string The plugin name.
	 */
	private $name = '';

	/**
	 * @since 8.20
	 * @var string The plugin's basename.
	 */
	private $basename = '';

	/**
	 * @since 8.20
	 * @var array
	 */
	private $requirements = array(
		// PHP
		'php' => array(
			'name'    => 'PHP',
			'min'     => '5.2.4',
			'max'     => '7.2',
			'current' => false,
			'checked' => false,
			'passed'  => false,
			'tested'  => false,
		),
		// WordPress
		'wp' => array(
			'name'    => 'WordPress',
			'min'     => '3.8',
			'max'     => '10',
			'current' => false,
			'checked' => false,
			'passed'  => false,
			'tested'  => false,
		),
	);

	/**
	 * @since 8.20
	 * @var string The plugin file.
	 */
	private $file;

	/**
	 * Setup class properties.
	 *
	 * @access public
	 * @since  8.20
	 *
	 * @param array $args
	 */
	public function __construct( $args ) {

		$defaults = array(
			'name'         => __( 'Unknown Plugin Name', 'connections' ),
			'basename'     => plugin_basename( __FILE__ ),
			'file'         => __FILE__,
			'requirements' => $this->requirements,
		);

		$args = array_replace_recursive( $defaults, $args );

		$this->name         = $args['name'];
		$this->basename     = $args['basename'];
		$this->file         = $args['file'];
		$this->requirements = $args['requirements'];

		$this->check();
		$this->hooks();
	}

	private function hooks() {

		if ( ! $this->passes() ) {

			add_action( 'admin_head-plugins.php', array( $this, 'scripts' ) );
			add_filter( "plugin_action_links_{$this->basename}", array( $this, 'plugin_row_links' ) );
			add_action( "after_plugin_row_{$this->basename}", array( $this, 'plugin_row_notice' ) );
			// add_action( 'admin_notices', array( $this, 'deactivate' ) );
		}

		if ( ! $this->passed( 'php' ) ) {

			add_action( 'admin_notices', array( $this, 'php_version_notice' ) );
		}

		if ( ! $this->passed( 'wp' ) ) {

			add_action( 'admin_notices', array( $this, 'wp_version_notice' ) );
		}

		if ( ! $this->tested( 'php' ) ) {

			add_action( 'load-toplevel_page_connections_dashboard', array( $this, 'php_register_tested_notice' ) );
			add_action( 'load-connections_page_connections_settings', array( $this, 'php_register_tested_notice' ) );
		}

		if ( ! $this->tested( 'wp' ) ) {

			add_action( 'load-toplevel_page_connections_dashboard', array( $this, 'wp_register_tested_notice' ) );
			add_action( 'load-connections_page_connections_settings', array( $this, 'wp_register_tested_notice' ) );
		}
	}

	/**
	 * Plugin specific requirements checker.
	 *
	 * @access private
	 * @since  8.20
	 */
	private function check() {

		// Loop through requirements.
		foreach ( $this->requirements as $dependency => $properties ) {

			// Which dependency are we checking?
			switch ( $dependency ) {
				// PHP
				case 'php':
					$version = phpversion();
					break;
				// WP
				case 'wp':
					$version = get_bloginfo( 'version' );
					break;
				// Unknown
				default:
					$version = false;
					break;
			}

			// Merge to original array.
			if ( ! empty( $version ) ) {

				$this->requirements[ $dependency ] = array_merge(
					$this->requirements[ $dependency ],
					array(
						'current' => $version,
						'checked' => true,
						'passed'  => version_compare( $version, $properties['min'], '>=' ),
						'tested'  => version_compare( substr( $version, 0, strlen( $properties['max'] ) ), $properties['max'], '<=' ),
					)
				);

			}
		}
	}

	/**
	 * Have all requirements been met?
	 *
	 * @access public
	 * @since  8.20
	 *
	 * @return bool
	 */
	public function passes() {

		// Default to true (any false below wins).
		$passes = true;

		$dependencies = wp_list_pluck( $this->requirements, 'passed' );

		// Look for unmet dependencies, and exit if so.
		foreach ( $dependencies as $passed ) {

			if ( empty( $passed ) ) {

				$passes = false;
				continue;
			}
		}

		// Return
		return $passes;
	}

	/**
	 * @access public
	 * @since  8.20
	 *
	 * @param string $dependency
	 *
	 * @return bool
	 */
	public function passed( $dependency ) {

		return isset( $this->requirements[ $dependency ]['passed'] ) && $this->requirements[ $dependency ]['passed'];
	}

	/**
	 * @access public
	 * @since  8.20
	 *
	 * @param string $dependency
	 *
	 * @return bool
	 */
	public function tested( $dependency ) {

		return isset( $this->requirements[ $dependency ]['tested'] ) && $this->requirements[ $dependency ]['tested'];
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function deactivate() {

		if ( isset( $this->file ) ) {

			deactivate_plugins( plugin_basename( $this->file ) );
		}
	}

	/**
	 * Callback for the `admin_head-plugins.php` action.
	 *
	 * Adds the necessary custom CSS/JS on the Plugins admin page
	 * to support the display of the requirements row.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function scripts() {
		?>
		<!-- Added by Connections Business Directory -->
		<style type="text/css" >
			.plugins tr[data-plugin="<?php echo esc_attr( $this->basename ); ?>"] th,
			.plugins tr[data-plugin="<?php echo esc_attr( $this->basename ); ?>"] td,
			.plugins .cn-requirements-row th,
			.plugins .cn-requirements-row td {
				background: #fff5f5;
				box-shadow: none;
			}
			.plugins tr[data-plugin="<?php echo esc_attr( $this->basename ); ?>"] th {
				box-shadow: none;
			}
			.plugins .cn-requirements-row th span {
				margin-left: 6px;
				color: #dc3232;
			}
			.plugins tr[data-plugin="<?php echo esc_attr( $this->basename ); ?>"] th,
			.plugins .cn-requirements-row th.check-column,
			#<?php echo esc_html( dirname( $this->basename ) ); ?>-update td {
				border-left: 4px solid #dc3232 !important;
			}
			.plugins .cn-requirements-row .column-description p {
				margin: 0;
				padding: 0;
			}
			.plugins .cn-requirements-row .column-description p:not(:last-of-type) {
				margin-bottom: 8px;
			}
		</style>
		<script type='text/javascript'>
			/**
			 * Remove the box-shadow style fromm the row before the support license key status row.
			 * Do this via JS because this can not be done with CSS. :(
			 */
			jQuery(document).ready( function($) {

				/**
				 * Deal with the "live" search introduced in WP 4.6.
				 * @link http://stackoverflow.com/a/19401707/5351316
				 */
				var body = $('body');
				var observer = new MutationObserver( function( mutations ) {
					mutations.forEach( function( mutation ) {
						if ( mutation.attributeName === "class" ) {
							//var attributeValue = $( mutation.target ).prop( mutation.attributeName );
							//console.log( "Class attribute changed to:", attributeValue );
							cnReStyle();
						}
					});
				});

				observer.observe( body[0], {
					attributes: true
				});

				cnReStyle();
			});

			function cnReStyle() {
				jQuery('tr.cn-requirements-row')
					.find('th, td')
					.css({
						'box-shadow': 'inset 0 -1px 0 rgba(0,0,0,0.1)'
					});
				jQuery('.plugin-update-tr')
					.prev().find('th, td')
					.css({
						'box-shadow': 'none'
					});
			}
		</script>
		<?php
	}

	/**
	 * Callback for the `admin_notices` action.
	 *
	 * Notifies the user that the version of PHP does not meet the minimum supported version.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function php_version_notice() {

		$message = sprintf(
			/* translators: Minimum PHP version. */
			__( 'The &#8220;%1$s&#8221; plugin cannot run on PHP versions older than %2$s. Please contact your web host and ask them to upgrade.', 'connections' ),
			$this->name,
			$this->requirements['php']['min']
		);

		$this->displayError( $message );
	}

	/**
	 * Callback for the `load-toplevel_page_connections_dashboard` action.
	 *
	 * Register an admin notification.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function php_register_tested_notice() {

		add_action( 'admin_notices', array( $this, 'php_tested_notice' ) );
	}

	/**
	 * Callback for the `admin_notices` action.
	 *
	 * Notifies the user that the version of PHP has not been tested against.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function php_tested_notice() {

		$message = sprintf(
			/* translators: Maximum PHP version. */
			__( 'The &#8220;%1$s&#8221; plugin has not been tested on PHP versions newer than %2$s. This is informational only and the plugin should continue to function normally.', 'connections' ),
			$this->name,
			$this->requirements['php']['max']
		);

		$this->displayWarning( $message );
	}

	/**
	 * Callback for the `admin_notices` action.
	 *
	 * Notifies the user that the version of WordPress does not meet the minimum supported version.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function wp_version_notice() {

		$message = sprintf(
			/* translators: Minimum WordPress version. */
			__( 'The &#8220;%1$s&#8221; plugin cannot run on WordPress versions older than %2$s. Please update WordPress.', 'connections' ),
			$this->name,
			$this->requirements['wp']['min']
		);

		$this->displayError( $message );
	}

	/**
	 * Callback for the `load-toplevel_page_connections_dashboard` action.
	 *
	 * Register an admin notification.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function wp_register_tested_notice() {

		add_action( 'admin_notices', array( $this, 'wp_tested_notice' ) );
	}

	/**
	 * Callback for the `admin_notices` action.
	 *
	 * Notifies the user that the current version of WordPress has not been tested.
	 *
	 * @access private
	 * @since  8.20
	 */
	public function wp_tested_notice() {

		$message = sprintf(
			/* translators: Maximum WordPress version. */
			__( 'The &#8220;%1$s&#8221; plugin has not been tested on WordPress versions newer than %2$s. This is informational only and the plugin should continue to function normally.', 'connections' ),
			$this->name,
			$this->requirements['wp']['max']
		);

		$this->displayWarning( $message );
	}

	/**
	 * Display the admin notice warning message.
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @param string $message The message to display.
	 */
	private function displayWarning( $message ) {
		?>
		<div class="notice notice-warning">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Display the admin notice error message.
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @param string $message The message to display.
	 */
	private function displayError( $message ) {
		?>
		<div class="notice notice-error">
			<p><?php echo wp_kses_post( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Plugin specific text used to link to an external requirements page.
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @return string
	 */
	private function requirements_link() {

		return __( 'Requirements', 'connections' );
	}
	/**
	 * Plugin specific aria label text to describe the requirements link.
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @return string
	 */
	private function requirements_label() {

		return __( 'Connections Business Directory Requirements', 'connections' );
	}

	/**
	 * Plugin agnostic method to add the "Requirements" link to row actions.
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @param array $links
	 *
	 * @return array
	 */
	public function plugin_row_links( $links = array() ) {

		// Add the Requirements link.
		$links['requirements'] = '<a href="#" aria-label="' . esc_attr( $this->requirements_label() ) . '">' . esc_html( $this->requirements_link() ) . '</a>';

		// Return links with Requirements link.
		return $links;
	}

	/**
	 * Plugin specific text to quickly explain what's wrong.
	 *
	 * @access private
	 * @since  8.20
	 */
	private function unmet_requirements_text() {

		esc_html_e( 'This plugin has not been fully activated.', 'connections' );
	}

	/**
	 * @access private
	 * @since  8.20
	 */
	public function plugin_row_notice() {
		?>
		<tr class="active cn-requirements-row">
			<th class="check-column">
				<span class="dashicons dashicons-warning"></span>
			</th>
			<td class="column-primary">
				<?php $this->unmet_requirements_text(); ?>
			</td>
			<td class="column-description">
				<?php $this->unmet_requirements_description(); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Plugin agnostic method used to output all unmet requirement information
	 *
	 * @access private
	 * @since  8.20
	 */
	private function unmet_requirements_description() {

		foreach ( $this->requirements as $properties ) {

			if ( empty( $properties['passed'] ) ) {

				$this->unmet_requirement_description( $properties );
			}
		}
	}

	/**
	 * Output specific unmet requirement information
	 *
	 * @access private
	 * @since  8.20
	 *
	 * @param array $requirement
	 */
	private function unmet_requirement_description( $requirement = array() ) {
		?>
		<p>
			<?php
			printf(
				/* translators: Minimum and maximum versions. */
				esc_html__( 'Requires %1$s (%2$s), but (%3$s) is installed.', 'connections' ),
				'<strong>' . esc_html( $requirement['name'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['min'] ) . '</strong>',
				'<strong>' . esc_html( $requirement['current'] ) . '</strong>'
			)
			?>
		</p>
		<?php
	}
}

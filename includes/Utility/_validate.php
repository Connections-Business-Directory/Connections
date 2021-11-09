<?php
/**
 * Helper methods to validate user input.
 *
 * @package Connections_Directory\Utility
 */

namespace Connections_Directory\Utility;

/**
 * Class _sanitize
 *
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 * @phpcs:disable PEAR.NamingConventions.ValidClassName.StartWithCapital
 */
final class _validate {

	/**
	 * Is file a CSV file.
	 *
	 * @since 10.4.4
	 *
	 * @param string $file The full path file to check.
	 *
	 * @return bool
	 */
	public static function isCSV( $file ) {

		/**
		 * Filter CSV valid file types.
		 *
		 * @since 3.6.5
		 *
		 * @param array $mimeTypes List of valid file types.
		 */
		$mimeTypes = apply_filters(
			'Connections_Directory/Utility/Validate/isCSV/MIME_Types',
			array(
				'csv' => 'text/csv',
				'txt' => 'text/plain',
			)
		);

		$filetype = wp_check_filetype( $file, $mimeTypes );

		if ( in_array( $filetype['type'], $mimeTypes, true ) ) {

			return true;
		}

		return false;
	}

	/**
	 * Whether the supplied value is a float.
	 *
	 * @since 10.4.6
	 *
	 * @param mixed $value The value to validate.
	 *
	 * @return bool
	 */
	public static function isFloat( $value ) {

		return ! ( false === filter_var( $value, FILTER_VALIDATE_FLOAT ) );
	}
}

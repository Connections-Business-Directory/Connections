<?php
/**
 * The `CN_parseCSV` class has been moved to the `/includes/Libraries/parseCSV` subdirectory.
 */

use function Connections_Directory\Utility\_deprecated\_file as _deprecated_file;

_deprecated_file(
	basename( __FILE__ ),
	'10.4.18',
	CN_PATH . 'includes/Libraries/parseCSV/cn-parsecsv.lib.v1.1.php',
	__( 'This class has been moved to Libraries/parseCSV subdirectory.', 'connections' )
);

require_once CN_PATH . 'includes/Libraries/parseCSV/cn-parsecsv.lib.v1.1.php';
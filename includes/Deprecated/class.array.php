<?php
/**
 * Trigger the autoloader for the `Connections_Directory\Utility\_array::class` class using class_exists().
 *
 * @link https://www.schmengler-se.de/en/2016/09/php-using-class_alias-to-maintain-bc-while-move-rename-classes/
 */

class_exists( Connections_Directory\Utility\_array::class );

_deprecated_file( basename( __FILE__ ), '9.11', 'includes/Utility/_array.php' );

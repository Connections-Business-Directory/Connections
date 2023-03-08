<?php
/**
 * Trigger the autoloader for the `cnEntry_HTML` class using class_exists().
 *
 * @link https://www.schmengler-se.de/en/2016/09/php-using-class_alias-to-maintain-bc-while-move-rename-classes/
 */

class_exists( \Connections_Directory\Utility\_collection::class );

_deprecated_file( basename( __FILE__ ), '10.4.24', 'includes/class.collection.php' );

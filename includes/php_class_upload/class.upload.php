<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// The only purpose of this file is to include class.upload.php so the CSV Import Extension does not break because the file path changed.

require_once CN_PATH . 'vendor/php_class_upload/class.upload.php';

<?php

/**
 * Function to log data to the console
 *
 * @param $key
 * @param $data
 * @param bool $to_error_log
 *
 * @return void
 */
function console_log( $key, $data = null, bool $to_error_log = false ): void {
    $output = json_encode( ! empty( $data ) ? [ $key, $data ] : $key );
    //if ( ! $to_error_log && headers_sent()) {
    echo '<script>';
    echo 'console.log(' . $output . ')';
    echo '</script>';
    //}else{
    //    error_log(stripslashes($output));
    //}
}

// Load all classes in the 'classes' folder using glob
foreach ( glob( get_stylesheet_directory() . "/classes/class.*.php" ) as $filename ) {
    require_once $filename;
}

<?php
spl_autoload_register( function ( $class_name ) {

    // If the class being requested does not start with our prefix
    // we know it's not one in our project.
    if ( 0 !== strpos( $class_name, 'NL_Checkout_Autocomplete' ) ) {
        return;
    }

    $file_name = strtolower(
        preg_replace(
            array( '/^NL_Checkout_Autocomplete\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
            array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
            $class_name
        )
    );

    // Compile our path from the corresponding location.
    $file = NL_CA_PLUGIN_PATH . 'includes' . DIRECTORY_SEPARATOR . $file_name . '.php';

    // If a file is found.
    if ( file_exists( $file ) ) {
        // Then load it up!
        require_once $file;
    }

} );

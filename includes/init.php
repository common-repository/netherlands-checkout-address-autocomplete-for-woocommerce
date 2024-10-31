<?php


namespace NL_Checkout_Autocomplete;

/**
 * Class Init
 *
 * @package EasyElements
 */
class Init {

    /**
     * Load plugin necessary class.
     */
    public static function easy_elements_setup() {
        add_filter('woocommerce_integrations', array(self::class, 'integration') );
        new Admin\Setting();
        new Front_End\Checkout_Autocomplete();
        new Assets();
    }

    public static function integration( $integrations ) {
        $integrations[] = 'NL_Checkout_Autocomplete\Admin\Admin_Settings';
        return $integrations;
    }
}

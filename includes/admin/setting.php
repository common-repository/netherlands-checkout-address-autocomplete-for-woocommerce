<?php


namespace NL_Checkout_Autocomplete\Admin;


class Setting {

    public function __construct() {
        add_filter( 'plugin_action_links_netherlands-address-autocomplete/netherlands-address-autocomplete.php', array( $this, 'plugin_setting_link' ) );
    }


    public function plugin_setting_link( $link ) {
        $new_link = sprintf("<a href='%s'>%s</a>","admin.php?page=wc-settings&tab=integration&section=netherlands-checkout-address-autocomplete-for-woocommerce",esc_html__("Setting","netherlands-checkout-address-autocomplete-for-woocommerce") );
        $link[]   = $new_link;
        return $link;
    }
}
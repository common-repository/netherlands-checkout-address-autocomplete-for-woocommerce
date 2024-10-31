<?php


namespace NL_Checkout_Autocomplete;


/**
 * Class Assets
 *
 * @package NL_Checkout_Autocomplete
 */
class Assets {

    /**
     * Assets constructor.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'front_end_enqueue' ) );
    }

    /**
     * Front css js enqueue
     */
    public function front_end_enqueue() {
        $settings = get_option('woocommerce_netherlands-checkout-address-autocomplete-for-woocommerce_settings');
        $enable_autocomplete = isset( $settings['enable_autocomplete'] ) ? $settings['enable_autocomplete'] : 'yes';
        $choose_autocomplete_type = isset( $settings['choose_autocomplete_type'] ) ? $settings['choose_autocomplete_type'] : 'address';

        if ( is_checkout() && $enable_autocomplete == "yes" ) {
            wp_enqueue_style( 'nlcac-frontend', NL_CA_PLUGIN_URL . 'assets/css/front-end.css', null, NL_CA_VERSION );


            wp_enqueue_script( 'nlcac-frontend', NL_CA_PLUGIN_URL . 'assets/js/front-end.js', array( 'jquery' ), NL_CA_VERSION, false );

            $home_url = home_url(); // Get the home URL from WordPress
            $parsed_url = parse_url($home_url); // Parse the URL
            $choose_autocomplete_type = isset( $settings['choose_autocomplete_type'] ) ? $settings['choose_autocomplete_type'] : 'address';


            $address_fields = isset( $settings['address_fields'] ) ? $settings['address_fields'] : 'show_after_filling';

            if ( is_user_logged_in()  ) {
                // Load the customer data
                $customer = new \WC_Customer( get_current_user_id() );

                // Get billing information
                $billing_first_name = $customer->get_billing_first_name();
                $billing_last_name = $customer->get_billing_last_name();
                $billing_address_1 = $customer->get_billing_address_1();
                $billing_city = $customer->get_billing_city();
                $billing_postcode = $customer->get_billing_postcode();
                $billing_country = $customer->get_billing_country();

                if ( ! empty( $billing_first_name ) && ! empty( $billing_last_name ) && ! empty( $billing_address_1 ) && ! empty( $billing_city ) && ! empty( $billing_postcode ) && ! empty( $billing_country ) ) {
                    $address_fields = "always_show";
                }
            }

            wp_localize_script( 'nlcac-frontend', 'nlcac_data', array(
                'api' => NL_CA_API_URL,
                'address_fields' => $address_fields,
                'editable_fields' => isset( $settings['editable_fields'] ) ? $settings['editable_fields'] : 'no',
                'website' => $parsed_url['host'],
                'choose_autocomplete_type' => $choose_autocomplete_type,
            ) );
        }

    }

}
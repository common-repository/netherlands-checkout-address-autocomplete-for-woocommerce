<?php

namespace NL_Checkout_Autocomplete\Admin;

use WC_Integration;

class Admin_Settings extends WC_Integration {

    public function __construct() {
        $this->id                 = 'netherlands-checkout-address-autocomplete-for-woocommerce';
        $this->method_title       = esc_html__('NL Checkout Autocomplete', 'netherlands-checkout-address-autocomplete-for-woocommerce');
        $this->method_description = esc_html__('Settings for NL Checkout Autocomplete.', 'netherlands-checkout-address-autocomplete-for-woocommerce');

        // Initialize the form fields
        $this->init_form_fields();

        // Initialize the settings
        $this->init_settings();

        // Define user set variables.
        $this->enable_autocomplete = $this->get_option('enable_autocomplete');
        $this->api_key            = $this->get_option('api_key');
        $this->address_fields      = $this->get_option('address_fields');
        $this->editable_fields     = $this->get_option('editable_fields');
        $this->choose_autocomplete_type = $this->get_option('choose_autocomplete_type');

        // Actions
        add_action('woocommerce_update_options_integration_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Initialize integration settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enable_autocomplete' => array(
                'title'       => esc_html__('Enable Autocomplete', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'type'        => 'checkbox',
                'label'       => esc_html__('Enable Address Autocomplete', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'default'     => 'yes',
            ),
            'api_key' => array(
                'title'       => esc_html__('API Key', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'type'        => 'text',
                'description' => esc_html__('Enter your API key.', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'desc_tip'    => true,
                'default'     => '',
            ),
            'choose_autocomplete_type' => array(
                'title'       => esc_html__('Choose Autocomplete Type', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'type'        => 'select',
                'options'     => array(
                    'address' => esc_html__('Address Autocomplete', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                    'postcode' => esc_html__('Address finder by Postcode', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                ),
                'description' => esc_html__('Select whether to use postcode or address autocomplete.', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'default'     => 'postcode',
            ),
            'address_fields' => array(
                'title'       => esc_html__('Address Fields', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'type'        => 'select',
                'options'     => array(
                    'show_after_filling' => esc_html__('Show after filling', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                    'always_show'        => esc_html__('Always show', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                    'hide'               => esc_html__('Hide all the time', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                ),
                'description' => esc_html__('Select behavior of address fields.', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'default'     => 'show_after_filling',
            ),
            'editable_fields' => array(
                'title'       => esc_html__('Editable Fields', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'type'        => 'select',
                'options'     => array(
                    'no'  => esc_html__('No', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                    'yes' => esc_html__('Yes', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                ),
                'description' => esc_html__('Select whether address fields can be edited.', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'default'     => 'no',
            ),
        );
    }

    /**
     * Validate and save settings.
     */
    public function process_admin_options() {
        $api_key = isset($_POST['woocommerce_netherlands-checkout-address-autocomplete-for-woocommerce_api_key']) ? sanitize_text_field($_POST['woocommerce_netherlands-checkout-address-autocomplete-for-woocommerce_api_key']) : ''; // Updated to 'api_key'

        if ($this->verify_api_key($api_key)) {
            parent::process_admin_options();
        } else {
            $this->add_error(__('API key is invalid. Please enter a valid API key.', 'netherlands-checkout-address-autocomplete-for-woocommerce'));
        }
    }

    /**
     * Verify API key via API.
     */
    private function verify_api_key($api_key) {
        $api_url = NL_CA_API_URL . 'api-key/v1/verify';
        $home_url = home_url();
        $parsed_url = parse_url($home_url);

        $response = wp_remote_post($api_url, array(
            'body' => json_encode(array(
                'api_key' => $api_key,
                'website_url' =>  $parsed_url['host'],
            )),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 20,
        ));

        if (is_wp_error($response)) {
            return false; // Request failed, consider verification unsuccessful
        }

        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if (isset($result['success']) && $result['success'] === false) {
            \WC_Admin_Settings::add_error($result['message']);
            return false; // Verification failed
        }

        return true; // Success
    }
}

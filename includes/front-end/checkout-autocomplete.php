<?php


namespace NL_Checkout_Autocomplete\Front_End;

class Checkout_Autocomplete {

    public function __construct() {
        add_filter('woocommerce_checkout_fields',  array( $this, 'checkout_fields' ) );
        add_action('wp_footer', array( $this, 'add_dynamic_list_template' ) );

    }

    public function checkout_fields($fields) {
        if ( ! is_checkout() ) {
            return $fields;
        }

        $settings =  get_option('woocommerce_netherlands-checkout-address-autocomplete-for-woocommerce_settings');
        $enable_autocomplete = isset( $settings['enable_autocomplete'] ) ? $settings['enable_autocomplete'] : 'yes';

        if ( $enable_autocomplete != "yes" ) {
            return $fields;
        }

        $choose_autocomplete_type = isset( $settings['choose_autocomplete_type'] ) ? $settings['choose_autocomplete_type'] : 'address';

        if ( $choose_autocomplete_type == "address" ) {
            $fields['billing']['nl_billing_find_address'] = array('label' => esc_html__('Address Finder', 'netherlands-checkout-address-autocomplete-for-woocommerce'), 'placeholder' => esc_html__('Type an address in here', 'placeholder', 'netherlands-checkout-address-autocomplete-for-woocommerce'), 'required' => false, 'class' => array('form-row-wide', 'nl-field', 'nl-billing' ), 'clear' => true, 'priority' => 40, 'label_class' => array('fndadress'));
            $fields['shipping']['nl_shipping_find_address'] = array('label' => esc_html__('Address Finder', 'netherlands-checkout-address-autocomplete-for-woocommerce'), 'placeholder' => esc_html__('Type an address in here', 'placeholder', 'netherlands-checkout-address-autocomplete-for-woocommerce'), 'required' => false, 'class' => array('form-row-wide', 'nl-field', 'nl-shipping' ), 'clear' => true, 'priority' => 40, 'label_class' => array('fndadresssh'));
        } else {
            // Add custom fields for postcode finder, house number, and toevension
            $fields['billing']['nl_billing_find_address_postcode'] = array(
                'label' => esc_html__('Postcode', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'placeholder' => esc_attr__('7705 PA', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-first', 'nl-col-50', 'nl-field', 'nl-required', 'nl-billing'),
                'clear' => true,
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['billing']['nl_billing_find_house_no'] = array(
                'label' => esc_html__('Huisnummer', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'placeholder' => esc_attr__('3', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-last', 'nl-col-25', 'nl-field', 'nl-required', 'nl-billing'),
                'clear' => true,
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['billing']['nl_billing_find_toev'] = array(
                'label' => esc_html__('toev', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-last', 'nl-col-25', 'nl-clearfix', 'nl-field', 'nl-billing'),
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['billing']['nl_billing_enable_disable'] = array(
                'type' => 'checkbox',
                'label' => esc_html__('Enter Address Manually', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-wide', 'nl-field'),
                'priority' => 45,
                'label_class' => array('')
            );

            $fields['shipping']['nl_shipping_find_address_postcode'] = array(
                'label' => esc_html__('Postcode', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'placeholder' => esc_attr__('7705 PA', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-first', 'nl-col-50', 'nl-field', 'nl-required', 'nl-shipping'),
                'clear' => true,
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['shipping']['nl_shipping_find_house_no'] = array(
                'label' => esc_html__('Huisnummer', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'placeholder' => esc_attr__('3', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-last', 'nl-col-25', 'nl-field', 'nl-required', 'nl-shipping'),
                'clear' => true,
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['shipping']['nl_shipping_find_toev'] = array(
                'label' => esc_html__('toev', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-last', 'nl-col-25', 'nl-clearfix', 'nl-field', 'nl-shipping'),
                'priority' => 40,
                'label_class' => array('')
            );

            $fields['shipping']['nl_shipping_enable_disable'] = array(
                'type' => 'checkbox',
                'label' => esc_html__('Enter Address Manually', 'netherlands-checkout-address-autocomplete-for-woocommerce'),
                'required' => false,
                'class' => array('form-row-wide', 'nl-field'),
                'priority' => 45,
                'label_class' => array('')
            );
        }

        return $fields;
    }

    public function add_dynamic_list_template() {
        ?>
        <!-- HTML Template for list items -->
        <script type="text/template" id="dynamic-address-list-template">
            <li class="nl-address-single" data-street="{{street}}" data-house_number="{{house_number}}" data-postcode="{{postcode}}" data-city="{{city}}" data-toevoeging="{{toevoeging}}">{{address}}</li>
        </script>
        <script type="text/template" id="nl-logo-template">
            <a href="https://addressapi.nl/" class="nl-logo" title="<?php echo esc_attr__( 'Logo', 'netherlands-checkout-address-autocomplete-for-woocommerce' ); ?>"><img
                    src="<?php echo NL_CA_ASSETS_URL; ?>images/logo.png" alt="<?php echo esc_attr__( 'Logo', 'netherlands-checkout-address-autocomplete-for-woocommerce' ); ?>"></a>
        </script>
        <?php
    }
}

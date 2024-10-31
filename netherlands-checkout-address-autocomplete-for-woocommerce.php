<?php
/**
 * Plugin Name:       Netherlands Checkout Address Autocomplete for Woocommerce
 * Plugin URI:        https://addressapi.nl/
 * Description:       Enhances the checkout experience for WooCommerce stores operating in the Netherlands by integrating address autocomplete functionality.
 * Version:           1.0.0
 * Author:            Monstackdev
 * Author URI:        https://monstackdev.com/
 * License:           GPL v2 or later
 * Text Domain:       netherlands-checkout-address-autocomplete-for-woocommerce
 * Domain Path:       /languages/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'NL_Checkout_Autocomplete' ) ) {

    /**
     * The main plugin class
     */
    final class NL_Checkout_Autocomplete {

        /**
         * NL_Checkout_Autocomplete constructor.
         */
        private function __construct() {
            $this->define_constants();

            add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
            add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
        }

        /**
         * Initializes a single instance
         */
        public static function init() {
            static $instance = false;

            if ( ! $instance ) {
                $instance = new self();
            }

            return $instance;
        }

        /**
         * Load plugin text domain
         */
        public function load_text_domain() {
            load_plugin_textdomain( 'netherlands-checkout-address-autocomplete-for-woocommerce', false, plugin_dir_path( __FILE__ ) . 'languages/' );
        }

        /**
         * Define plugin path and url constants
         */
        public function define_constants() {
            define( 'NL_CA_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
            define( 'NL_CA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
            define( 'NL_CA_API_URL', "https://addressapi.nl/wp-json/" );
            define( 'NL_CA_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
            define( 'NL_CA_VERSION', '1.0.0' );
        }

        /**
         * Check if WooCommerce is installed
         *
         * @return bool
         */
        public function is_woo_installed() {
            $plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
            return file_exists( $plugin_path );
        }

        /**
         * Display admin notice if WooCommerce is not active
         */
        public function woocommerce_missing_notice() {
            $screen = get_current_screen();

            if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
                return;
            }

            $class = 'notice notice-error';
            $message = sprintf( esc_html__( 'The %1$sNetherlands Checkout Address Autocomplete%2$s plugin requires %1$sWooCommerce%2$s plugin installed & activated.', 'netherlands-checkout-address-autocomplete-for-woocommerce' ), '<strong>', '</strong>' );
            $plugin  = 'woocommerce/woocommerce.php';

            if ( $this->is_woo_installed() ) {
                if ( ! current_user_can( 'activate_plugins' ) ) {
                    return;
                }

                $action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
                $button_label = esc_html__( 'Activate WooCommerce', 'netherlands-checkout-address-autocomplete-for-woocommerce' );

            } else {
                if ( ! current_user_can( 'install_plugins' ) ) {
                    return;
                }

                $action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
                $button_label = esc_html__( 'Install WooCommerce', 'netherlands-checkout-address-autocomplete-for-woocommerce' );
            }

            $button = '<p><a href="' . $action_url . '" class="button-primary">' . $button_label . '</a></p><p></p>';

            printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
        }

        /**
         * Initialize the plugin
         */
        public function init_plugin() {
            if ( ! function_exists( 'WC' ) ) {
                add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
                return;
            }

            require_once NL_CA_PLUGIN_PATH . 'autoloader.php';
            \NL_Checkout_Autocomplete\Init::easy_elements_setup();
        }
    }
}

/**
 * Initialize the main plugin
 *
 * @return NL_Checkout_Autocomplete
 */
function nl_checkout_autocomplete() {
    return NL_Checkout_Autocomplete::init();
}

/**
 * Kick off the plugin
 */
nl_checkout_autocomplete();





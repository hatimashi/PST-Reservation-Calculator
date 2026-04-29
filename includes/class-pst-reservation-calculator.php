<?php

/**
 * Core plugin class — wires together all hooks.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PST_Reservation_Calculator {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version     = defined( 'PST_RESERVATION_CALCULATOR_VERSION' )
            ? PST_RESERVATION_CALCULATOR_VERSION
            : '1.0.0';
        $this->plugin_name = 'pst-reservation-calculator';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-loader.php';
        require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-i18n.php';
        require_once PST_RESERVATION_CALCULATOR_DIR . 'admin/class-pst-reservation-calculator-admin.php';
        require_once PST_RESERVATION_CALCULATOR_DIR . 'public/class-pst-reservation-calculator-public.php';

        $this->loader = new PST_Reservation_Calculator_Loader();
    }

    private function set_locale() {
        $i18n = new PST_Reservation_Calculator_i18n();
        $this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
    }

    private function define_admin_hooks() {
        $admin = new PST_Reservation_Calculator_Admin( $this->plugin_name, $this->version );

        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu',            $admin, 'register_menu' );
        $this->loader->add_action( 'wp_ajax_pst_rc_admin',  $admin, 'handle_ajax' );
    }

    private function define_public_hooks() {
        $public = new PST_Reservation_Calculator_Public( $this->plugin_name, $this->version );

        $this->loader->add_action( 'wp_enqueue_scripts',              $public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts',              $public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_ajax_nopriv_pst_rc_calculate',          $public, 'ajax_calculate' );
        $this->loader->add_action( 'wp_ajax_pst_rc_calculate',                $public, 'ajax_calculate' );
        $this->loader->add_action( 'wp_ajax_nopriv_pst_rc_email',             $public, 'ajax_email' );
        $this->loader->add_action( 'wp_ajax_pst_rc_email',                    $public, 'ajax_email' );
        $this->loader->add_action( 'wp_ajax_nopriv_pst_rc_validate_discount', $public, 'ajax_validate_discount' );
        $this->loader->add_action( 'wp_ajax_pst_rc_validate_discount',        $public, 'ajax_validate_discount' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() { return $this->plugin_name; }
    public function get_version()     { return $this->version; }
    public function get_loader()      { return $this->loader; }
}

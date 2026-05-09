<?php

/**
 * Core plugin class — wires together all hooks.
 *
 * @link    https://primestep.pl/pstrc_reservation
 * @since   1.0.0
 * @package PSTRC_Reservation_Calculator
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class PSTRC_Reservation_Calculator {

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version     = defined( 'PSTRC_CALCULATOR_VERSION' )
            ? PSTRC_CALCULATOR_VERSION
            : '1.0.0';
        $this->plugin_name = 'pstrc_reservation';

        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-reservation-calculator-loader.php';
        require_once PSTRC_CALCULATOR_DIR . 'admin/class-pstrc-reservation-calculator-admin.php';
        require_once PSTRC_CALCULATOR_DIR . 'public/class-pstrc-reservation-calculator-public.php';

        $this->loader = new PSTRC_Reservation_Calculator_Loader();
    }

    private function define_admin_hooks() {
        $admin = new PSTRC_Reservation_Calculator_Admin( $this->plugin_name, $this->version );

        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu',            $admin, 'register_menu' );
        $this->loader->add_action( 'wp_ajax_pstrc_admin',  $admin, 'handle_ajax' );
    }

    private function define_public_hooks() {
        $public = new PSTRC_Reservation_Calculator_Public( $this->plugin_name, $this->version );

        $this->loader->add_action( 'wp_enqueue_scripts',              $public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts',              $public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_ajax_nopriv_pstrc_calculate',          $public, 'ajax_calculate' );
        $this->loader->add_action( 'wp_ajax_pstrc_calculate',                $public, 'ajax_calculate' );
        $this->loader->add_action( 'wp_ajax_nopriv_pstrc_email',             $public, 'ajax_email' );
        $this->loader->add_action( 'wp_ajax_pstrc_email',                    $public, 'ajax_email' );
        $this->loader->add_action( 'wp_ajax_nopriv_pstrc_validate_discount', $public, 'ajax_validate_discount' );
        $this->loader->add_action( 'wp_ajax_pstrc_validate_discount',        $public, 'ajax_validate_discount' );
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() { return $this->plugin_name; }
    public function get_version()     { return $this->version; }
    public function get_loader()      { return $this->loader; }
}

<?php

/**
 * PST Reservation Calculator — plugin bootstrap file.
 *
 * @link              https://primestep.pl/pst-reservation-calculator
 * @since             1.0.0
 * @package           PST_Reservation_Calculator
 *
 * @wordpress-plugin
 * Plugin Name:       PST Reservation Calculator
 * Plugin URI:        https://primestep.pl/pst-reservation-calculator
 * Description:       Seasonal rental price calculator for motorhomes, trailers and cars. Embeds via shortcode, calculates prices across season boundaries and sends reservation inquiries by e-mail.
 * Version:           1.0.0
 * Author:            hatimashi
 * Author URI:        https://primestep.pl
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pst-reservation-calculator
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'PST_RESERVATION_CALCULATOR_DIR' ) ) {
    define( 'PST_RESERVATION_CALCULATOR_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'PST_RESERVATION_CALCULATOR_URL' ) ) {
    define( 'PST_RESERVATION_CALCULATOR_URL', plugin_dir_url( __FILE__ ) );
}

define( 'PST_RESERVATION_CALCULATOR_VERSION', '1.0.0' );

function activate_pst_reservation_calculator() {
    require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-tables.php';
    $tables = new PST_Reservation_Calculator_Tables();
    require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-activator.php';
    $activator = new PST_Reservation_Calculator_Activator( $tables );
    $activator->activate();
}

function deactivate_pst_reservation_calculator() {
    require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-deactivator.php';
    $deactivator = new PST_Reservation_Calculator_Deactivator();
    $deactivator->deactivate();
}

register_activation_hook( __FILE__, 'activate_pst_reservation_calculator' );
register_deactivation_hook( __FILE__, 'deactivate_pst_reservation_calculator' );

require PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator.php';

function run_pst_reservation_calculator() {
    $plugin = new PST_Reservation_Calculator();
    $plugin->run();
}

run_pst_reservation_calculator();

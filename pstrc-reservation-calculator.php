<?php

/**
 * Primestep Vehicle Reservation Calculator — plugin bootstrap file.
 *
 * @link              https://primestep.pl/pstrc_reservation
 * @since             1.0.0
 * @package           PSTRC_Reservation_Calculator
 *
 * @wordpress-plugin
 * Plugin Name:       Primestep Vehicle Reservation Calculator
 * Plugin URI:        https://primestep.pl/pstrc_reservation
 * Description:       Seasonal rental price calculator for motorhomes, trailers and cars. Embeds via shortcode, calculates prices across season boundaries and sends reservation inquiries by e-mail.
 * Version:           1.2.2
 * Author:            hatimashi
 * Author URI:        https://primestep.pl
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pstrc-reservation-calculator
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'PSTRC_CALCULATOR_DIR' ) ) {
    define( 'PSTRC_CALCULATOR_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'PSTRC_CALCULATOR_URL' ) ) {
    define( 'PSTRC_CALCULATOR_URL', plugin_dir_url( __FILE__ ) );
}

define( 'PSTRC_CALCULATOR_VERSION', '1.0.0' );

function pstrc_reservation_calculator_activate() {
    require_once PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-reservation-calculator-tables.php';
    $tables = new PSTRC_Reservation_Calculator_Tables();
    require_once PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-reservation-calculator-activator.php';
    $activator = new PSTRC_Reservation_Calculator_Activator( $tables );
    $activator->activate();
}

function pstrc_reservation_calculator_deactivate() {
    require_once PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-reservation-calculator-deactivator.php';
    $deactivator = new PSTRC_Reservation_Calculator_Deactivator();
    $deactivator->deactivate();
}

register_activation_hook( __FILE__, 'pstrc_reservation_calculator_activate' );
register_deactivation_hook( __FILE__, 'pstrc_reservation_calculator_deactivate' );

require PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-reservation-calculator.php';

function pstrc_reservation_calculator_run() {
    $plugin = new PSTRC_Reservation_Calculator();
    $plugin->run();
}

require_once PSTRC_CALCULATOR_DIR . 'includes/class-pstrc-updater.php';

new PSTRC_Updater(
    __FILE__,
    'https://soyokaze.pl/updates/update-info.php',
    PSTRC_CALCULATOR_VERSION
);

pstrc_reservation_calculator_run();

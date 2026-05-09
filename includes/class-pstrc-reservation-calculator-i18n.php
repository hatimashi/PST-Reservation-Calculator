<?php

/**
 * Loads the plugin text domain for translations.
 *
 * @link    https://primestep.pl/pstrc_reservation
 * @since   1.0.0
 * @package PSTRC_Reservation_Calculator
 */

class PSTRC_Reservation_Calculator_i18n {

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'pstrc_reservation',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}

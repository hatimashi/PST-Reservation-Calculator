<?php

/**
 * Loads the plugin text domain for translations.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

class PST_Reservation_Calculator_i18n {

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'pst-reservation-calculator',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}

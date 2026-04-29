<?php

/**
 * Fired during plugin deactivation — data is intentionally preserved.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

class PST_Reservation_Calculator_Deactivator {

    public function deactivate() {
        // Data is preserved on deactivation.
        // Full cleanup happens only in uninstall.php.
    }
}

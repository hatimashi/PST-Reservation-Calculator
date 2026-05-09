<?php

/**
 * Fired during plugin deactivation — data is intentionally preserved.
 *
 * @link    https://primestep.pl/pstrc_reservation
 * @since   1.0.0
 * @package PSTRC_Reservation_Calculator
 */

class PSTRC_Reservation_Calculator_Deactivator {

    public function deactivate() {
        // Data is preserved on deactivation.
        // Full cleanup happens only in uninstall.php.
    }
}

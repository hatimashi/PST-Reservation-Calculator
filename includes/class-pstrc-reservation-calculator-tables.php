<?php

/**
 * @link    https://primestep.pl/pstrc_reservation
 * @since   1.0.0
 * @package PSTRC_Reservation_Calculator
 */

class PSTRC_Reservation_Calculator_Tables {

    public function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'pstrc_reservation';
    }

    public static function get_vehicle_types() {
        $default = array(
            'kamper'    => 'Kamper',
            'przyczepa' => 'Przyczepa',
            'samochod'  => 'Samochód',
        );
        $saved = get_option( 'pstrc_reservation_calculator_vehicle_types', null );
        return ( is_array( $saved ) && ! empty( $saved ) ) ? $saved : $default;
    }

    /** @deprecated Use get_table_name() */
    public function kamperowanicalculatortable() {
        return $this->get_table_name();
    }
}

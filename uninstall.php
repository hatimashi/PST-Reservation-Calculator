<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 *
 * @package PST_Reservation_Calculator
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'pst_reservation';
$safe_table = esc_sql( $table );
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    "DROP TABLE IF EXISTS `{$safe_table}`" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SchemaChange
);

delete_option('pst_reservation_calculator_email');
delete_option('pst_reservation_calculator_vat');
delete_option('pst_reservation_calculator_fees');
delete_option('pst_reservation_calculator_vehicle_types');
delete_option('pst_reservation_calculator_discount_codes');

<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link    https://primestep.pl/pstrc_reservation
 * @since   1.0.0
 *
 * @package PSTRC_Reservation_Calculator
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$pstrc_table = $wpdb->prefix . 'pstrc_reservation';
$pstrc_safe_table = esc_sql( $table );
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    "DROP TABLE IF EXISTS `{$pstrc_safe_table}`" // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SchemaChange
);

delete_option('pstrc_reservation_calculator_email');
delete_option('pstrc_reservation_calculator_vat');
delete_option('pstrc_reservation_calculator_fees');
delete_option('pstrc_reservation_calculator_vehicle_types');
delete_option('pstrc_reservation_calculator_discount_codes');

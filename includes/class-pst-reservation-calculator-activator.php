<?php

/**
 * Fired during plugin activation — creates the pricing table with sample data.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

class PST_Reservation_Calculator_Activator
{

    private $tables;

    public function __construct($tables_object)
    {
        $this->tables = $tables_object;
    }

    public function activate()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = $this->tables->get_table_name();

        $cache_key = 'pst_table_exists_' . $table;
        $table_exists = wp_cache_get($cache_key);

        if (false === $table_exists) {
            $table_exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $wpdb->prepare('SHOW TABLES LIKE %s', $table)
            );
            wp_cache_set($cache_key, $table_exists);
        }

        if ($table_exists === $table) {
            return;
        }

        $charset = $wpdb->get_charset_collate();
        $sql     = "CREATE TABLE `{$table}` (
            `id`          int(11)        NOT NULL AUTO_INCREMENT,
            `price`       decimal(10,2)  NOT NULL DEFAULT '0.00',
            `type`        tinytext       NOT NULL,
            `description` tinytext       NOT NULL,
            `season`      tinytext       NOT NULL,
            `date_start`  date           NOT NULL DEFAULT '0000-00-00',
            `date_end`    date           NOT NULL DEFAULT '0000-00-00',
            `last_change` timestamp      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 {$charset};"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        dbDelta($sql);

        $this->insert_sample_data($wpdb, $table);
        $this->init_options();
    }

    private function init_options()
    {
        if (false === get_option('pstrc_reservation_calculator_vehicle_types')) {
            add_option('pstrc_reservation_calculator_vehicle_types', array(
                'kamper'    => 'Kamper',
                'przyczepa' => 'Przyczepa',
                'samochod'  => 'Samochód',
            ));
        }
        if (false === get_option('pstrc_reservation_calculator_fees')) {
            add_option('pstrc_reservation_calculator_fees', array(
                'kamper'    => array('service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 5000, 'delivery_netto' => 2, 'delivery_brutto' => 2.46),
                'przyczepa' => array('service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 2000, 'delivery_netto' => 2, 'delivery_brutto' => 2.46),
                'samochod'  => array('service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 1500, 'delivery_netto' => 2, 'delivery_brutto' => 2.46),
            ));
        }
        if (false === get_option('pstrc_reservation_calculator_discount_codes')) {
            add_option('pstrc_reservation_calculator_discount_codes', array());
        }
    }

    private function insert_sample_data($wpdb, $table)
    {
        $rows = array(
            // Przyczepa
            array('price' => 180, 'type' => 'przyczepa', 'description' => 'niski_1',   'season' => 'niski',   'date_start' => '2024-10-01', 'date_end' => '2025-04-26'),
            array('price' => 225, 'type' => 'przyczepa', 'description' => 'sredni_1',  'season' => 'sredni',  'date_start' => '2024-09-01', 'date_end' => '2024-09-30'),
            array('price' => 225, 'type' => 'przyczepa', 'description' => 'sredni_2',  'season' => 'sredni',  'date_start' => '2025-05-06', 'date_end' => '2025-06-19'),
            array('price' => 250, 'type' => 'przyczepa', 'description' => 'wysoki_1',  'season' => 'wysoki',  'date_start' => '2025-06-20', 'date_end' => '2025-08-31'),
            array('price' => 250, 'type' => 'przyczepa', 'description' => 'wysoki_2',  'season' => 'wysoki',  'date_start' => '2025-04-27', 'date_end' => '2025-05-05'),
            // Kamper
            array('price' => 350, 'type' => 'kamper',    'description' => 'niski_1',   'season' => 'niski',   'date_start' => '2024-10-01', 'date_end' => '2025-04-26'),
            array('price' => 450, 'type' => 'kamper',    'description' => 'sredni_1',  'season' => 'sredni',  'date_start' => '2024-09-01', 'date_end' => '2024-09-30'),
            array('price' => 450, 'type' => 'kamper',    'description' => 'sredni_2',  'season' => 'sredni',  'date_start' => '2025-05-06', 'date_end' => '2025-06-19'),
            array('price' => 550, 'type' => 'kamper',    'description' => 'wysoki_1',  'season' => 'wysoki',  'date_start' => '2025-06-20', 'date_end' => '2025-08-31'),
            array('price' => 550, 'type' => 'kamper',    'description' => 'wysoki_2',  'season' => 'wysoki',  'date_start' => '2025-04-27', 'date_end' => '2025-05-05'),
            // Samochód
            array('price' => 200, 'type' => 'samochod',  'description' => 'niski_1',   'season' => 'niski',   'date_start' => '2024-10-01', 'date_end' => '2025-04-26'),
            array('price' => 200, 'type' => 'samochod',  'description' => 'sredni_1',  'season' => 'sredni',  'date_start' => '2024-09-01', 'date_end' => '2024-09-30'),
            array('price' => 200, 'type' => 'samochod',  'description' => 'sredni_2',  'season' => 'sredni',  'date_start' => '2025-05-06', 'date_end' => '2025-06-19'),
            array('price' => 200, 'type' => 'samochod',  'description' => 'wysoki_1',  'season' => 'wysoki',  'date_start' => '2025-06-20', 'date_end' => '2025-08-31'),
            array('price' => 200, 'type' => 'samochod',  'description' => 'wysoki_2',  'season' => 'wysoki',  'date_start' => '2025-04-27', 'date_end' => '2025-05-05'),
        );

        foreach ($rows as $row) {
            $wpdb->insert(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                $table, $row, array('%f', '%s', '%s', '%s', '%s', '%s', '%s')
                );
        }
    }
}

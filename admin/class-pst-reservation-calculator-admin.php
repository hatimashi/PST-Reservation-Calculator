<?php

/**
 * Admin-specific functionality of the plugin.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

class PST_Reservation_Calculator_Admin
{

    private $plugin_name;
    private $version;
    private $tables;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        require_once PSTRC_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-tables.php';
        $this->tables = new PST_Reservation_Calculator_Tables();
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('pstrc-admin',      plugin_dir_url(__FILE__) . 'css/pst-reservation-calculator-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('pstrc-datatables', plugin_dir_url(__FILE__) . 'css/jquery.dataTables.min.css',            array(), $this->version, 'all');
        wp_enqueue_style('pstrc-bootstrap',  plugin_dir_url(__FILE__) . 'css/bootstrap.min.css',                    array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('pstrc-datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('pstrc-notify',     plugin_dir_url(__FILE__) . 'js/jquery.notifyBar.js',      array('jquery'), $this->version, false);
        wp_enqueue_script('pstrc-validate',   plugin_dir_url(__FILE__) . 'js/validate.min.js',          array('jquery'), $this->version, true);
        wp_enqueue_script('pstrc-bootstrap',  plugin_dir_url(__FILE__) . 'js/bootstrap.min.js',         array('jquery'), $this->version, false);
        wp_enqueue_script('pstrc-admin-js',   plugin_dir_url(__FILE__) . 'js/pst-reservation-calculator-admin.js', array('jquery'), $this->version, true);
        wp_localize_script('pstrc-admin-js', 'pstrc_admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('pstrc_admin_nonce'),
        ));
    }

    public function register_menu()
    {
        add_menu_page(
            __('PST Reservation Calculator', 'pst-reservation-calculator'),
            __('PST Reservation', 'pst-reservation-calculator'),
            'manage_options',
            'pstrc-settings',
            array($this, 'render_settings_page'),
            'dashicons-products',
            55
        );
    }

    public function render_settings_page()
    {
        include_once PSTRC_CALCULATOR_DIR . 'admin/partials/pst-reservation-calculator-admin-display.php';
    }

    public function handle_ajax()
    {
        if (! wp_verify_nonce(
            isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '',
            'pstrc_admin_nonce'
        )) {
            wp_die('', '', 403);
        }

        if (! current_user_can('manage_options')) {
            wp_die('', '', 403);
        }

        $param = isset($_REQUEST['param']) ? sanitize_text_field(wp_unslash($_REQUEST['param'])) : '';
        global $wpdb;
        $table = $this->tables->get_table_name();

        switch ($param) {

            // ---- SEZONY ----

            case 'save_seasons':
                $raw  = isset($_REQUEST['seasons']) ? $_REQUEST['seasons'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
                $type = isset($_REQUEST['type']) ? sanitize_text_field(wp_unslash($_REQUEST['type'])) : '';

                if (! is_array($raw)) {
                    wp_send_json_error('Invalid data', 400);
                }

                $vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();
                if (! array_key_exists($type, $vehicle_types)) {
                    wp_send_json_error('Invalid type', 400);
                }

                foreach ($raw as $key => $data) {
                    if (! is_array($data)) {
                        continue;
                    }

                    $season     = isset($data['season'])     ? sanitize_text_field(wp_unslash($data['season']))     : '';
                    $price      = isset($data['price'])      ? floatval($data['price'])                                : 0;
                    $date_start = isset($data['date_start']) ? sanitize_text_field(wp_unslash($data['date_start'])) : '';
                    $date_end   = isset($data['date_end'])   ? sanitize_text_field(wp_unslash($data['date_end']))   : '';

                    if (
                        empty($season) || $price <= 0
                        || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_start)
                        || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_end)
                    ) {
                        continue;
                    }

                    // Numeric key → existing row (UPDATE); non-numeric → new row (INSERT)
                    $id = ctype_digit((string) $key) ? (int) $key : 0;

                    if ($id > 0) {
                        $wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                            $table,
                            array('season' => $season, 'price' => $price, 'date_start' => $date_start, 'date_end' => $date_end),
                            array('id' => $id),
                            array('%s', '%f', '%s', '%s'),
                            array('%d')
                        );
                    } else {
                        $wpdb->insert(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                            $table,
                            array(
                                'price'       => $price,
                                'type'        => $type,
                                'description' => sanitize_key($season),
                                'season'      => $season,
                                'date_start'  => $date_start,
                                'date_end'    => $date_end,
                            ),
                            array('%f', '%s', '%s', '%s', '%s', '%s')
                        );
                    }
                }
                wp_send_json_success();
                break;

            case 'delete_season':
                $id = absint(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
                if ($id <= 0) {
                    wp_send_json_error('Invalid id', 400);
                }
                $wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $table,
                    array('id' => $id),
                    array('%d')
                );
                wp_send_json_success();
                break;

            // ---- POJAZDY ----

            case 'add_vehicle_type':
                $slug  = sanitize_key(isset($_REQUEST['slug'])  ? $_REQUEST['slug']  : '');
                $label = sanitize_text_field(wp_unslash(isset($_REQUEST['label']) ? $_REQUEST['label'] : ''));

                if (empty($slug) || empty($label)) {
                    wp_send_json_error('Brakuje klucza lub nazwy pojazdu.', 400);
                }

                $vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();
                if (array_key_exists($slug, $vehicle_types)) {
                    wp_send_json_error('Typ pojazdu o takim kluczu już istnieje.', 400);
                }

                $vehicle_types[$slug] = $label;
                update_option('pstrc_reservation_calculator_vehicle_types', $vehicle_types);

                // Skopiuj sezony z kampera jako wzorzec dla nowego typu
                $cache_key = 'pst_template_seasons_kamper';
                $template_seasons = wp_cache_get($cache_key);
                $safe_table = esc_sql( $table );
                if (false === $template_seasons) {
                    $template_seasons = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                        $wpdb->prepare(
                            "SELECT * FROM `{$safe_table}` WHERE `type` = %s ORDER BY `date_start` ASC",
                            'kamper'
                        ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    ); 
                    wp_cache_set($cache_key, $template_seasons);
                }
                foreach ($template_seasons as $ks) {
                    $wpdb->insert(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                        $table,
                        array(
                            'price'       => $ks->price,
                            'type'        => $slug,
                            'description' => $ks->description,
                            'season'      => $ks->season,
                            'date_start'  => $ks->date_start,
                            'date_end'    => $ks->date_end,
                        ),
                        array('%f', '%s', '%s', '%s', '%s', '%s')
                    );
                }

                wp_send_json_success(array('slug' => $slug, 'label' => $label));
                break;

            case 'delete_vehicle_type':
                $slug = sanitize_key(isset($_REQUEST['slug']) ? $_REQUEST['slug'] : '');

                if (empty($slug)) {
                    wp_send_json_error('Invalid slug', 400);
                }

                $vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();
                if (! array_key_exists($slug, $vehicle_types)) {
                    wp_send_json_error('Typ pojazdu nie istnieje.', 400);
                }

                unset($vehicle_types[$slug]);
                update_option('pstrc_reservation_calculator_vehicle_types', $vehicle_types);

                // Usuń sezony i opłaty tego typu
                $wpdb->delete(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $table, array('type' => $slug), array('%s')
                    );
                $fees = get_option('pstrc_reservation_calculator_fees', array());
                unset($fees[$slug]);
                update_option('pstrc_reservation_calculator_fees', $fees);

                wp_send_json_success();
                break;

            // ---- USTAWIENIA ----

            case 'save_settings':
                $vat = isset($_REQUEST['vat']) ? max(0, min(100, absint($_REQUEST['vat']))) : 23;
                update_option('pstrc_reservation_calculator_vat', $vat);

                $raw_fees      = isset($_REQUEST['fees']) ? $_REQUEST['fees'] : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
                $vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();

                if (is_array($raw_fees)) {
                    $clean = array();
                    foreach (array_keys($vehicle_types) as $vtype) {
                        if (isset($raw_fees[$vtype]) && is_array($raw_fees[$vtype])) {
                            $f              = $raw_fees[$vtype];
                            $clean[$vtype] = array(
                                'service_pay_netto'  => floatval($f['service_pay_netto']  ?? 0),
                                'service_pay_brutto' => floatval($f['service_pay_brutto'] ?? 0),
                                'deposit'            => floatval($f['deposit']            ?? 0),
                                'delivery_netto'     => floatval($f['delivery_netto']     ?? 2),
                                'delivery_brutto'    => floatval($f['delivery_brutto']    ?? 2.46),
                            );
                        }
                    }
                    if (! empty($clean)) {
                        update_option('pstrc_reservation_calculator_fees', $clean);
                    }
                }
                wp_send_json_success();
                break;

            // ---- KODY RABATOWE ----

            case 'add_discount_code':
                $code  = strtoupper(sanitize_text_field(wp_unslash(isset($_REQUEST['code'])  ? $_REQUEST['code']  : '')));
                $dtype = sanitize_text_field(wp_unslash(isset($_REQUEST['type'])  ? $_REQUEST['type']  : ''));
                $value = floatval(isset($_REQUEST['value']) ? $_REQUEST['value'] : 0);

                if (empty($code) || ! in_array($dtype, array('percent', 'fixed'), true) || $value <= 0) {
                    wp_send_json_error('Nieprawidłowe dane kodu rabatowego.', 400);
                }

                $codes = get_option('pstrc_reservation_calculator_discount_codes', array());
                foreach ($codes as $dc) {
                    if ($dc['code'] === $code) {
                        wp_send_json_error('Kod o tej nazwie już istnieje.', 400);
                    }
                }

                $codes[] = array(
                    'code'   => $code,
                    'type'   => $dtype,
                    'value'  => $value,
                    'active' => 1,
                );
                update_option('pstrc_reservation_calculator_discount_codes', $codes);
                wp_send_json_success();
                break;

            case 'delete_discount_code':
                $code  = strtoupper(sanitize_text_field(wp_unslash(isset($_REQUEST['code']) ? $_REQUEST['code'] : '')));
                $codes = get_option('pstrc_reservation_calculator_discount_codes', array());
                $codes = array_values(array_filter($codes, function ($dc) use ($code) {
                    return $dc['code'] !== $code;
                }));
                update_option('pstrc_reservation_calculator_discount_codes', $codes);
                wp_send_json_success();
                break;

            case 'toggle_discount_code':
                $code      = strtoupper(sanitize_text_field(wp_unslash(isset($_REQUEST['code']) ? $_REQUEST['code'] : '')));
                $codes     = get_option('pstrc_reservation_calculator_discount_codes', array());
                $new_state = 0;
                foreach ($codes as &$dc) {
                    if ($dc['code'] === $code) {
                        $dc['active'] = $dc['active'] ? 0 : 1;
                        $new_state    = $dc['active'];
                        break;
                    }
                }
                unset($dc);
                update_option('pstrc_reservation_calculator_discount_codes', $codes);
                wp_send_json_success(array('active' => $new_state));
                break;

            // ---- KONTAKT ----

            case 'save_contact':
                $email = isset($_REQUEST['kc_email']) ? sanitize_email(wp_unslash($_REQUEST['kc_email'])) : '';
                if (! empty($email)) {
                    update_option('pstrc_reservation_calculator_email', $email);
                }
                wp_send_json_success();
                break;

            default:
                wp_send_json_error('Unknown action', 400);
                break;
        }
    }
}

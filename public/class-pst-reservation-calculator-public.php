<?php

/**
 * Public-facing functionality of the plugin.
 *
 * @link    https://primestep.pl/pst-reservation-calculator
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

class PST_Reservation_Calculator_Public {

    private $plugin_name;
    private $version;
    private $tables;
    private $atts_type;

    private static $default_fees = array(
        'kamper'    => array( 'service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 5000, 'delivery_netto' => 2, 'delivery_brutto' => 2.46 ),
        'przyczepa' => array( 'service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 2000, 'delivery_netto' => 2, 'delivery_brutto' => 2.46 ),
        'samochod'  => array( 'service_pay_netto' => 300, 'service_pay_brutto' => 369, 'deposit' => 1500, 'delivery_netto' => 2, 'delivery_brutto' => 2.46 ),
    );

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        require_once PST_RESERVATION_CALCULATOR_DIR . 'includes/class-pst-reservation-calculator-tables.php';
        $this->tables = new PST_Reservation_Calculator_Tables();
    }

    public static function show_calculator( $atts, $content = '' ) {        
        ob_start();
        include_once PST_RESERVATION_CALCULATOR_DIR . 'public/partials/pst-reservation-calculator-public-display.php';
        return ob_get_clean();
    }

    public function enqueue_styles() {
        wp_enqueue_style( $this->plugin_name,  plugin_dir_url( __FILE__ ) . 'css/pst-reservation-calculator-public.css', array(), $this->version, 'all' );
        wp_enqueue_style( 'pst-rc-jquery-ui',  plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css',  array(), $this->version, 'all' );
        wp_enqueue_style( 'pst-rc-datepicker', plugin_dir_url( __FILE__ ) . 'css/datepicker.css', array(), $this->version, 'all' );
/*         wp_enqueue_style( 'pst-rc-form',       plugin_dir_url( __FILE__ ) . 'css/form.css',       array(), $this->version, 'all' );
 */        wp_enqueue_style( 'pst-tailwind',      plugin_dir_url( __FILE__ ) . 'css/tailwind.min.css',array(),$this->version, 'all' );
        }

    public function enqueue_scripts() {
        wp_enqueue_script('lucide',               'https://unpkg.com/lucide@latest', array(), null,      false);

        wp_enqueue_script( 'pst-rc-public',       plugin_dir_url( __FILE__ ) . 'js/pst-reservation-calculator-public.js', array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'pst-rc-validate',     plugin_dir_url( __FILE__ ) . 'js/validate.min.js',      array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'pst-rc-datepicker',   plugin_dir_url( __FILE__ ) . 'js/datepicker.js',        array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'pst-rc-datepicker-pl', plugin_dir_url( __FILE__ ) . 'js/datepicker.pl-PL.js', array( 'jquery' ), $this->version, true );
        wp_localize_script( 'pst-rc-public', 'pst_rc_ajax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'pst_rc_public_nonce' ),
            'vat'     => (int) get_option( 'pst_reservation_calculator_vat', 23 ),
            'thetitleattribute' => esc_attr(get_the_title(get_the_ID())),
            ) );
    }

    public function ajax_calculate() {
        if ( ! wp_verify_nonce(
            isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '',
            'pst_rc_public_nonce'
        ) ) {
            wp_send_json_error( 'Invalid nonce', 403 );
        }

        if ( ! isset( $_REQUEST['start_date'] ) || ! isset( $_REQUEST['end_date'] ) ) {
            wp_send_json_error( 'Missing dates', 400 );
        }

        global $wpdb;

        $type      = sanitize_text_field( wp_unslash( isset( $_REQUEST['type'] ) ? $_REQUEST['type'] : '' ) );
        $start_raw = sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) );
        $end_raw   = sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) );

        $vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();
        if ( ! array_key_exists( $type, $vehicle_types ) ) {
            wp_send_json_error( 'Invalid type', 400 );
        }

        $values = array( 'sum' => 0, 'days' => 0 );
        $this->add_fees( $values, $type );

        if ( empty( $start_raw ) || empty( $end_raw ) ) {
            wp_send_json( $values );
        }

        $start_date = date_create( $start_raw );
        $end_date   = date_create( $end_raw );

        if ( ! $start_date || ! $end_date || $start_date >= $end_date ) {
            wp_send_json( $values );
        }

        $table = $this->tables->get_table_name();
        $safe_table = esc_sql( $table );
        $query = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare( 
                "SELECT * FROM `{$safe_table}` WHERE `type` = %s ORDER BY `date_start` ASC", $type 
                ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        $total_sum   = 0.0;
        $days_priced = 0;
        $rental_year = (int) $start_date->format( 'Y' );

        foreach ( $query as $value ) {
            $start_md           = gmdate( 'm-d', strtotime( $value->date_start ) );
            $end_md             = gmdate( 'm-d', strtotime( $value->date_end ) );
            $season_start_month = (int) gmdate( 'm', strtotime( $value->date_start ) );
            $season_end_month   = (int) gmdate( 'm', strtotime( $value->date_end ) );
            $crosses_year       = $season_start_month > $season_end_month;

            for ( $y_offset = -1; $y_offset <= 1; $y_offset++ ) {
                $y            = $rental_year + $y_offset;
                $season_start = date_create( $y . '-' . $start_md );
                $season_end   = $crosses_year
                    ? date_create( ( $y + 1 ) . '-' . $end_md )
                    : date_create( $y . '-' . $end_md );

                $season_end_excl = clone $season_end;
                date_add( $season_end_excl, new DateInterval( 'P1D' ) );

                if ( $season_start >= $end_date || $season_end_excl <= $start_date ) {
                    continue;
                }

                $ov_start = ( $start_date > $season_start ) ? $start_date : $season_start;
                $ov_end   = ( $end_date < $season_end_excl ) ? $end_date : $season_end_excl;
                $days     = (int) date_diff( $ov_start, $ov_end )->days;

                if ( $days > 0 ) {
                    $total_sum   += $days * floatval( $value->price );
                    $days_priced += $days;
                    if ( empty( $values['season'] ) ) {
                        $values['season'] = $value->season;
                    }
                }
                break;
            }
        }

        if ( $days_priced > 0 ) {
            $values['sum']  = round( $total_sum, 2 );
            $values['days'] = $days_priced;
            $values['if1']  = 1;
        }

        wp_send_json( $values );
    }

    public function ajax_validate_discount() {
        if ( ! wp_verify_nonce(
            isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '',
            'pst_rc_public_nonce'
        ) ) {
            wp_send_json_error( 'Invalid nonce', 403 );
        }

        $code  = strtoupper( sanitize_text_field( wp_unslash( isset( $_REQUEST['code'] ) ? $_REQUEST['code'] : '' ) ) );
        $codes = get_option( 'pst_reservation_calculator_discount_codes', array() );

        foreach ( $codes as $dc ) {
            if ( strtoupper( $dc['code'] ) === $code && ! empty( $dc['active'] ) ) {
                wp_send_json_success( array(
                    'type'  => $dc['type'],
                    'value' => floatval( $dc['value'] ),
                ) );
                return;
            }
        }

        wp_send_json_error( 'Nieprawidłowy lub nieaktywny kod rabatowy.' );
    }

    public function ajax_email()
    {
        error_log('ajax_email odpalony');
        error_log('REQUEST: ' . print_r($_REQUEST, true));
        if (! wp_verify_nonce(
            isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '',
            'pst_rc_public_nonce'
        )) {
            wp_send_json_error('Invalid nonce', 403);
        }

        $name          = sanitize_text_field(wp_unslash(isset($_REQUEST['fullname'])     ? $_REQUEST['fullname']     : ''));
        $email         = sanitize_email(wp_unslash(isset($_REQUEST['email'])             ? $_REQUEST['email']        : ''));
        $phone         = sanitize_text_field(wp_unslash(isset($_REQUEST['phone'])        ? $_REQUEST['phone']        : ''));
        $message_body  = sanitize_textarea_field(wp_unslash(isset($_REQUEST['extramessage']) ? $_REQUEST['extramessage'] : ''));
        $type          = sanitize_text_field(wp_unslash(isset($_REQUEST['type'])         ? $_REQUEST['type']         : ''));
        $date_od       = sanitize_text_field(wp_unslash(isset($_REQUEST['start_date'])   ? $_REQUEST['start_date']   : ''));
        $date_do       = sanitize_text_field(wp_unslash(isset($_REQUEST['end_date'])     ? $_REQUEST['end_date']     : ''));
        $price_netto   = floatval(isset($_REQUEST['wyniknetto'])  ? $_REQUEST['wyniknetto']  : 0);
        $price_brutto  = floatval(isset($_REQUEST['wynikbrutto']) ? $_REQUEST['wynikbrutto'] : 0);
        $discount_code = strtoupper(sanitize_text_field(wp_unslash(isset($_REQUEST['discount_code']) ? $_REQUEST['discount_code'] : '')));

        $to_email = get_option('pst_reservation_calculator_email', get_option('admin_email'));
        $subject  = 'Rezerwacja: ' . $type . ' — ' . $name;

        $message  = '<h1>Nowa rezerwacja</h1>';
        $message .= '<h2>Typ pojazdu:</h2><p>' . esc_html($type) . '</p>';
        $message .= '<h2>Imię i nazwisko:</h2><p>' . esc_html($name) . '</p>';
        $message .= '<h2>Email:</h2><p>' . esc_html($email) . '</p>';
        $message .= '<h2>Telefon:</h2><p>' . esc_html($phone) . '</p>';
        $message .= '<h2>Okres rezerwacji:</h2><p>od ' . esc_html($date_od) . ' do ' . esc_html($date_do) . '</p>';
        $message .= '<h2>Cena z kalkulatora:</h2><p>' . esc_html($price_netto) . ' zł netto — ' . esc_html($price_brutto) . ' zł brutto</p>';

        if (! empty($discount_code)) {
            $message .= '<h2>Kod rabatowy:</h2><p>' . esc_html($discount_code) . '</p>';
        }

        $message .= '<h2>Dodatkowe informacje:</h2><p>' . nl2br(esc_html($message_body)) . '</p>';

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $result  = wp_mail($to_email, $subject, $message, $headers);
        error_log('wp_mail result: ' . ($result ? 'true' : 'false'));
        error_log('to_email: ' . $to_email);
        wp_send_json($result ? 1 : 0);
    }

    private function add_fees( array &$values, string $type ) {
        $all_fees = get_option( 'pst_reservation_calculator_fees', array() );
        $f        = isset( $all_fees[ $type ] )
            ? $all_fees[ $type ]
            : ( self::$default_fees[ $type ] ?? array() );

        $values['service_pay_netto']  = $f['service_pay_netto']  ?? 0;
        $values['service_pay_brutto'] = $f['service_pay_brutto'] ?? 0;
        $values['deposit']            = $f['deposit']            ?? 0;
        $values['delivery_netto']     = $f['delivery_netto']     ?? 0;
        $values['delivery_brutto']    = $f['delivery_brutto']    ?? 0;
    }
}

add_shortcode( 'pst_reservation', array( 'PST_Reservation_Calculator_Public', 'show_calculator' ) );

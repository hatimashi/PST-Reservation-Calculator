<?php

/**
 * Admin settings page view.
 *
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */

if (! defined('ABSPATH')) {
    exit;
}

global $wpdb;
$pst_tables    = new PST_Reservation_Calculator_Tables();
$table         = $pst_tables->get_table_name();
$vehicle_types = PST_Reservation_Calculator_Tables::get_vehicle_types();

$all_seasons = array();
foreach (array_keys($vehicle_types) as $vtype) {
    $cache_key = 'pst_all_seasons_' . $vtype;
    $all_seasons[$vtype] = wp_cache_get($cache_key);
    $safe_table = esc_sql($table);
    if (false === $all_seasons[$vtype]) {
        $all_seasons[$vtype] = $wpdb->get_results(  // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                "SELECT * FROM `{$safe_table}` WHERE `type` = %s ORDER BY `date_start` ASC",
                $vtype
            )
        );
        wp_cache_set($cache_key, $all_seasons[$vtype]);
    }
}

$email          = get_option('pst_reservation_calculator_email', '');
$vat            = (int) get_option('pst_reservation_calculator_vat', 23);
$fees           = get_option('pst_reservation_calculator_fees', array());
$discount_codes = get_option('pst_reservation_calculator_discount_codes', array());

$fee_defaults = array(
    'service_pay_netto'  => 0,
    'service_pay_brutto' => 0,
    'deposit'            => 0,
    'delivery_netto'     => 2,
    'delivery_brutto'    => 2.46,
);
?>

<div class="wrap">
    <h1><?php esc_html_e('PST Reservation Calculator — Ustawienia', 'pst-reservation-calculator'); ?></h1>

    <ul class="nav nav-tabs" id="kcTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#kc-sezony" role="tab">Sezony</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kc-pojazdy" role="tab">Pojazdy</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kc-ustawienia" role="tab">Ustawienia</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kc-kody" role="tab">Kody rabatowe</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#kc-kontakt" role="tab">Kontakt</a>
        </li>
    </ul>

    <div class="tab-content" id="kcTabContent">

        <!-- TAB: SEZONY -->
        <div class="tab-pane fade show active" id="kc-sezony" role="tabpanel">
            <div class="container kc-tab-content">
                <p class="text-muted">
                    Rok w datach jest pomijany podczas obliczeń — liczy się tylko miesiąc i dzień.
                </p>

                <?php foreach ($vehicle_types as $vtype => $vlabel) : ?>
                    <h3><?php echo esc_html($vlabel); ?></h3>
                    <form class="kc-season-form" data-type="<?php echo esc_attr($vtype); ?>">
                        <table class="wp-list-table widefat fixed striped kc-seasons-table">
                            <thead>
                                <tr>
                                    <th>Nazwa sezonu</th>
                                    <th>Cena / dzień (zł)</th>
                                    <th>Data od</th>
                                    <th>Data do</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($all_seasons[$vtype])) : ?>
                                    <tr class="kc-empty-row">
                                        <td colspan="5">Brak rekordów.</td>
                                    </tr>
                                <?php else : ?>
                                    <?php foreach ($all_seasons[$vtype] as $row) : ?>
                                        <tr data-id="<?php echo (int) $row->id; ?>">
                                            <td>
                                                <input type="text"
                                                    name="seasons[<?php echo (int) $row->id; ?>][season]"
                                                    value="<?php echo esc_attr($row->season); ?>"
                                                    class="regular-text" required>
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="0.01"
                                                    name="seasons[<?php echo (int) $row->id; ?>][price]"
                                                    value="<?php echo esc_attr($row->price); ?>"
                                                    class="small-text" required>
                                            </td>
                                            <td>
                                                <input type="date"
                                                    name="seasons[<?php echo (int) $row->id; ?>][date_start]"
                                                    value="<?php echo esc_attr($row->date_start); ?>"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="date"
                                                    name="seasons[<?php echo (int) $row->id; ?>][date_end]"
                                                    value="<?php echo esc_attr($row->date_end); ?>"
                                                    required>
                                            </td>
                                            <td>
                                                <button type="button" class="button kc-delete-season"
                                                    data-id="<?php echo (int) $row->id; ?>">Usuń</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <input type="hidden" name="type" value="<?php echo esc_attr($vtype); ?>">
                        <div style="margin: 10px 0;">
                            <button type="button" class="button kc-add-season-row">+ Dodaj sezon</button>
                            <button type="submit" class="button button-primary kc-save-btn" style="margin-left:6px;">
                                Zapisz: <?php echo esc_html($vlabel); ?>
                            </button>
                            <span class="kc-save-msg" style="display:none; margin-left:10px;"></span>
                        </div>
                    </form>
                    <hr>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB: POJAZDY -->
        <div class="tab-pane fade" id="kc-pojazdy" role="tabpanel">
            <div class="container kc-tab-content">
                <h3>Typy pojazdów</h3>
                <table class="wp-list-table widefat fixed striped" id="kc-vehicle-types-table">
                    <thead>
                        <tr>
                            <th>Klucz (slug)</th>
                            <th>Nazwa wyświetlana</th>
                            <th style="width:80px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicle_types as $vslug => $vlabel) : ?>
                            <tr>
                                <td><?php echo esc_html($vslug); ?></td>
                                <td><?php echo esc_html($vlabel); ?></td>
                                <td>
                                    <button type="button" class="button kc-delete-vehicle-type"
                                        data-slug="<?php echo esc_attr($vslug); ?>"
                                        data-label="<?php echo esc_attr($vlabel); ?>">
                                        Usuń
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h3 style="margin-top: 24px;">Dodaj nowy typ pojazdu</h3>
                <form id="kc-add-vehicle-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="kc_vslug">Klucz (slug)</label></th>
                            <td>
                                <input type="text" id="kc_vslug" name="slug" class="regular-text"
                                    placeholder="np. quad" pattern="[a-z0-9_-]+" required>
                                <p class="description">Małe litery, cyfry, myślniki, podkreślenia. Nie można zmienić po zapisaniu.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="kc_vlabel">Nazwa wyświetlana</label></th>
                            <td>
                                <input type="text" id="kc_vlabel" name="label" class="regular-text"
                                    placeholder="np. Quad" required>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="submit" class="button button-primary">Dodaj pojazd</button>
                        <span id="kc-vehicle-msg" style="display:none; margin-left:10px;"></span>
                    </p>
                    <p class="description">Po dodaniu pojazd automatycznie otrzyma domyślną listę sezonów skopiowaną z Kampera.</p>
                </form>
            </div>
        </div>

        <!-- TAB: USTAWIENIA -->
        <div class="tab-pane fade" id="kc-ustawienia" role="tabpanel">
            <div class="container kc-tab-content">
                <form id="kc-settings-form">
                    <h3>Podatek VAT</h3>
                    <table class="form-table">
                        <tr>
                            <th><label for="kc_vat">VAT (%)</label></th>
                            <td>
                                <input type="number" id="kc_vat" name="vat" min="0" max="100"
                                    value="<?php echo esc_attr($vat); ?>" class="small-text" required>
                            </td>
                        </tr>
                    </table>

                    <h3>Opłaty serwisowe i kaucje</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Pojazd</th>
                                <th>Opłata netto (zł)</th>
                                <th>Opłata brutto (zł)</th>
                                <th>Kaucja (zł)</th>
                                <th>Podstawienie netto (zł/km)</th>
                                <th>Podstawienie brutto (zł/km)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehicle_types as $vtype => $vlabel) :
                                $f = isset($fees[$vtype]) ? array_merge($fee_defaults, $fees[$vtype]) : $fee_defaults;
                            ?>
                                <tr>
                                    <td><?php echo esc_html($vlabel); ?></td>
                                    <td><input type="number" min="0" step="0.01"
                                            name="fees[<?php echo esc_attr($vtype); ?>][service_pay_netto]"
                                            value="<?php echo esc_attr($f['service_pay_netto']); ?>"
                                            class="small-text" required></td>
                                    <td><input type="number" min="0" step="0.01"
                                            name="fees[<?php echo esc_attr($vtype); ?>][service_pay_brutto]"
                                            value="<?php echo esc_attr($f['service_pay_brutto']); ?>"
                                            class="small-text" required></td>
                                    <td><input type="number" min="0" step="0.01"
                                            name="fees[<?php echo esc_attr($vtype); ?>][deposit]"
                                            value="<?php echo esc_attr($f['deposit']); ?>"
                                            class="small-text" required></td>
                                    <td><input type="number" min="0" step="0.01"
                                            name="fees[<?php echo esc_attr($vtype); ?>][delivery_netto]"
                                            value="<?php echo esc_attr($f['delivery_netto']); ?>"
                                            class="small-text" required></td>
                                    <td><input type="number" min="0" step="0.01"
                                            name="fees[<?php echo esc_attr($vtype); ?>][delivery_brutto]"
                                            value="<?php echo esc_attr($f['delivery_brutto']); ?>"
                                            class="small-text" required></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p>
                        <button type="submit" class="button button-primary" style="margin-top:10px;">Zapisz ustawienia</button>
                        <span id="kc-settings-msg" style="display:none; margin-left:10px;"></span>
                    </p>
                </form>
            </div>
        </div>

        <!-- TAB: KODY RABATOWE -->
        <div class="tab-pane fade" id="kc-kody" role="tabpanel">
            <div class="container kc-tab-content">
                <h3>Aktywne kody rabatowe</h3>
                <table class="wp-list-table widefat fixed striped" id="kc-discount-table">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Typ rabatu</th>
                            <th>Wartość</th>
                            <th>Status</th>
                            <th style="width:160px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($discount_codes)) : ?>
                            <tr id="kc-no-codes">
                                <td colspan="5">Brak kodów rabatowych.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($discount_codes as $dc) : ?>
                                <tr data-code="<?php echo esc_attr($dc['code']); ?>">
                                    <td><strong><?php echo esc_html($dc['code']); ?></strong></td>
                                    <td><?php echo $dc['type'] === 'percent' ? 'Procentowy' : 'Kwotowy'; ?></td>
                                    <td><?php echo esc_html($dc['value']); ?><?php echo $dc['type'] === 'percent' ? ' %' : ' zł'; ?></td>
                                    <td class="kc-dc-status"><?php echo $dc['active'] ? '<span style="color:green">Aktywny</span>' : '<span style="color:#aaa">Nieaktywny</span>'; ?></td>
                                    <td>
                                        <button type="button" class="button kc-toggle-discount"
                                            data-code="<?php echo esc_attr($dc['code']); ?>"
                                            data-active="<?php echo (int) $dc['active']; ?>">
                                            <?php echo $dc['active'] ? 'Dezaktywuj' : 'Aktywuj'; ?>
                                        </button>
                                        <button type="button" class="button kc-delete-discount"
                                            data-code="<?php echo esc_attr($dc['code']); ?>"
                                            style="margin-left:4px;">
                                            Usuń
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <h3 style="margin-top: 24px;">Dodaj nowy kod</h3>
                <form id="kc-add-discount-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="kc_dcode">Kod</label></th>
                            <td>
                                <input type="text" id="kc_dcode" name="code" class="regular-text"
                                    placeholder="np. LATO2025" required style="text-transform:uppercase;">
                                <p class="description">Kod wpisywany przez klienta w formularzu rezerwacji. Automatycznie konwertowany do wielkich liter.</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="kc_dtype">Typ rabatu</label></th>
                            <td>
                                <select id="kc_dtype" name="type">
                                    <option value="percent">Procentowy — od wartości wynajmu (%)</option>
                                    <option value="fixed">Kwotowy — stała kwota (zł)</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="kc_dvalue">Wartość rabatu</label></th>
                            <td>
                                <input type="number" id="kc_dvalue" name="value" min="0.01" step="0.01"
                                    class="small-text" required>
                                <span id="kc_dvalue_unit">%</span>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="submit" class="button button-primary">Dodaj kod</button>
                        <span id="kc-discount-msg" style="display:none; margin-left:10px;"></span>
                    </p>
                </form>
            </div>
        </div>

        <!-- TAB: KONTAKT -->
        <div class="tab-pane fade" id="kc-kontakt" role="tabpanel">
            <div class="container kc-tab-content">
                <form id="kc-contact-form">
                    <table class="form-table">
                        <tr>
                            <th><label for="kc_email">Adres e-mail do powiadomień</label></th>
                            <td>
                                <input type="email" id="kc_email" name="kc_email"
                                    value="<?php echo esc_attr($email); ?>"
                                    class="regular-text" required>
                                <p class="description">Na ten adres trafiają zapytania o rezerwację. Domyślnie używany jest adres administratora WordPress.</p>
                            </td>
                        </tr>
                    </table>
                    <p>
                        <button type="submit" class="button button-primary">Zapisz e-mail</button>
                        <span id="kc-contact-msg" style="display:none; margin-left:10px;"></span>
                    </p>
                </form>
            </div>
        </div>

    </div><!-- /.tab-content -->
</div><!-- /.wrap -->
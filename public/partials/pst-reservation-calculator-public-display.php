<?php
/**
 * Public-facing view — calculator and reservation form.
 *
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="">
    <form id="formCalculate" action="javascript:void(0)">
        <div class="kalkulator">
            <div>
                <label class="kamp-label">Wybierz termin wynajmu
                    <span class="kamp-required-char">*</span>
                </label>
            </div>
            <div class="form-elements">
                <input id="StartDate" readonly="readonly" name="start_date" class="hasDatepicker" required placeholder="od">
                <input id="EndDate"   readonly="readonly" name="end_date"   class="hasDatepicker" required placeholder="do">
            </div>

            <!-- Kod rabatowy -->
            <div class="form-elements" style="margin-top:8px; align-items:center; gap:6px;">
                <input id="discount_code" name="discount_code_display" type="text"
                    placeholder="Kod rabatowy (opcjonalnie)"
                    style="text-transform:uppercase; max-width:220px;">
                <button type="button" id="kc-apply-discount" class="button-post" style="padding:6px 14px;">
                    Zastosuj
                </button>
                <span id="kc-discount-msg" style="display:none; font-size:0.9em;"></span>
            </div>

            <div class="form-info">
                <label class="d-flex flex-row m-0">
                    <span class="kamp-text-before">Suma:</span>
                    <span class="wyniknetto"></span>
                    <span class="kamp-text-currency">zł</span>
                    <span class="kamp-text-before">netto</span>

                    <span class="wynikbrutto"></span>
                    <span class="kamp-text-currency">zł</span>
                    <span class="kamp-text-before">brutto</span>
                    <span id="opis"></span>
                    <p class="desc"></p>
                    <div class="error">
                        <span></span>
                    </div>
                    <input type="hidden" id="type" name="type" value="<?php echo esc_attr( $atts['type'] ); ?>">
                </label>
            </div>

            <p class="kamp-element-description-below-input">
                + opłata serwisowa: <span class="service_pay_netto"></span> zł netto / <span class="service_pay_brutto"></span> zł brutto<br>
                + zwrotna kaucja: <span class="deposit"></span> zł brutto<br>
                + podstawienie: <span class="delivery_netto"></span> zł netto/km / <span class="delivery_brutto"></span> zł brutto/km
            </p>

            <button type="submit" class="button-post my-2" id="calc">REZERWUJĘ !</button>
        </div>
    </form>
</div>

<div class="popup-calc" style="display:none;">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="popup-content product-offer-page bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto relative">
            
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white rounded-t-2xl z-10">
                <h2 class="text-xl font-semibold text-gray-800 tracking-wide">Formularz kontaktowy</h2>
                <button class="icon close-popup w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-500 hover:text-gray-800">
                    <span class="dashicons dashicons-no-alt text-xl"></span>
                </button>
            </div>

            <div role="form" class="wpcf7 px-6 py-5" id="wpcf7-f322-o1" lang="pl-PL" dir="ltr">
                <div class="screen-reader-response"></div>
                <form id="contactForm" action="javascript:void(0)" class="wpcf7-form">
                    <div style="display:none;">
                        <input type="hidden" name="_wpcf7" value="322">
                        <input type="hidden" name="_wpcf7_version" value="5.0.1">
                        <input type="hidden" name="_wpcf7_locale" value="pl_PL">
                        <input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f322-o1">
                        <input type="hidden" name="_wpcf7_container_post" value="0">
                        <input type="hidden" id="popup_discount_code" name="discount_code" value="">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <!-- Typ pojazdu (readonly) -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Typ pojazdu</label>
                            <span class="wpcf7-form-control-wrap <?php echo esc_attr( $atts['type'] ); ?>">
                                <input type="text" name="type-description" value="<?php the_title_attribute(); ?>" size="40"
                                    class="wpcf7-form-control wpcf7-text w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed focus:outline-none"
                                    id="reserved" readonly="readonly" aria-invalid="false">
                            </span>
                        </div>

                        <!-- Imię -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Imię lub nazwa firmy <span class="text-red-500">*</span></label>
                            <span class="wpcf7-form-control-wrap your-name">
                                <input type="text" name="your-name" value="" size="40"
                                    class="wpcf7-form-control wpcf7-text wpcf7-validates-as-required w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    aria-required="true" aria-invalid="false"
                                    placeholder="Imię lub nazwa firmy" required>
                            </span>
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Adres email <span class="text-red-500">*</span></label>
                            <span class="wpcf7-form-control-wrap your-email">
                                <input type="email" name="your-email" value="" size="40"
                                    class="wpcf7-form-control wpcf7-text wpcf7-email wpcf7-validates-as-required wpcf7-validates-as-email w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    aria-required="true" aria-invalid="false"
                                    placeholder="Twój adres email" required>
                            </span>
                        </div>

                        <!-- Telefon -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Numer telefonu <span class="text-red-500">*</span></label>
                            <span class="wpcf7-form-control-wrap nr-tel">
                                <input type="tel" name="nr-tel" value="" size="40"
                                    class="wpcf7-form-control wpcf7-text wpcf7-tel wpcf7-validates-as-required wpcf7-validates-as-tel w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    aria-required="true" aria-invalid="false"
                                    placeholder="Twój numer telefonu" required>
                            </span>
                        </div>

                        <!-- Temat -->
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Temat</label>
                            <span class="wpcf7-form-control-wrap your-subject">
                                <input type="text" name="your-subject"
                                    value="Wynajem/Rezerwacja: <?php echo esc_attr( $atts['type'] ); ?>" size="40"
                                    class="wpcf7-form-control wpcf7-text w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    aria-invalid="false" placeholder="Temat">
                            </span>
                        </div>

                        <!-- Daty -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Data od</label>
                            <span class="wpcf7-form-control-wrap data-od">
                                <input type="date" name="data-od" value=""
                                    class="wpcf7-form-control wpcf7-date wpcf7-validates-as-date w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed focus:outline-none"
                                    id="DateStartPop" readonly="readonly" aria-invalid="false">
                            </span>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Data do</label>
                            <span class="wpcf7-form-control-wrap data-do">
                                <input type="date" name="data-do" value=""
                                    class="wpcf7-form-control wpcf7-date wpcf7-validates-as-date w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-500 text-sm cursor-not-allowed focus:outline-none"
                                    id="DateEndPop" readonly="readonly" aria-invalid="false">
                            </span>
                        </div>

                        <!-- Podsumowanie cen -->
                        <div class="sm:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4 space-y-1.5">
                            <p class="text-sm font-semibold text-blue-800">
                                SUMA: <span class="wyniknetto" name="wyniknetto"></span> zł netto /
                                <span class="wynikbrutto" name="wynikbrutto"></span> zł brutto
                            </p>
                            <input type="hidden" class="wyniknetto" name="wyniknetto">
                            <input type="hidden" class="wynikbrutto" name="wynikbrutto">
                            <p class="text-xs text-gray-600">+ opłata serwisowa:
                                <span class="service_pay_netto font-medium"></span> zł netto /
                                <span class="service_pay_brutto font-medium"></span> zł brutto
                            </p>
                            <p class="text-xs text-gray-600">+ zwrotna kaucja:
                                <span class="deposit font-medium"></span> zł brutto
                            </p>
                            <p class="text-xs text-gray-600">+ podstawienie:
                                <span class="delivery_netto font-medium"></span> zł netto/km /
                                <span class="delivery_brutto font-medium"></span> zł brutto/km
                            </p>
                        </div>

                        <!-- Wiadomość -->
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wider">Wiadomość <span class="text-red-500">*</span></label>
                            <span class="wpcf7-form-control-wrap your-message">
                                <textarea name="your-message" cols="40" rows="5"
                                    class="wpcf7-form-control wpcf7-textarea w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
                                    aria-invalid="false"
                                    placeholder="Wpisz treść wiadomości" required></textarea>
                            </span>
                        </div>

                        <!-- Submit -->
                        <div class="sm:col-span-2 flex items-center gap-4 pt-2">
                            <input type="submit" value="Wyślij wiadomość"
                                class="wpcf7-form-control wpcf7-submit cursor-pointer bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold px-8 py-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                            <span class="ajax-loader"></span>
                        </div>

                    </div>

                    <div class="wpcf7-response-output wpcf7-display-none mt-4 text-sm text-center rounded-lg px-4 py-3"></div>
                </form>
            </div>
        </div>
    </div>
</div>

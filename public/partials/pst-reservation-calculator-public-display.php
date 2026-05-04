<?php

/**
 * Public-facing view — calculator and reservation form.
 *
 * @since   1.0.0
 * @package PST_Reservation_Calculator
 */
if (! defined('ABSPATH')) exit;
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
                <input id="EndDate" readonly="readonly" name="end_date" class="hasDatepicker" required placeholder="do">
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
                    <input type="hidden" id="type" name="type" value="<?php echo esc_attr($atts['type']); ?>">
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

<div class="popup-calc fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="popup-content bg-white rounded-3xl shadow-2xl max-w-2xl overflow-y-auto relative">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 sticky top-0 bg-white rounded-t-3xl z-10">
            <h2 class="text-lg font-bold text-gray-900 tracking-tight">Formularz kontaktowy</h2>
            <button class="close-popup w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 hover:bg-gray-200 transition text-gray-500">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>

        <div role="form" id="wpcf7-f322-o1" lang="pl-PL" dir="ltr" class="px-8 py-6">
            <div class="screen-reader-response"></div>
            <form id="contactForm" action="javascript:void(0)">
                <div style="display:none;">
                    <input type="hidden" name="_wpcf7" value="322">
                    <input type="hidden" name="_wpcf7_version" value="5.0.1">
                    <input type="hidden" name="_wpcf7_locale" value="pl_PL">
                    <input type="hidden" name="_wpcf7_unit_tag" value="wpcf7-f322-o1">
                    <input type="hidden" name="_wpcf7_container_post" value="0">
                    <input type="hidden" id="popup_discount_code" name="discount_code" value="">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- Typ pojazdu -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Typ pojazdu</label>
                        <span class="wpcf7-form-control-wrap <?php echo esc_attr($atts['type']); ?>">
                            <input type="text" name="type-description" value="<?php the_title_attribute(); ?>"
                                id="reserved" readonly
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed outline-none">
                        </span>
                    </div>

                    <!-- Imię -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Imię lub nazwa firmy <span class="text-rose-500">*</span></label>
                        <span class="wpcf7-form-control-wrap your-name">
                            <input type="text" name="your-name"
                                placeholder="Imię lub nazwa firmy" required aria-required="true"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                        </span>
                    </div>

                    <!-- Email -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Adres email <span class="text-rose-500">*</span></label>
                        <span class="wpcf7-form-control-wrap your-email">
                            <input type="email" name="your-email"
                                placeholder="Twój adres email" required aria-required="true"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                        </span>
                    </div>

                    <!-- Telefon -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Numer telefonu <span class="text-rose-500">*</span></label>
                        <span class="wpcf7-form-control-wrap nr-tel">
                            <input type="tel" name="nr-tel"
                                placeholder="Twój numer telefonu" required aria-required="true"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                        </span>
                    </div>

                    <!-- Temat -->
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Temat</label>
                        <span class="wpcf7-form-control-wrap your-subject">
                            <input type="text" name="your-subject"
                                value="Wynajem/Rezerwacja: <?php echo esc_attr($atts['type']); ?>"
                                placeholder="Temat"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition">
                        </span>
                    </div>

                    <!-- Daty -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Data od</label>
                        <span class="wpcf7-form-control-wrap data-od">
                            <input type="date" name="data-od"
                                id="DateStartPop" readonly
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed outline-none">
                        </span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Data do</label>
                        <span class="wpcf7-form-control-wrap data-do">
                            <input type="date" name="data-do"
                                id="DateEndPop" readonly
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-400 text-sm cursor-not-allowed outline-none">
                        </span>
                    </div>

                    <!-- Podsumowanie cen -->
                    <div class="sm:col-span-2 bg-indigo-50 border border-indigo-100 rounded-2xl p-5 space-y-2">
                        <p class="text-sm font-bold text-indigo-700">
                            SUMA: <span class="wyniknetto"></span> zł netto /
                            <span class="wynikbrutto"></span> zł brutto
                        </p>
                        <input type="hidden" class="wyniknetto" name="wyniknetto">
                        <input type="hidden" class="wynikbrutto" name="wynikbrutto">
                        <p class="text-xs text-indigo-500">+ opłata serwisowa:
                            <span class="service_pay_netto font-semibold"></span> zł netto /
                            <span class="service_pay_brutto font-semibold"></span> zł brutto
                        </p>
                        <p class="text-xs text-indigo-500">+ zwrotna kaucja:
                            <span class="deposit font-semibold"></span> zł brutto
                        </p>
                        <p class="text-xs text-indigo-500">+ podstawienie:
                            <span class="delivery_netto font-semibold"></span> zł netto/km /
                            <span class="delivery_brutto font-semibold"></span> zł brutto/km
                        </p>
                    </div>

                    <!-- Wiadomość -->
                    <div class="flex flex-col gap-1.5 sm:col-span-2">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Wiadomość <span class="text-rose-500">*</span></label>
                        <span class="wpcf7-form-control-wrap your-message">
                            <textarea name="your-message" rows="4"
                                placeholder="Wpisz treść wiadomości" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 text-sm text-gray-800 outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition resize-none"></textarea>
                        </span>
                    </div>

                    <!-- Submit -->
                    <div class="sm:col-span-2 flex items-center gap-4 pt-1">
                        <input type="submit" value="Wyślij wiadomość"
                            class="cursor-pointer bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-sm font-bold px-8 py-3 rounded-xl shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all duration-200">
                        <span class="ajax-loader"></span>
                    </div>

                </div>

                <div class="wpcf7-response-output wpcf7-display-none mt-5 text-sm text-center rounded-xl px-4 py-3"></div>
            </form>
        </div>

    </div>
</div>
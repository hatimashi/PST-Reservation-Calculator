<?php

/**
 * Public-facing view — calculator and reservation form.
 *
 * @since   1.0.0
 * @package PSTRC_Reservation_Calculator
 */
if (! defined('ABSPATH')) exit;
?>

<div class="w-full">
    <form id="formCalculate" action="javascript:void(0)">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-lg p-6 space-y-5">

            <!-- Wybierz termin -->
            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-gray-600 uppercase tracking-widest">
                    Wybierz termin wynajmu
                    <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <input id="StartDate" readonly name="start_date"
                        class="hasDatepicker w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 cursor-pointer"
                        required placeholder="Data od">
                    <input id="EndDate" readonly name="end_date"
                        class="hasDatepicker w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 cursor-pointer"
                        required placeholder="Data do">
                </div>
            </div>

            <!-- Kod rabatowy -->
            <div class="flex flex-wrap items-center gap-3">
                <input id="discount_code" name="discount_code_display" type="text"
                    placeholder="Kod rabatowy (opcjonalnie)"
                    class="flex-1 min-w-0 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-700 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 uppercase">
                <button type="button" id="kc-apply-discount"
                    class="button-post px-5 py-3 rounded-xl bg-gradient-to-r from-fuchsia-500 via-purple-500 to-indigo-500 text-white text-sm font-semibold hover:opacity-90 transition whitespace-nowrap">
                    Zastosuj
                </button>
                <span id="kc-discount-msg" class="w-full text-sm hidden"></span>
            </div>

            <!-- Suma -->
            <div class="rounded-xl bg-indigo-50 border border-indigo-100 px-4 py-3 space-y-2">
                <div class="flex flex-wrap items-center gap-1.5 text-sm">
                    <span class="text-gray-500">Suma:</span>
                    <span class="wyniknetto font-bold text-indigo-700"></span>
                    <span class="text-gray-500">zł netto</span>
                    <span class="text-gray-300">/</span>
                    <span class="wynikbrutto font-bold text-indigo-700"></span>
                    <span class="text-gray-500">zł brutto</span>
                    <span id="opis" class="text-gray-400 text-xs"></span>
                    <input type="hidden" id="type" name="type" value="<?php echo esc_attr($atts['type']); ?>">
                    <input type="hidden" name="wyniknetto" id="wyniknetto">
                    <input type="hidden" name="wynikbrutto" id="wynikbrutto">
                </div>
                <p class="desc text-xs text-gray-400"></p>
                <div class="error hidden"><span class="text-xs text-rose-500"></span></div>

                <!-- Dodatkowe opłaty -->
                <div id="additional_fees" class="text-xs text-gray-500 space-y-0.5 pt-1 border-t border-indigo-100">
                    <p>+ opłata serwisowa:
                        <span class="service_pay_netto font-medium text-gray-700"></span> zł netto /
                        <span class="service_pay_brutto font-medium text-gray-700"></span> zł brutto
                    </p>
                    <p>+ zwrotna kaucja:
                        <span class="deposit font-medium text-gray-700"></span> zł brutto
                    </p>
                    <p>+ podstawienie:
                        <span class="delivery_netto font-medium text-gray-700"></span> zł netto/km /
                        <span class="delivery_brutto font-medium text-gray-700"></span> zł brutto/km
                    </p>
                </div>
            </div>

            <!-- Przycisk -->
            <button type="submit" id="calc"
                class="button-post w-full py-3.5 rounded-xl bg-gradient-to-r from-fuchsia-500 via-purple-500 to-indigo-500 text-white font-bold text-sm tracking-wide hover:opacity-90 active:scale-95 transition-all shadow-lg">
                REZERWUJĘ !
            </button>

        </div>
    </form>
</div>
<div class="popup-calc fixed inset-0 top-6 bottom-6 bg-gradient-to-br from-slate-900 via-purple-900 to-black text-white backdrop-blur-sm z-[9999] grid place-items-center  p-6 overflow-none" style="display:none;">
    <!-- <div class="popup-calc fixed min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-black text-white grid place-items-center p-6" style="display:none;">
 -->
    <div class="w-full max-w-7xl">
        <!-- Przycisk zamknięcia -->
        <button class="close-popup absolute top-2 right-2 w-9 h-9 rounded-full bg-black/5 hover:bg-white/20 flex items-center justify-center transition z-10">
            <i data-lucide="x" class="w-4 h-4 text-white"></i>
        </button>
        <!-- Header / Intro -->
        <div class="mb-6 text-center">
            <h1 class="text-3xl font-extrabold tracking-tight" id="h1-title"></h1>
            <p class="text-white/70 mt-1">Wypełnij formularz rezerwacji.</p>
        </div>

        <!-- Card -->
        <div class="relative rounded-2xl border border-white/15 bg-white/10 backdrop-blur-xl shadow-glow overflow-hidden">

            <!-- Stepper -->
            <div class="relative px-6 pt-6 pb-4">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-3">
                        <div class="step-dot h-8 w-8 rounded-full grid place-items-center bg-white/20 border border-white/30" data-step="0">
                            <span class="font-semibold">1</span>
                        </div>
                        <div class="hidden sm:block">
                            <div class="font-semibold">Dane kontaktowe</div>
                            <div class="text-white/60 text-xs"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="step-dot h-8 w-8 rounded-full grid place-items-center bg-white/10 border border-white/20" data-step="1">
                            <span class="font-semibold">2</span>
                        </div>
                        <div class="hidden sm:block">
                            <div class="font-semibold">Dodatkowe informacje</div>
                            <div class="text-white/60 text-xs"></div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="step-dot h-8 w-8 rounded-full grid place-items-center bg-white/10 border border-white/20" data-step="2">
                            <span class="font-semibold">3</span>
                        </div>
                        <div class="hidden sm:block">
                            <div class="font-semibold">Podsumowanie</div>
                            <div class="text-white/60 text-xs"></div>
                        </div>
                    </div>
                </div>

                <!-- Progress line -->
                <div class="absolute left-6 right-6 top-[72px] h-1 bg-white/10 rounded-full">
                    <div id="progress" class="h-1 bg-gradient-to-r from-fuchsia-500 via-purple-500 to-indigo-500 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
            </div>

            <!-- Form body -->
            <form id="msf" class="p-6 space-y-6" novalidate>

                <!-- STEP 1: Account -->
                <section class="step" data-step="0">
                    <div class="grid gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium mb-1">Email</label>
                            <div class="relative">
                                <input id="email" name="email" type="email" autocomplete="email" required class="peer w-full rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 px-11 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="twoj@adres.pl" />
                                <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white/60"></i>
                            </div>
                            <p class="mt-1 text-xs text-red-300 hidden" data-error="email">Wpisz prawidłowy adres email.</p>
                        </div>

                        <div>
                            <label for="fullname" class="block text-sm font-medium mb-1">Imię i nazwisko</label>
                            <div class="relative">
                                <input id="fullname" name="fullname" type="text" autocomplete="name" required class="peer w-full rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 px-11 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Twoje imię i nazwisko" />
                                <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white/60"></i>
                            </div>
                            <p class="mt-1 text-xs text-red-300 hidden" data-error="fullname">Wpisz prawidłowe imię i nazwisko.</p>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium mb-1">Numer telefonu</label>
                            <div class="relative">
                                <input id="phone" name="phone" type="text" autocomplete="tel" required class="peer w-full rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/40 px-11 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Twój numer telefonu" />
                                <i data-lucide="phone" class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-white/60"></i>
                            </div>
                            <p class="mt-1 text-xs text-red-300 hidden" data-error="phone">Wpisz prawidłowy numer telefonu.</p>
                        </div>



                        <div class="flex items-center gap-2 text-sm">
                            <input id="tos1" type="checkbox" class="accent-purple-500">
                            <label for="tos1" class="text-white/80">Zaakceptuj nasz <a href="#" class="underline decoration-purple-400/70 hover:text-white">Regulamin</a>.</label>
                        </div>
                    </div>
                </section>

                <!-- STEP 2: Profile -->
                <section class="step hidden" data-step="1">
                    <div class="grid gap-4">
                        <div>
                            <label for="extramessage" class="block text-sm font-medium mb-1">Dodatkowe informacje</label>
                            <div class="relative">
                                <textarea id="extramessage" name="extramessage" rows="3" required class="w-full appearance-none rounded-xl bg-white/10 border border-white/20 text-white placeholder:white/40 px-11 py-3 focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Wpisz dodatkowe informacje"></textarea>
                            </div>
                            <p class="mt-1 text-xs text-red-300 hidden" data-error="extramessage">Please provide additional information.</p>
                        </div>
                        <p class="mt-1 text-xs text-red-300 hidden" data-error="extramessage">Please select your role.</p>
                    </div>

                </section>

                <!-- STEP 3: Review -->
                <section class="step hidden" data-step="2">
                    <div class="grid gap-4">
                        <div class="rounded-xl border border-white/15 bg-white/5 p-4">
                            <h3 class="font-semibold mb-2">Podsumowanie</h3>
                            <dl class="grid grid-cols-3 gap-2 text-sm">
                                <dt class="text-white/60">Email</dt>
                                <dd class="col-span-2 font-medium" id="r-email">—</dd>

                                <dt class="text-white/60">Imię i Nazwisko</dt>
                                <dd class="col-span-2 font-medium" id="r-fullname">—</dd>

                                <dt class="text-white/60">Phone</dt>
                                <dd class="col-span-2 font-medium" id="r-phone">—</dd>

                                <dt class="text-white/60">Dodatkowe informacje</dt>
                                <dd class="col-span-2 font-medium" id="r-extramessage">—</dd>

                                <dt class="text-white/60">Typ pojazdu</dt>
                                <dd class="col-span-2 font-medium" id="r-type"><?php echo esc_attr($atts['type']); ?></dd>

                                <dt class="text-white/60">Okres rezerwacji</dt>
                                <dd class="col-span-2 font-medium" id="r-dates">—</dd>

                                <dt class="text-white/60">Suma <small>netto/brutto</small></dt>
                                <dd class="col-span-2 font-medium" id="r-reservation-sum">—</dd>

                                <dt class="text-white/60">Dodatkowe opłaty</dt>
                                <dd class="col-span-2 font-medium" id="r-additional-fees">—</dd>

                            </dl>
                        </div>

                        <label class="flex items-start gap-3 text-sm">
                            <input id="consent" type="checkbox" class="mt-1 accent-purple-500" required>
                            <span>Potwierdzam, że podane powyżej dane są prawidłowe i wyrażam zgodę na przetwarzanie zgodnie z polityką prywatności.</span>
                        </label>
                        <p class="mt-1 text-xs text-red-300 hidden" data-error="consent">Aby kontynuować, musisz wyrazić zgodę.</p>
                    </div>
                </section>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" id="backBtn" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 disabled:opacity-40 disabled:cursor-not-allowed" disabled>Powrót</button>

                    <div class="flex gap-2">
                        <button type="button" id="nextBtn" class="px-4 py-2 rounded-lg bg-gradient-to-r from-fuchsia-500 via-purple-500 to-indigo-500 hover:opacity-90">
                            Dalej
                        </button>
                        <button type="submit" id="submitBtn" class="hidden px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600">Submit</button>
                    </div>
                </div>
            </form>

            <!-- Success state -->
            <div id="success" class="hidden p-10 text-center">
                <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-emerald-500/20 grid place-items-center">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold">Wszystko gotowe!</h3>
                <p class="text-white/70 mt-1">Twoja rezerwacja została wysłana.</p>
            </div>
        </div>

        <!-- Footer note -->
        <p class="mt-4 text-center text-xs text-white/50"></p>
    </div>
</div>
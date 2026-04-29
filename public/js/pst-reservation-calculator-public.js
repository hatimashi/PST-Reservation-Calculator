(function ($) {
    'use strict';

    $(function () {
        $("#EndDate").datepicker({ language: 'pl-PL' });
        $("#StartDate").datepicker({ language: 'pl-PL' });
    });

    jQuery(document).ready(function () {

        var vat      = parseInt( pst_rc_ajax.vat, 10 ) || 23;
        var discount = { type: null, value: 0 };
        var lastData = {};

        // ---- Oblicz cenę po rabacie ----
        function applyDiscount( netto ) {
            if ( ! discount.type || ! discount.value ) {
                return netto;
            }
            var result = discount.type === 'percent'
                ? netto * ( 1 - discount.value / 100 )
                : netto - discount.value;
            return Math.max( 0, Math.round( result * 100 ) / 100 );
        }

        // ---- Wyrenderuj ceny w DOM ----
        function renderPrices( data ) {
            lastData = data;

            jQuery( '.service_pay_netto'  ).text( data.service_pay_netto  || '' );
            jQuery( '.service_pay_brutto' ).text( data.service_pay_brutto || '' );
            jQuery( '.deposit'            ).text( data.deposit            || '' );
            jQuery( '.delivery_netto'     ).text( data.delivery_netto     || '' );
            jQuery( '.delivery_brutto'    ).text( data.delivery_brutto    || '' );

            if ( data.sum ) {
                var netto  = applyDiscount( parseFloat( data.sum ) || 0 );
                var brutto = Math.round( ( netto / 100 * vat + netto ) * 100 ) / 100;

                jQuery( '.wyniknetto'  ).text( netto  ).val( netto  );
                jQuery( '.wynikbrutto' ).text( brutto ).val( brutto );

                jQuery( '#DateStartPop' ).val( jQuery( '#StartDate' ).val() );
                jQuery( '#DateEndPop'   ).val( jQuery( '#EndDate'   ).val() );
            }
        }

        // ---- Wywołaj AJAX kalkulatora ----
        function postCalculate() {
            var postdata = jQuery( '#formCalculate' ).serialize()
                + '&action=pst_rc_calculate'
                + '&param=calculate_prices'
                + '&nonce=' + pst_rc_ajax.nonce;

            jQuery.post( pst_rc_ajax.ajaxurl, postdata, function ( response ) {
                var data = ( typeof response === 'string' ) ? JSON.parse( response ) : response;
                renderPrices( data );
            });
        }

        // Wczytaj opłaty przy starcie strony (bez dat)
        postCalculate();

        // Przelicz przy każdej zmianie daty
        jQuery( '#formCalculate' ).on( 'input', function () {
            postCalculate();
        });

        // ---- Kod rabatowy ----
        jQuery( '#kc-apply-discount' ).on( 'click', function () {
            var code = jQuery.trim( jQuery( '#discount_code' ).val().toUpperCase() );
            jQuery( '#discount_code' ).val( code );

            var $msg = jQuery( '#kc-discount-msg' );

            if ( ! code ) {
                discount = { type: null, value: 0 };
                jQuery( '#popup_discount_code' ).val( '' );
                $msg.hide();
                renderPrices( lastData );
                return;
            }

            jQuery.post( pst_rc_ajax.ajaxurl, {
                action : 'pst_rc_validate_discount',
                nonce  : pst_rc_ajax.nonce,
                code   : code,
            }, function ( response ) {
                if ( response && response.success ) {
                    discount = response.data;
                    var info = discount.type === 'percent'
                        ? '−' + discount.value + '%'
                        : '−' + discount.value + ' zł';
                    $msg.text( 'Rabat zastosowany: ' + info )
                        .css( 'color', 'green' ).show();
                    jQuery( '#popup_discount_code' ).val( code );
                } else {
                    discount = { type: null, value: 0 };
                    jQuery( '#popup_discount_code' ).val( '' );
                    $msg.text( ( response && response.data ) || 'Nieprawidłowy kod.' )
                        .css( 'color', 'red' ).show();
                }
                renderPrices( lastData );
            });
        });

        // ---- Walidacja formularza kalkulatora → otwórz popup ----
        jQuery( '#formCalculate' ).validate({
            submitHandler: function () {
                jQuery( '.popup-calc' ).css( 'display', 'block' );
            }
        });

        // ---- Wysyłka e-maila rezerwacyjnego ----
        jQuery( '#contactForm' ).validate({
            submitHandler: function () {
                var postdata = jQuery( '#contactForm' ).serialize()
                    + '&action=pst_rc_email'
                    + '&param=send_email'
                    + '&nonce=' + pst_rc_ajax.nonce;

                jQuery.post( pst_rc_ajax.ajaxurl, postdata, function ( response ) {
                    jQuery( '.popup-calc' ).css( 'display', 'none' );
                    jQuery( '#mailInfoPopup' ).css( 'display', 'block' );
                    var msg = ( response == 1 )
                        ? 'Wiadomość wysłana poprawnie.'
                        : 'Błąd wysyłania wiadomości. Skontaktuj się z nami telefonicznie.';
                    jQuery( '#mailInfoPopup span' ).text( msg );
                });
            },
            invalidHandler: function ( event, validator ) {
                var errors = validator.numberOfInvalids();
                if ( errors ) {
                    var message = errors === 1
                        ? 'Nie uzupełniłeś 1 pola. Pole zostało podświetlone.'
                        : 'Uzupełnij ' + errors + ' pola. Pola zostały podświetlone.';
                    $( 'div.error span' ).html( message );
                    $( 'div.error' ).show();
                } else {
                    $( 'div.error' ).hide();
                }
            }
        });

        // ---- Zamknij popup ----
        jQuery( '.close-popup' ).on( 'click', function () {
            jQuery( '.popup-calc' ).css( 'display', 'none' );
        });
    });
})(jQuery);

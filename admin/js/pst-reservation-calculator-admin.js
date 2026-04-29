(function( $ ) {
    'use strict';

    function showMsg( $el, text, ok ) {
        $el.text( text )
            .css( 'color', ok ? 'green' : 'red' )
            .show();
        setTimeout( function() { $el.fadeOut(); }, 3500 );
    }

    jQuery(document).ready(function() {

        // ================================================================
        // SEZONY — zapis istniejących i nowych wierszy
        // ================================================================
        $( '.kc-season-form' ).each(function() {
            var $form    = $( this );
            var $msg     = $form.find( '.kc-save-msg' );
            var newIndex = 0;

            // Dodaj nowy pusty wiersz
            $form.find( '.kc-add-season-row' ).on( 'click', function() {
                var idx  = 'new_' + newIndex++;
                var $row = $(
                    '<tr data-id="new">' +
                        '<td><input type="text"   name="seasons[' + idx + '][season]"     class="regular-text" placeholder="Nazwa sezonu" required></td>' +
                        '<td><input type="number" name="seasons[' + idx + '][price]"      class="small-text"   placeholder="0.00" min="0" step="0.01" required></td>' +
                        '<td><input type="date"   name="seasons[' + idx + '][date_start]" required></td>' +
                        '<td><input type="date"   name="seasons[' + idx + '][date_end]"   required></td>' +
                        '<td><button type="button" class="button kc-remove-new-row">Usuń</button></td>' +
                    '</tr>'
                );
                $form.find( '.kc-empty-row' ).remove();
                $form.find( 'tbody' ).append( $row );
            } );

            // Usuń nowy (niezapisany) wiersz z DOM
            $form.on( 'click', '.kc-remove-new-row', function() {
                $( this ).closest( 'tr' ).remove();
            } );

            // Zapis formularza (UPDATE istniejących + INSERT nowych)
            $form.on( 'submit', function( e ) {
                e.preventDefault();
                var postdata = $form.serialize()
                    + '&action=pst_rc_admin'
                    + '&param=save_seasons'
                    + '&nonce=' + pst_rc_admin.nonce;

                $.post( pst_rc_admin.ajaxurl, postdata, function( response ) {
                    if ( response && response.success ) {
                        showMsg( $msg, 'Zapisano.', true );
                        // Odśwież stronę, by nowe wiersze otrzymały prawdziwe ID
                        setTimeout( function() { location.reload(); }, 1000 );
                    } else {
                        showMsg( $msg, 'Błąd zapisu.', false );
                    }
                } ).fail(function() {
                    showMsg( $msg, 'Błąd połączenia.', false );
                });
            });
        });

        // Usuń istniejący sezon (przez AJAX)
        $( document ).on( 'click', '.kc-delete-season', function() {
            var id  = $( this ).data( 'id' );
            var $tr = $( this ).closest( 'tr' );

            if ( ! confirm( 'Czy na pewno usunąć ten sezon?' ) ) {
                return;
            }

            $.post( pst_rc_admin.ajaxurl, {
                action : 'pst_rc_admin',
                param  : 'delete_season',
                nonce  : pst_rc_admin.nonce,
                id     : id,
            }, function( response ) {
                if ( response && response.success ) {
                    $tr.remove();
                } else {
                    alert( 'Błąd usuwania sezonu.' );
                }
            } );
        } );

        // ================================================================
        // POJAZDY — dodawanie i usuwanie typów
        // ================================================================
        $( '#kc-add-vehicle-form' ).on( 'submit', function( e ) {
            e.preventDefault();
            var $msg = $( '#kc-vehicle-msg' );

            // Normalizuj slug do lowercase
            var $slug = $( '#kc_vslug' );
            $slug.val( $slug.val().toLowerCase().replace( /[^a-z0-9_-]/g, '' ) );

            var postdata = $( this ).serialize()
                + '&action=pst_rc_admin'
                + '&param=add_vehicle_type'
                + '&nonce=' + pst_rc_admin.nonce;

            $.post( pst_rc_admin.ajaxurl, postdata, function( response ) {
                if ( response && response.success ) {
                    showMsg( $msg, 'Pojazd dodany. Strona zostanie odświeżona…', true );
                    setTimeout( function() { location.reload(); }, 1200 );
                } else {
                    var err = ( response && response.data ) ? response.data : 'Błąd zapisu.';
                    showMsg( $msg, err, false );
                }
            } ).fail(function() {
                showMsg( $msg, 'Błąd połączenia.', false );
            });
        } );

        $( document ).on( 'click', '.kc-delete-vehicle-type', function() {
            var slug  = $( this ).data( 'slug' );
            var label = $( this ).data( 'label' );

            if ( ! confirm( 'Usunąć typ pojazdu "' + label + '"?\nWszystkie sezony i opłaty tego pojazdu zostaną trwale usunięte.' ) ) {
                return;
            }

            $.post( pst_rc_admin.ajaxurl, {
                action : 'pst_rc_admin',
                param  : 'delete_vehicle_type',
                nonce  : pst_rc_admin.nonce,
                slug   : slug,
            }, function( response ) {
                if ( response && response.success ) {
                    location.reload();
                } else {
                    alert( 'Błąd: ' + ( response && response.data ? response.data : 'nieznany błąd' ) );
                }
            } );
        } );

        // ================================================================
        // USTAWIENIA — VAT i opłaty
        // ================================================================
        $( '#kc-settings-form' ).on( 'submit', function( e ) {
            e.preventDefault();
            var $msg     = $( '#kc-settings-msg' );
            var postdata = $( this ).serialize()
                + '&action=pst_rc_admin'
                + '&param=save_settings'
                + '&nonce=' + pst_rc_admin.nonce;

            $.post( pst_rc_admin.ajaxurl, postdata, function( response ) {
                showMsg( $msg, ( response && response.success ) ? 'Ustawienia zapisane.' : 'Błąd zapisu.', response && response.success );
            } ).fail(function() {
                showMsg( $msg, 'Błąd połączenia.', false );
            });
        } );

        // ================================================================
        // KODY RABATOWE
        // ================================================================

        // Zmiana jednostki przy wyborze typu rabatu
        $( '#kc_dtype' ).on( 'change', function() {
            $( '#kc_dvalue_unit' ).text( $( this ).val() === 'percent' ? '%' : 'zł' );
        } );

        // Dodaj kod
        $( '#kc-add-discount-form' ).on( 'submit', function( e ) {
            e.preventDefault();
            var $msg  = $( '#kc-discount-msg' );
            var $code = $( '#kc_dcode' );
            $code.val( $code.val().toUpperCase().trim() );

            var postdata = $( this ).serialize()
                + '&action=pst_rc_admin'
                + '&param=add_discount_code'
                + '&nonce=' + pst_rc_admin.nonce;

            $.post( pst_rc_admin.ajaxurl, postdata, function( response ) {
                if ( response && response.success ) {
                    showMsg( $msg, 'Kod dodany.', true );
                    setTimeout( function() { location.reload(); }, 1000 );
                } else {
                    showMsg( $msg, ( response && response.data ) || 'Błąd zapisu.', false );
                }
            } ).fail(function() {
                showMsg( $msg, 'Błąd połączenia.', false );
            });
        } );

        // Usuń kod
        $( document ).on( 'click', '.kc-delete-discount', function() {
            var code = $( this ).data( 'code' );
            var $tr  = $( this ).closest( 'tr' );

            if ( ! confirm( 'Usunąć kod rabatowy "' + code + '"?' ) ) {
                return;
            }

            $.post( pst_rc_admin.ajaxurl, {
                action : 'pst_rc_admin',
                param  : 'delete_discount_code',
                nonce  : pst_rc_admin.nonce,
                code   : code,
            }, function( response ) {
                if ( response && response.success ) {
                    $tr.remove();
                } else {
                    alert( 'Błąd usuwania kodu.' );
                }
            } );
        } );

        // Aktywuj / dezaktywuj kod
        $( document ).on( 'click', '.kc-toggle-discount', function() {
            var code = $( this ).data( 'code' );
            var $btn = $( this );
            var $tr  = $( this ).closest( 'tr' );

            $.post( pst_rc_admin.ajaxurl, {
                action : 'pst_rc_admin',
                param  : 'toggle_discount_code',
                nonce  : pst_rc_admin.nonce,
                code   : code,
            }, function( response ) {
                if ( response && response.success ) {
                    var active = response.data.active;
                    $btn.text( active ? 'Dezaktywuj' : 'Aktywuj' ).data( 'active', active );
                    $tr.find( '.kc-dc-status' ).html(
                        active
                            ? '<span style="color:green">Aktywny</span>'
                            : '<span style="color:#aaa">Nieaktywny</span>'
                    );
                }
            } );
        } );

        // ================================================================
        // KONTAKT
        // ================================================================
        $( '#kc-contact-form' ).on( 'submit', function( e ) {
            e.preventDefault();
            var $msg     = $( '#kc-contact-msg' );
            var postdata = $( this ).serialize()
                + '&action=pst_rc_admin'
                + '&param=save_contact'
                + '&nonce=' + pst_rc_admin.nonce;

            $.post( pst_rc_admin.ajaxurl, postdata, function( response ) {
                showMsg( $msg, ( response && response.success ) ? 'E-mail zapisany.' : 'Błąd zapisu.', response && response.success );
            } ).fail(function() {
                showMsg( $msg, 'Błąd połączenia.', false );
            });
        } );

    });
})( jQuery );

/* global jQuery, mfdgFront */
( function ( $ ) {
	'use strict';

	var cfg = window.mfdgFront || {};
	var i18n = cfg.i18n || {};

	function isValidEmail( email ) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email );
	}

	function hasCookie( name ) {
		return document.cookie.split( ';' ).some( function ( c ) {
			return c.trim().indexOf( name + '=' ) === 0;
		} );
	}

	function setSessionCookie( name ) {
		document.cookie = name + '=1; path=/; SameSite=Lax';
	}

	function triggerDownload( url, filename ) {
		var a = document.createElement( 'a' );
		a.href = url;
		if ( filename ) {
			a.setAttribute( 'download', filename );
		}
		a.rel = 'noopener';
		document.body.appendChild( a );
		a.click();
		document.body.removeChild( a );
	}

	/* ---------- Modal ---------- */
	var $modal = null;

	function buildModal() {
		if ( $modal ) {
			return $modal;
		}
		var nameField = '';
		if ( cfg.collectName ) {
			nameField =
				'<label class="mfdg-modal__label">' + i18n.nameLabel + '</label>' +
				'<input type="text" class="mfdg-modal__input mfdg-name" autocomplete="name" />';
		}
		var consentField = '';
		if ( cfg.consentEnabled ) {
			consentField =
				'<label class="mfdg-modal__consent"><input type="checkbox" class="mfdg-consent" /> <span>' +
				( cfg.consentText || '' ) +
				'</span></label>';
		}

		var html =
			'<div class="mfdg-modal" role="dialog" aria-modal="true" aria-labelledby="mfdg-modal-title">' +
				'<div class="mfdg-modal__overlay" data-close="1"></div>' +
				'<div class="mfdg-modal__box">' +
					'<button type="button" class="mfdg-modal__close" data-close="1" aria-label="' + i18n.close + '">&times;</button>' +
					'<h3 class="mfdg-modal__title" id="mfdg-modal-title"></h3>' +
					'<div class="mfdg-modal__form">' +
						nameField +
						'<label class="mfdg-modal__label">' + i18n.emailLabel + '</label>' +
						'<input type="email" class="mfdg-modal__input mfdg-email" placeholder="' + i18n.emailPh + '" autocomplete="email" required />' +
						consentField +
						'<p class="mfdg-modal__error" role="alert" aria-live="polite"></p>' +
						'<button type="button" class="mfdg-btn mfdg-modal__submit">' + i18n.submit + '</button>' +
					'</div>' +
					'<div class="mfdg-modal__success" hidden><p class="mfdg-modal__success-msg"></p></div>' +
				'</div>' +
			'</div>';

		$modal = $( html ).appendTo( 'body' );
		return $modal;
	}

	function openModal( data ) {
		var $m = buildModal();
		$m.data( 'payload', data );
		$m.find( '.mfdg-modal__title' ).text( data.title || '' );
		$m.find( '.mfdg-email' ).val( '' );
		$m.find( '.mfdg-name' ).val( '' );
		$m.find( '.mfdg-consent' ).prop( 'checked', false );
		$m.find( '.mfdg-modal__error' ).text( '' );
		$m.find( '.mfdg-modal__form' ).show();
		$m.find( '.mfdg-modal__success' ).attr( 'hidden', true );
		$m.addClass( 'is-open' );
		setTimeout( function () {
			$m.find( '.mfdg-email' ).trigger( 'focus' );
		}, 50 );
	}

	function closeModal() {
		if ( $modal ) {
			$modal.removeClass( 'is-open' );
		}
	}

	function submitLead() {
		var $m = $modal;
		var data = $m.data( 'payload' ) || {};
		var email = $.trim( $m.find( '.mfdg-email' ).val() );
		var name = $.trim( $m.find( '.mfdg-name' ).val() );
		var consent = $m.find( '.mfdg-consent' ).is( ':checked' ) ? 1 : 0;
		var $err = $m.find( '.mfdg-modal__error' );
		var $btn = $m.find( '.mfdg-modal__submit' );

		$err.text( '' );

		if ( ! isValidEmail( email ) ) {
			$err.text( i18n.invalidEmail );
			return;
		}
		if ( cfg.consentEnabled && ! consent ) {
			$err.text( i18n.consentReq );
			return;
		}

		$btn.prop( 'disabled', true ).text( i18n.downloading );

		$.post( cfg.ajaxUrl, {
			action: 'mfdg_submit_lead',
			nonce: cfg.nonce,
			email: email,
			name: name,
			consent: consent,
			file_id: data.fileId
		} )
			.done( function ( res ) {
				if ( res && res.success ) {
					if ( cfg.rememberVisitor ) {
						setSessionCookie( 'mfdg_lead' );
					}
					$m.find( '.mfdg-modal__form' ).hide();
					$m.find( '.mfdg-modal__success-msg' ).text( res.data.message || cfg.successMessage );
					$m.find( '.mfdg-modal__success' ).removeAttr( 'hidden' );
					triggerDownload( res.data.file_url, res.data.filename );
					setTimeout( closeModal, 2500 );
				} else {
					$err.text( ( res && res.data && res.data.message ) || i18n.error );
				}
			} )
			.fail( function () {
				$err.text( i18n.error );
			} )
			.always( function () {
				$btn.prop( 'disabled', false ).text( i18n.submit );
			} );
	}

	/* ---------- Events ---------- */
	$( document ).on( 'click', '.mfdg-open-modal', function ( e ) {
		e.preventDefault();
		var $btn = $( this );
		var payload = {
			fileId: $btn.data( 'file-id' ),
			url: $btn.data( 'file-url' ),
			title: $btn.data( 'file-title' ),
			category: $btn.data( 'category' ),
			filename: $btn.data( 'filename' )
		};

		// Remembered this session? Download directly.
		if ( cfg.rememberVisitor && hasCookie( 'mfdg_lead' ) ) {
			triggerDownload( payload.url, payload.filename );
			return;
		}
		openModal( payload );
	} );

	$( document ).on( 'click', '.mfdg-modal [data-close]', closeModal );
	$( document ).on( 'click', '.mfdg-modal__submit', submitLead );
	$( document ).on( 'keydown', function ( e ) {
		if ( 27 === e.keyCode ) {
			closeModal();
		}
	} );
	$( document ).on( 'keydown', '.mfdg-email, .mfdg-name', function ( e ) {
		if ( 13 === e.keyCode ) {
			e.preventDefault();
			submitLead();
		}
	} );

	// Track direct downloads (fire and forget).
	$( document ).on( 'click', '.mfdg-direct', function () {
		var id = $( this ).data( 'file-id' );
		if ( id ) {
			$.post( cfg.ajaxUrl, { action: 'mfdg_track', nonce: cfg.nonce, file_id: id } );
		}
	} );

	/* ---------- AJAX category filter ---------- */
	$( document ).on( 'click', '.mfdg-filter__btn', function ( e ) {
		var $btn = $( this );
		var $wrap = $btn.closest( '.mfdg--grid-wrap' );
		var $grid = $wrap.find( '.mfdg-grid' );

		if ( ! $grid.length ) {
			return; // Let the link navigate as fallback.
		}
		e.preventDefault();

		var cat = $btn.data( 'cat' ) || '';
		$btn.closest( '.mfdg-filter' ).find( '.mfdg-filter__btn' ).removeClass( 'is-active' );
		$btn.addClass( 'is-active' );
		$grid.addClass( 'is-loading' );

		$.post( cfg.ajaxUrl, {
			action: 'mfdg_filter',
			nonce: cfg.nonce,
			category: cat,
			columns: $grid.data( 'columns' ) || 3
		} )
			.done( function ( res ) {
				if ( res && res.success ) {
					$grid.html( res.data.html );
				}
			} )
			.always( function () {
				$grid.removeClass( 'is-loading' );
			} );

		// Update the URL without reloading (shareable).
		if ( window.history && window.history.replaceState ) {
			var url = new URL( window.location.href );
			if ( cat ) {
				url.searchParams.set( 'mfdg_cat', cat );
			} else {
				url.searchParams.delete( 'mfdg_cat' );
			}
			window.history.replaceState( {}, '', url.toString() );
		}
	} );
} )( jQuery );

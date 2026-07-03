/* global jQuery, wp, mfdgAdmin */
( function ( $ ) {
	'use strict';

	var frame = null;

	$( document ).on( 'click', '.mfdg-select-file', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.mfdg-file-picker' );

		if ( frame ) {
			frame.open();
			bindSelect( $wrap );
			return;
		}

		frame = wp.media( {
			title: mfdgAdmin.chooseFile,
			button: { text: mfdgAdmin.useThisFile },
			multiple: false
		} );

		bindSelect( $wrap );
		frame.open();
	} );

	function bindSelect( $wrap ) {
		frame.off( 'select' ).on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			$wrap.find( '#mfdg_file_id' ).val( attachment.id );
			$wrap
				.find( '.mfdg-file-preview' )
				.html( '<a href="' + attachment.url + '" target="_blank" rel="noopener">' + attachment.filename + '</a>' );
			$wrap.find( '.mfdg-remove-file' ).show();
		} );
	}

	$( document ).on( 'click', '.mfdg-remove-file', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.mfdg-file-picker' );
		$wrap.find( '#mfdg_file_id' ).val( '' );
		$wrap.find( '.mfdg-file-preview' ).empty();
		$( this ).hide();
	} );
} )( jQuery );

( function( $ ) {

	let isRunning = false;

	const installSampleCourse = function ( e ) {
		e.preventDefault();
		const $button = $( this );
		if ( isRunning ) {
			return;
		}

		$button.addClass( 'disabled' ).html( $button.data( 'installing-text' ) );
		isRunning = true;

		$.ajax( {
			url: crlmsAdminTools.ajax_url,
			type: 'POST',
			data: {
				action: 'install_sample_data',
				nonce: crlmsAdminTools.nonce
			},
			success( response ) {
				$button.removeClass( 'disabled' ).html( $button.data( 'text' ) );
				isRunning = false;
				$( response ).insertBefore( $button.parent() );
			},
			error() {
				$button.removeClass( 'disabled' ).html( $button.data( 'text' ) );
				isRunning = false;
				$( response ).insertBefore( $button.parent() );
			},
		} );
	};


	const deleteSampleCourse = function (e) {
		e.preventDefault();

		$.ajax( {
			url: crlmsAdminTools.ajax_url,
			type: 'POST',
			data: {
				action: 'delete_sample_data',
				nonce: crlmsAdminTools.nonce
			},
			success( response ) {
				$button.removeClass( 'disabled' ).html( $button.data( 'text' ) );
				isRunning = false;
				$( response ).insertBefore( $button.parent() );
			},
			error() {
				$button.removeClass( 'disabled' ).html( $button.data( 'text' ) );
				isRunning = false;
				$( response ).insertBefore( $button.parent() );
			},
		} );
	}

	$( function() {
		$( document ).on( 'click', '.crlms-tools-sample-data-install', installSampleCourse )
			.on( 'click', '.crlms-tools-sample-data-delete', deleteSampleCourse );
	} );
}( jQuery ) );

(function($){

	$(function() {
		var AdminSettings = {
			init: function() {
				this.ajaxPageSearch();
				this.currencySearch();
			},
			ajaxPageSearch: function () {
				$( '.crlms-page-search' ).each( function() {
					var select2_args = {
						allowClear:  true,
						placeholder: $( this ).data( 'placeholder' ),
						minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
						escapeMarkup: function( m ) {
							return m;
						},
						ajax: {
							url:         crlms_settings_params.ajax_url,
							dataType:    'json',
							delay:       250,
							data:        function( params ) {
								return {
									term         : params.term,
									action       : 'creator_lms_search_pages',
									security     : crlms_settings_params.search_pages_nonce,
									exclude      : $( this ).data( 'exclude' ),
									post_status  : $( this ).data( 'post_status' ),
									limit        : $( this ).data( 'limit' ),
								};
							},
							processResults: function( data ) {
								var terms = [];
								if ( data ) {
									$.each( data, function( id, text ) {
										terms.push( { id: id, text: text } );
									} );
								}
								return {
									results: terms
								};
							},
							cache: true
						}
					};
					$( this ).select2( select2_args );
				});
			},
			currencySearch: function() {
				$( '.crlms-select' ).select2();
			}

		};

		AdminSettings.init();
	});

})(jQuery);

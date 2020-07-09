
(function($){

	AstraPortfolioAPI = {

		_api_url  : astraPortfolioApi.ApiURL,

		/**
		 * API Request
		 */
		_api_request: function( args ) {

			$.ajax({
				dataType: 'json',
				url: AstraPortfolioAPI._api_url + args.slug,
				cache: false
			})
			.done(function( items, status, XHR ) {

				if( 'success' === status && XHR.getResponseHeader('x-wp-total') ) {

					var data = {
						args 		: args,
						items 		: items,
						items_count	: XHR.getResponseHeader('x-wp-total') || 0,
					};

					if( 'undefined' !== args.trigger && '' !== args.trigger ) {
						$(document).trigger( args.trigger, [data] );
					}

				} else {
					$(document).trigger( 'astra-portfolio-api-request-error' );
				}

			})
			.fail(function( jqXHR, textStatus ) {

				$(document).trigger( 'astra-portfolio-api-request-fail', [args, jqXHR, textStatus] );

			})
			.always(function() {

				$(document).trigger( 'astra-portfolio-api-request-always', [args] );

			});

		},

	};

})(jQuery);
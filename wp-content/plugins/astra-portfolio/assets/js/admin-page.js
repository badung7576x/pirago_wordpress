(function($){

	AstraPortfolioAdminPage = {

		init: function()
		{
			this._show_status();
		},
		
		/**
		 * Binds events for the Astra Portfolio.
		 *
		 * @since 1.7.0
		 * @access private
		 * @method _bind
		 */
		_show_status: function()
		{
			setInterval(function(){
				if( $('.astra-sites-batch-processing').length ) {
					$.ajax({
						url: ajaxurl,
						data: {
							action: 'astra_portfolio_batch_status'
						},
					})
					.done(function (result) {
						if( result.success ) {
							$('.astra-sites-batch-processing').text( result.data );
						} else {
							$('.astra-portfolio-notice').remove();
							$('.astra-sites-batch-processing')
								.attr( 'href', AstraPortfolioAdminPageVars.admin_page_url )
								.text('Successfully Imported!')
								.removeClass( 'updating-message is-disabled astra-sites-batch-processing' );
						}
					})
					.fail(function (e) {
						console.log("error");
						console.log( e );
					});
				}
			}, 1000);
		}

	};

	/**
	 * Initialize AstraPortfolioAdminPage
	 */
	$(function(){
		AstraPortfolioAdminPage.init();
	});

})(jQuery);
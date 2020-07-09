(function($){

	AstraPortfolioNotice = {

		init: function()
		{
			this._bind();
		},
		
		/**
		 * Binds events for the Astra Portfolio.
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _bind
		 */
		_bind: function()
		{
			$( document ).on('click', '.astra-portfolio-notice.is-dismissible .notice-dismiss', AstraPortfolioNotice._dismissNotice );
		},

		/**
		 * Dismiss Notice.
		 */
		_dismissNotice: function( event )
		{
			event.preventDefault();

			var $id   = $( this ).parents('.astra-portfolio-notice').attr( 'id' ) || '';
			var $time = $( this ).parents('.astra-portfolio-notice').attr( 'dismissible-time' ) || '';
			var $meta = $( this ).parents('.astra-portfolio-notice').attr( 'dismissible-meta' ) || '';

			$.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action 	: 'astra-portfolio-notices',
					id 		: $id,
					meta 	: $meta,
					time 	: $time,
					security : AstraPortfolioNotices._nonce,
				},
			});
		}

	};

	/**
	 * Initialize AstraPortfolioNotice
	 */
	$(function(){
		AstraPortfolioNotice.init();
	});

})(jQuery);
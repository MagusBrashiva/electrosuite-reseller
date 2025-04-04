(function($) {
	$(document).ready( function(){
		electrosuite_reseller_transifex_translations();
		electrosuite_reseller_transifex_contributors();
	});

	function electrosuite_reseller_transifex_translations() {

		if ( 0 === $('.transifex-stats').length ) {
			return;
		}

		$('.transifex-stats').each( function(){

			var container = $(this).addClass('loading');

			$.ajax({
				url: electrosuite_reseller_admin_params.ajaxurl,
				data: {
					action: 'transifex_project_stats',
					project_slug: container.attr('data-project-slug'),
					resource_slug: container.attr('data-resource-slug'),
				},
				type: 'post',
				dataType: 'html',
				success: function( html ){
					if ( html && '0' !== html ) {
						container.html( html );
					}
				},
				complete: function() {
					container.removeClass('loading');
				}
			});
		});
	}

	function electrosuite_reseller_transifex_contributors() {

		if ( 0 === $('.transifex-stats-contributors').length ) {
			return;
		}

		$('.transifex-stats-contributors').each( function(){

			var container = $(this).addClass('loading');

			$.ajax({
				url: electrosuite_reseller_admin_params.ajaxurl,
				data: {
					action: 'transifex_contributor_stats',
					project_slug: container.attr('data-project-slug')
				},
				type: 'post',
				dataType: 'html',
				success: function( html ){
					if ( html && '0' !== html ) {
						container.html( html );
					}
				},
				complete: function() {
					container.removeClass('loading');
				}
			});
		});
	}

})(jQuery);
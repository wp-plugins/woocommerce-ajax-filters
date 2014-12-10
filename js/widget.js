(function ($){
	$(document).ready(function (){
		
		var berocket_aapf_widget_product_filters = [];
		function updateProducts( $el ){
			$('ul.products').addClass('hide_products').append('<div class="berocket_aapf_widget_loading" />');
			
			if( $el ){
				$li = $el.parent().parent();
				taxonomy = $li.data('taxonomy');
				term_id = $li.data('term_id');
				operator = $li.data('operator');
				
				if( $el.is(':checked') || $el.is(':selected') ){
					berocket_aapf_widget_product_filters[berocket_aapf_widget_product_filters.length] = [taxonomy,term_id,operator];
				}else{
					$(berocket_aapf_widget_product_filters).each(function (i,o){
						if( o[0] == taxonomy && o[1] == term_id ){
							berocket_aapf_widget_product_filters.splice(i, 1);
						}
					});
				}
			}
			
			if( $('.berocket_filter_slider').hasClass('berocket_filter_slider') ){
				var berocket_aapf_widget_product_limits = [];
				$('.berocket_filter_slider').each(function (i,o){
					val1 = $('#'+$(o).data('fields_1')).val();
					val2 = $('#'+$(o).data('fields_2')).val();
					berocket_aapf_widget_product_limits[berocket_aapf_widget_product_limits.length] = [$(o).data('taxonomy'), val1, val2];
				});
			}
			
			if( $('.berocket_filter_price_slider').hasClass('berocket_filter_price_slider') ){
				val1 = $('#'+$('.berocket_filter_price_slider').data('fields_1')).val();
				val2 = $('#'+$('.berocket_filter_price_slider').data('fields_2')).val();
				var berocket_aapf_widget_product_price_limit = [val1, val2];
			}
			
			$.post( the_ajax_script.ajaxurl, { terms: berocket_aapf_widget_product_filters, price: berocket_aapf_widget_product_price_limit, limits: berocket_aapf_widget_product_limits, product_cat: the_ajax_script.product_cat, action: 'berocket_aapf_listener' }, function (data){
				$('ul.products').html(data).removeClass('hide_products');
				$('.berocket_aapf_widget_loading').remove();
			})
		}
		
		$('.berocket_aapf_widget').on("change", "input, select", function(){
			updateProducts( $(this) );
		});
		
		$( ".berocket_filter_slider" ).each(function (i,o){
			$(o).slider({
				range: true,
				min: $(o).data('min')>>0,
				max: $(o).data('max')>>0,
				values: [$(o).data('min')>>0,$(o).data('max')>>0],
				slide: function( event, ui ) {
					$o = $(ui.handle).parents('div.berocket_filter_slider');
					
					$( '#'+$o.data('fields_1') ).val( ui.values[0] );
					$( '#'+$o.data('fields_2') ).val( ui.values[1] );
				},
				stop: function( event, ui ){
					updateProducts( false );
				}
			}); 
		});
		
		$( ".berocket_filter_price_slider" ).each(function (i,o){
			$(o).slider({
				range: true,
				min: $(o).data('min')>>0,
				max: $(o).data('max')>>0,
				values: [$(o).data('min')>>0,$(o).data('max')>>0],
				slide: function( event, ui ) {
					$o = $(ui.handle).parents('div.berocket_filter_price_slider');
					
					$( '#'+$o.data('fields_1') ).val( ui.values[0].toFixed(2) );
					$( '#'+$o.data('fields_2') ).val( ui.values[1].toFixed(2) );
				},
				stop: function( event, ui ){
					updateProducts( false );
				}
			}); 
		});
		
		$(".berocket_aapf_widget_height_control").each(function (i,o){
			$(o).mCustomScrollbar({
				axis: "xy",
				theme: $(o).data('scroll_theme'),
				scrollInertia: 300
			});
		})
		
	});
})(jQuery)
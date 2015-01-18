(function ($){
	$(document).ready(function (){
		
		var berocket_aapf_widget_product_filters = JSON.parse(the_ajax_script.berocket_aapf_widget_product_filters),
            berocket_aapf_widget_product_limits = [],
            berocket_aapf_widget_product_price_limit = [];

		function updateProducts( $el ){
			$(the_ajax_script.products_holder_id).addClass('hide_products').append('<div class="berocket_aapf_widget_loading" />');
			
			if( $el ){
				$li = $el.parent().parent();
                if( $el.is("select") ) $li = $el.find("option:selected");
				taxonomy = $li.data('taxonomy');
				term_id = $li.data('term_id');
				operator = $li.data('operator');

                if( $el.is("select") ){
                    $(berocket_aapf_widget_product_filters).each(function (i, o) {
                        if (o[0] == taxonomy) {
                            berocket_aapf_widget_product_filters.splice(i, 1);
                        }
                    });
                    if( $li.val() )
                        berocket_aapf_widget_product_filters[berocket_aapf_widget_product_filters.length] = [taxonomy, term_id, operator];
                }else {
                    if ($el.is(':checked') || $el.is(':selected')) {
                        berocket_aapf_widget_product_filters[berocket_aapf_widget_product_filters.length] = [taxonomy, term_id, operator];
                    } else {
                        $(berocket_aapf_widget_product_filters).each(function (i, o) {
                            if (o[0] == taxonomy && o[1] == term_id) {
                                berocket_aapf_widget_product_filters.splice(i, 1);
                            }
                        });
                    }
                }
			}

            berocket_aapf_widget_product_limits = [];
            berocket_aapf_widget_product_price_limit = [];

			$t = $('.berocket_filter_slider');
			if( $t.hasClass('berocket_filter_slider') ){
                $t.each(function (i,o){
					val1 = $('#'+$(o).data('fields_1')).val();
					val2 = $('#'+$(o).data('fields_2')).val();
                    if( val1 != $(o).data('min') || val2 != $(o).data('max') ){
                        if( $(o).hasClass('berocket_filter_price_slider') ){
                            berocket_aapf_widget_product_price_limit = [val1, val2];
                        }else{
                            berocket_aapf_widget_product_limits[berocket_aapf_widget_product_limits.length] = [$(o).data('taxonomy'), val1, val2];
                        }
                    }
				});
			}

            args = {
                terms: berocket_aapf_widget_product_filters,
                price: berocket_aapf_widget_product_price_limit,
                limits: berocket_aapf_widget_product_limits,
                product_cat: the_ajax_script.product_cat,
                action: 'berocket_aapf_listener',
                orderby: $('.woocommerce-ordering select.orderby').val()
            };

            if( the_ajax_script.seo_friendly_urls && 'history' in window && 'pushState' in history ) {
                updateLocation(args);
            }

            $.post(the_ajax_script.ajaxurl, args, function (data) {
                $(the_ajax_script.products_holder_id).html(data).removeClass('hide_products');
                $('.berocket_aapf_widget_loading').remove();
            });
		}

        function updateLocation( args ){
            uri_request_array = [];
            uri_request = '';

            if( args.orderby && $('.woocommerce-ordering select.orderby option:first').attr('value') != args.orderby ){
                uri_request_array[uri_request_array.length] = 'order='+args.orderby;
            }
            if( args.product_cat && args.product_cat > 0 ){
                uri_request_array[uri_request_array.length] = 'pcategory='+args.product_cat;
            }
            if( args.price ){
                $price_obj = $('.berocket_filter_price_slider');
                if( args.price[0] && args.price[1] && ( args.price[0] != $price_obj.data('min') || args.price[1] != $price_obj.data('max') ) ){
                    uri_request_array[uri_request_array.length] = 'price='+args.price[0]+'^'+args.price[1];
                }
            }
            if( args.limits ){
                $( args.limits).each(function (i,o){
                    uri_request_array[uri_request_array.length] = o[0].substring(3)+'='+o[1]+'^'+o[2];
                });
            }
            if( args.terms ){
                $( args.terms).each(function (i,o){
                    uri_request_array[uri_request_array.length] = o[0].substring(3)+'='+o[1]+'^'+o[2];
                });
            }

            var uri = '';
            if( uri_request_array.length ){
                $(uri_request_array).each(function (i,o){
                    if( uri_request ) uri_request += "|";
                    uri_request += o;
                });

                uri = the_ajax_script.current_page_url + "?filters=" + uri_request;
            } else {
                uri = the_ajax_script.current_page_url;
            }

            var stateParameters = { BeRocket: "Rules" };
            history.pushState(stateParameters, "BeRocket Rules", uri);
            history.pathname = uri;
        }
		
		$('.berocket_aapf_widget').on("change", "input, select", function(){
			updateProducts( $(this) );
		});
		
		$( ".berocket_filter_slider" ).each(function (i,o){
			$(o).slider({
				range: true,
				min: $(o).data('min')>>0,
				max: $(o).data('max')>>0,
				values: [$(o).data('value1')>>0,$(o).data('value2')>>0],
				slide: function( event, ui ) {
					$o = $(ui.handle).parents('div.berocket_filter_slider');
                    vals = ui.values;
					if( $(o).hasClass('berocket_filter_price_slider') ){
                        vals[0] = vals[0].toFixed(2);
                        vals[1] = vals[1].toFixed(2);
                    }
					$( '#'+$o.data('fields_1') ).val( vals[0] );
					$( '#'+$o.data('fields_2') ).val( vals[1] );
				},
				stop: function(){
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
		});

        // Option to take control over (default) sorting function, make it AJAXy and work with filters
        if( the_ajax_script.control_sorting ) {
            $('.woocommerce-ordering').on('submit', function (event) {
                event.preventDefault();
                updateProducts(false);
            });
        }
		
	});
})(jQuery);
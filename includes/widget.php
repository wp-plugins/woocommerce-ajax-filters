<?php

define('BEROCKETAAPF', 'BeRocket_AAPF_Widget');

/* Widget */
function BeRocket_AAPF_load_widgets() {
	register_widget( 'BeRocket_AAPF_widget' );
}

add_action( 'widgets_init', 'BeRocket_AAPF_load_widgets' );
add_action( 'wp_ajax_nopriv_berocket_aapf_listener', array( 'BeRocket_AAPF_Widget', 'listener' ) );
add_action( 'wp_ajax_berocket_aapf_listener', array( 'BeRocket_AAPF_Widget', 'listener' ) );


/**
 * BeRocket_AAPF_Widget - main filter widget. One filter for any needs
*/
class BeRocket_AAPF_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	function BeRocket_AAPF_Widget() {
        /* Widget settings. */
        $widget_ops = array( 'classname' => 'widget_berocket_aapf', 'description' => 'Add Filters to Products page' );

        /* Widget control settings. */
        $control_ops = array( 'id_base' => 'berocket_aapf_widget' );

        /* Create the widget. */
        $this->WP_Widget( 'berocket_aapf_widget', 'AJAX Product Filters', $widget_ops, $control_ops );
	}

	/**
	 * Show widget to user
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
		if( !is_shop() and !is_product_category() ) return;
		global $wp_query;
        
        wp_register_style( 'berocket_aapf_widget-style', plugins_url( '../css/widget.min.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url( '../js/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js', __FILE__ ), array( 'jquery' ) );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url( '../js/custom-scrollbar/jquery.mCustomScrollbar.min.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'berocket_aapf_widget-script', plugins_url( '../js/widget.min.js', __FILE__ ), array( 'jquery' ) );
		wp_enqueue_script( 'berocket_aapf_widget-hack-script', plugins_url( '../js/mobiles.min.js', __FILE__ ), array( 'jquery' ) );

		$br_options = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
        
        $wp_query_product_cat = '-1';
        if ( @ $wp_query->query['product_cat'] ) {
	        $wp_query_product_cat = explode( "/", $wp_query->query['product_cat'] );
	        $wp_query_product_cat = $wp_query_product_cat[ count( $wp_query_product_cat ) - 1 ];
        }

		if( ! $br_options['products_holder_id'] ) $br_options['products_holder_id'] = 'ul.products';

		wp_localize_script(
			'berocket_aapf_widget-script',
			'the_ajax_script',
			array(
				'ajaxurl'            => admin_url( 'admin-ajax.php' ),
				'product_cat'        => $wp_query_product_cat,
				'products_holder_id' => $br_options['products_holder_id']
			)
		);

		extract( $args );
		extract( $instance );

		$product_cat = @ json_decode( $product_cat );

		if ( $product_cat ) {
			$hide_widget = true;

			$cur_cat = get_term_by( 'slug', $wp_query_product_cat, 'product_cat' );
			$cur_cat_ancestors = get_ancestors( $cur_cat->term_id, 'product_cat' );
			$cur_cat_ancestors[] = $cur_cat->term_id;

			if( $product_cat ) {
				if ( $cat_propagation ) {
					foreach ( $product_cat as $cat ) {
						$cat = get_term_by( 'slug', $cat, 'product_cat' );

						if ( @ in_array( $cat->term_id, $cur_cat_ancestors ) ) {
							$hide_widget = false;
						}
					}
				} else {
					foreach ( $product_cat as $cat ) {
						if ( $cat == $wp_query_product_cat ) {
							$hide_widget = false;
						}
					}
				}
			}

			if ( $hide_widget ) {
				return true;
			}
		}

		$woocommerce_hide_out_of_stock_items = BeRocket_AAPF_Widget::woocommerce_hide_out_of_stock_items();
		$terms = $sort_terms = $price_range = array();

		if( $attribute == 'price' ) {
			$price_range = BeRocket_AAPF_Widget::get_price_range( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );
			if( ! $price_range ) return false;
		}else{
			$my_query = BeRocket_AAPF_Widget::get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );

			if ( $my_query->have_posts() ) {
				while ( $my_query->have_posts() ) {
					$my_query->the_post();
					$t_terms = get_the_terms( $my_query->post->ID, $attribute );
					if( $t_terms ) {
						foreach ( $t_terms as $key => $val ) {
							$terms[ $key ]      = $val;
							$sort_terms[ $key ] = $val->name;
						}
					}
				}
			}

			if ( @ count( $terms ) < 2 ) return false;

			array_multisort( $sort_terms, $terms );
			set_query_var( 'terms', apply_filters( 'berocket_aapf_widget_terms', $terms ) );
		}

		$style = $class = '';
		if( @$height and $height != 'auto' ){
			$style = "style='height: {$height}px; overflow: hidden;'";
			$class = "berocket_aapf_widget_height_control";
		}
		
		if( !$scroll_theme ) $scroll_theme = 'dark';

		set_query_var( 'operator', $operator );
		set_query_var( 'title', apply_filters( 'berocket_aapf_widget_title', $title ) );
		set_query_var( 'class', apply_filters( 'berocket_aapf_widget_class', $class ) );
		set_query_var( 'style', apply_filters( 'berocket_aapf_widget_style', $style ) );
		set_query_var( 'scroll_theme', $scroll_theme );
		set_query_var( 'x', time() );

		// widget title and start tag ( <ul> ) can be found in templates/widget_start.php
		br_get_template_part('widget_start');

		if( $type == 'slider' ){
			$min = $max = false;
			$main_class = 'slider';
			$slider_class = 'berocket_filter_slider';

			if( $attribute == 'price' ){
				if( $price_range ) {
					foreach ( $price_range as $price ) {
						if ( $min === false or $min > (int) $price ) {
							$min = $price;
						}
						if ( $max === false or $max < (int) $price ) {
							$max = $price;
						}
					}
				}
				$id = rand( 0, time() );
				$slider_class = 'berocket_filter_price_slider';
				$main_class .= ' price';
			}else{
				if( $terms ) {
					foreach ( $terms as $term ) {
						if ( $min === false or $min > (int) $term->slug ) {
							$min = $term->slug;
						}
						if ( $max === false or $max < (int) $term->slug ) {
							$max = $term->slug;
						}
					}
				}
				$id = $term->taxonomy;
			}

			set_query_var( 'id', $id );
			set_query_var( 'main_class', $main_class );
			set_query_var( 'slider_class', $slider_class );
			set_query_var( 'min', number_format(floor($min), 2, '.', '') );
			set_query_var( 'max', number_format(ceil($max), 2, '.', '') );
		}

		br_get_template_part( $type );

		br_get_template_part('widget_end');
	}

	public static function woocommerce_hide_out_of_stock_items(){
		$hide = get_option( 'woocommerce_hide_out_of_stock_items', null );

		if ( is_array( $hide ) ) {
			$hide = array_map( 'stripslashes', $hide );
		} elseif ( ! is_null( $hide ) ) {
			$hide = stripslashes( $hide );
		}

		return apply_filters( 'berocket_aapf_hide_out_of_stock_items', $hide );
	}

	public static function get_price_range( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items ){
		$price_range = array();
		$my_query = BeRocket_AAPF_Widget::get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );

		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) {
				$my_query->the_post();
				$meta_values = get_post_meta( $my_query->post->ID, '_price' );
				if ( $meta_values[0] or $woocommerce_hide_out_of_stock_items != 'yes' ) {
					$price_range[] = $meta_values[0];
				}
			}
		}

		if ( @ count( $price_range ) < 2 ) {
			$price_range = false;
		}

		return apply_filters( 'berocket_aapf_get_price_range', $price_range );
	}

	function get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items ) {
		$args = array(
			'post_type'           => 'product',
			'orderby'             => 'category',
			'order'               => 'ASC',
			'ignore_sticky_posts' => 1
		);

		if ( $wp_query_product_cat != - 1 ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'slug',
					'terms'    => array( $wp_query_product_cat ),
				)
			);
		}

		if ( $woocommerce_hide_out_of_stock_items == 'yes' ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_stock_status',
					'value'   => 'instock',
					'compare' => '='
				)
			);
		}

		$args = apply_filters( 'berocket_aapf_get_filter_products_args', $args );

		return new WP_Query( $args );
	}
	/**
	 * Validating and updating widget data
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array - new merged instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		 
		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['attribute'] = strip_tags( $new_instance['attribute'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['product_cat'] = ( $new_instance['product_cat'] ) ? json_encode( $new_instance['product_cat'] ) : '';
		$instance['scroll_theme'] = strip_tags( $new_instance['scroll_theme'] );
		$instance['cat_propagation'] = (int) $new_instance['cat_propagation'];

		if( $new_instance['height'] != 'auto' ) $new_instance['height'] = (float) $new_instance['height'];
		if( !$new_instance['height'] ) $new_instance['height'] = 'auto';
		$instance['height'] = $new_instance['height'];
		
		if( $new_instance['operator'] != 'OR' ) $new_instance['operator'] = 'AND';
		$instance['operator'] = $new_instance['operator'];

		if( $instance['attribute'] == 'price' ) $instance['type'] = 'slider';

		do_action( 'berocket_aapf_admin_update', $instance, $new_instance, $old_instance );

		return apply_filters( 'berocket_aapf_admin_update_instance', $instance );
	}

	/**
	 * Output admin form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {
		wp_enqueue_script( 'berocket_aapf_widget-admin-script', plugins_url('../js/admin.js', __FILE__), array('jquery') );
		wp_register_style( 'berocket_aapf_widget-style', plugins_url('../css/admin.css', __FILE__) );
		wp_enqueue_style( 'berocket_aapf_widget-style' );
	 
		/* Set up some default widget settings. */
		$defaults = array(
			'title' => '',
			'attribute' => '',
			'type' => '',
			'operator' => '',
			'product_cat' => '',
			'height' => 'auto',
			'scroll_theme' => 'dark'
		);

		$defaults = apply_filters( 'berocket_aapf_form_defaults', $defaults );

		$instance = wp_parse_args( (array) $instance, $defaults );
		$attributes = $this->get_attributes();
		$categories = self::get_product_categories( @ json_decode( $instance['product_cat'] ) );

		include AAPF_TEMPLATE_PATH . "admin.php";
	}

	/**
	 * Widget ajax listener
	 */
	public static function listener(){
		$attributes_terms = $tax_query = array();

		add_filter( 'post_class', array( __CLASS__, 'add_product_class' ) );
		
		$attributes = apply_filters( 'berocket_aapf_listener_get_attributes', self::get_attributes() );
		if( @$attributes ) {
			foreach ( $attributes as $k => $v ) {
				$terms = get_terms( array( $k ), $args = array( 'orderby' => 'name', 'order' => 'ASC' ) );
				if( $terms ) {
					foreach ( $terms as $term ) {
						$attributes_terms[ $k ][ $term->term_id ] = $term->slug;
					}
				}
			}
		}
		
		if( @$_POST['terms'] ){
			foreach( $_POST['terms'] as $t ){
				$taxonomies[$t[0]][] = $attributes_terms[$t[0]][$t[1]];
				$taxonomies_operator[$t[0]] = $t[2];
			}
		}

		$taxonomies = apply_filters( 'berocket_aapf_listener_taxonomies', @$taxonomies );
		$taxonomies_operator = apply_filters( 'berocket_aapf_listener_taxonomies_operator', @$taxonomies_operator );

		if( @$taxonomies ){
			$tax_query['relation'] = 'AND';
			if( $taxonomies ) {
				foreach ( $taxonomies as $k => $v ) {
					if ( $taxonomies_operator[ $k ] == 'AND' ) {
						$op = 'AND';
					} else {
						$op = 'IN';
					}

					$tax_query[] = array(
						'taxonomy' => $k,
						'field'    => 'slug',
						'terms'    => $v,
						'operator' => $op
					);
				}
			}
		}
		
		if( @$_POST['product_cat'] and $_POST['product_cat'] != '-1' )
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => strip_tags( $_POST['product_cat'] ),
				'operator' => 'IN'
			);
		
		$args = array( 'tax_query' => $tax_query, 'posts_per_page' => 9, 'post_type' => 'product' );

		$args = apply_filters( 'berocket_aapf_listener_wp_query_args', $args );

		$query = new WP_Query( $args );
		$br_options = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
		$has_products = false;
		
		if( $query->have_posts() ){
			while( $query->have_posts() ){
				$query->the_post();
				$product = new WC_Product($query->post);
				$product_price = $product->get_price();
				
				if( @$_POST['limits'] ){
					foreach( $_POST['limits'] as $l ){
						$attr = $product->get_attribute( $l[0] );
						if( $attr < $l[1] or $attr > $l[2] )
							continue 2;
					}
				}
				
				if( @$_POST['price'] ){
					if( $product_price < $_POST['price'][0] or $product_price > $_POST['price'][1] )
						continue;
				}

				$has_products = true;
				woocommerce_get_template_part( 'content', 'product' );
			}
			wp_reset_postdata();
		}

		if( ! $has_products ){
			echo apply_filters( 'berocket_aapf_listener_no_products_message', "<div class='no-products" . ( ( $br_options['no_products_class'] ) ? ' '.$br_options['no_products_class'] : '' ) . "'>" . $br_options['no_products_message'] . "</div>" );
		}
        die();
	}
	
	public static function get_attributes(){
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attributes = array();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$attributes[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = $tax->attribute_label;
			}
		}
		
		return apply_filters( 'berocket_aapf_get_attributes', $attributes );
	}

	function get_product_categories( $current_product_cat = '' ) {
		$args = array(
			'pad_counts'         => 1,
			'show_counts'        => 1,
			'hierarchical'       => 1,
			'hide_empty'         => 1,
			'show_uncategorized' => 0,
			'orderby'            => 'name',
			'selected'           => $current_product_cat,
			'menu_order'         => false
		);
		
		return get_terms( 'product_cat', apply_filters( 'wc_product_dropdown_categories_get_terms_args', $args ) );
	}

	public static function add_product_class( $classes ) {
		$classes[] = 'product';
		return apply_filters( 'berocket_aapf_add_product_class', $classes );
	}
}
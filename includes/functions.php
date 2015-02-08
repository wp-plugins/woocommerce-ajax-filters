<?php


if( ! function_exists( 'br_get_template_part' ) ){
    /**
     * Public function to get plugin's template
     *
     * @param string $name Template name to search for
     *
     * @return void
     */
    function br_get_template_part( $name = '' ){
		BeRocket_AAPF::br_get_template_part( $name );
	}
}

if( ! function_exists( 'br_aapf_get_attributes' ) ) {
    /**
     * Get all possible woocommerce attribute taxonomies
     *
     * @return mixed|void
     */
    function br_aapf_get_attributes() {
		$attribute_taxonomies = wc_get_attribute_taxonomies();
		$attributes           = array();

		if ( $attribute_taxonomies ) {
			foreach ( $attribute_taxonomies as $tax ) {
				$attributes[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = $tax->attribute_label;
			}
		}

		return apply_filters( 'berocket_aapf_get_attributes', $attributes );
	}
}

if( ! function_exists( 'br_parse_order_by' ) ){
    /**
     * br_aapf_parse_order_by - parsing order by data and saving to $args array that was passed into
     *
     * @param $args
     */
    function br_aapf_parse_order_by( &$args ){
		$orderby = $_GET['orderby'] = $_POST['orderby'];
		$order = "ASK";
		if( @ preg_match( "/-/", $orderby ) ){
			list( $orderby, $order ) = explode( "-", $orderby );
		}

        // needed for woocommerce sorting funtionality
        if( @ $orderby and @ $order ) {

			// Get ordering from query string unless defined
			$orderby = strtolower( $orderby );
			$order   = strtoupper( $order );

			// default - menu_order
			$args['orderby']  = 'menu_order title';
			$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';

			switch ( $orderby ) {
				case 'rand' :
					$args['orderby']  = 'rand';
					break;
				case 'date' :
					$args['orderby']  = 'date';
					$args['order']    = $order == 'ASC' ? 'ASC' : 'DESC';
					break;
				case 'price' :
					$args['orderby']  = 'meta_value_num';
					$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					$args['meta_key'] = '_price';
					break;
				case 'popularity' :
					$args['meta_key'] = 'total_sales';

					// Sorting handled later though a hook
					add_filter( 'posts_clauses', array( 'WC_Query', 'order_by_popularity_post_clauses' ) );
					break;
				case 'rating' :
					// Sorting handled later though a hook
					add_filter( 'posts_clauses', array( 'WC_Query', 'order_by_rating_post_clauses' ) );
					break;
				case 'title' :
					$args['orderby']  = 'title';
					$args['order']    = $order == 'DESC' ? 'DESC' : 'ASC';
					break;
			}
		}
	}
}

if( ! function_exists( 'br_aapf_args_parser' ) ){
    /**
     * br_aapf_args_parser - extend $args based on passed filters
     *
     * @param array $args
     *
     * @return array
     */
    function br_aapf_args_parser( $args = array() ) {
		$attributes_terms = $tax_query = array();
		$attributes       = apply_filters( 'berocket_aapf_listener_get_attributes', br_aapf_get_attributes() );

		if ( @$attributes ) {
			foreach ( $attributes as $k => $v ) {
				$terms = get_terms( array( $k ), $args = array( 'orderby' => 'name', 'order' => 'ASC' ) );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$attributes_terms[ $k ][ $term->term_id ] = $term->slug;
					}
				}
			}
		}

		if ( @$_POST['terms'] ) {
			foreach ( $_POST['terms'] as $t ) {
				$taxonomies[ $t[0] ][]        = $attributes_terms[ $t[0] ][ $t[1] ];
				$taxonomies_operator[ $t[0] ] = $t[2];
			}
		}

		$taxonomies          = apply_filters( 'berocket_aapf_listener_taxonomies', @$taxonomies );
		$taxonomies_operator = apply_filters( 'berocket_aapf_listener_taxonomies_operator', @$taxonomies_operator );

		if ( @$taxonomies ) {
			$tax_query['relation'] = 'AND';
			if ( $taxonomies ) {
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

		if ( @$_POST['product_cat'] and $_POST['product_cat'] != '-1' ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => strip_tags( $_POST['product_cat'] ),
				'operator' => 'IN'
			);
		}

		$args['tax_query'] = $tax_query;
		$args['post_type'] = 'product';

		if ( @ $_POST['orderby'] ) {
			br_aapf_parse_order_by( $args );
		}

		return $args;
	}
}

if( ! function_exists( 'br_aapf_args_converter' ) ) {
    /**
     * convert args-url to normal filters
     */
    function br_aapf_args_converter() {
		if ( preg_match( "~\|~", $_GET['filters'] ) ) {
			$filters = explode( "|", $_GET['filters'] );
		} else {
			$filters[0] = $_GET['filters'];
		}

        foreach ( $filters as $filter ) {
			list( $attribute, $value ) = explode( "=", $filter );

			if ( $attribute == 'price' ) {
				$_POST['price'] = explode( "^", $value );
			} elseif ( $attribute == 'order' ) {
				$_GET['orderby'] = $value;
			} else {
                $term_or_limit = explode( "^", $value );
                if ( $term_or_limit[1] == 'OR' or $term_or_limit[1] == 'AND' ) {
					$_POST['terms'][] = array( "pa_" . $attribute, $term_or_limit[0], $term_or_limit[1] );
				} else {
					$_POST['limits'][] = array( "pa_" . $attribute, $term_or_limit[0], $term_or_limit[1] );
				}
			}
		}
	}
}
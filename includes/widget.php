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

        add_filter( 'berocket_aapf_listener_wp_query_args', 'br_aapf_args_parser' );
    }

    /**
     * Show widget to user
     *
     * @param array $args
     * @param array $instance
     */
    function widget( $args, $instance ) {
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );
        if( @ $br_options['filters_turn_off'] ) return false;

        global $wp_query, $wp;

        wp_register_style( 'berocket_aapf_widget-style', plugins_url( '../css/widget.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url( '../js/scrollbar/Scrollbar.concat.min.js', __FILE__ ), array( 'jquery' ) );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url( '../css/scrollbar/Scrollbar.min.css', __FILE__ ) );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );

        /* main scripts */
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-slider' );
        wp_enqueue_script( 'berocket_aapf_widget-script', plugins_url( '../js/widget.min.js', __FILE__ ), array( 'jquery' ) );
        wp_enqueue_script( 'berocket_aapf_widget-hack-script', plugins_url( '../js/mobiles.min.js', __FILE__ ), array( 'jquery' ) );

        $wp_query_product_cat = '-1';
        if ( @ $wp_query->query['product_cat'] ) {
            $wp_query_product_cat = explode( "/", $wp_query->query['product_cat'] );
            $wp_query_product_cat = $wp_query_product_cat[ count( $wp_query_product_cat ) - 1 ];
        }

        if ( ! $br_options['products_holder_id'] ) $br_options['products_holder_id'] = 'ul.products';

        if ( @ $_POST['terms'] ) {
            $post_temrs = @ json_encode( $_POST['terms'] );
        } else {
            $post_temrs = "[]";
        }

        wp_localize_script(
            'berocket_aapf_widget-script',
            'the_ajax_script',
            array(
                'current_page_url'   => preg_replace( "~paged?/[0-9]+/?~", "", home_url( $wp->request ) ),
                'ajaxurl'            => admin_url( 'admin-ajax.php' ),
                'product_cat'        => $wp_query_product_cat,
                'products_holder_id' => $br_options['products_holder_id'],
                'control_sorting'    => $br_options['control_sorting'],
                'seo_friendly_urls'  => $br_options['seo_friendly_urls'],
                'berocket_aapf_widget_product_filters' => $post_temrs,
                'user_func'          => @ $br_options['user_func'],
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

        if ( $attribute == 'price' ) {
            $price_range = BeRocket_AAPF_Widget::get_price_range( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );
            if ( ! $price_range or count( $price_range ) < 2 ) return false;
        } else {
            $sort_array = array();
            $wc_order_by = wc_attribute_orderby( $attribute );

            if ( @ $br_options['show_all_values'] ) {
                $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute );
            } else {
                $terms = BeRocket_AAPF_Widget::get_attribute_values( $attribute, 'id', true );
            }

            if ( @ count( $terms ) < 2 ) return false;

            if ( $wc_order_by == 'menu_order' ) {
                foreach ( $terms as $term ) {
                    $sort_array[] = get_woocommerce_term_meta( $term->term_id, 'order_' . $attribute );
                }
                array_multisort( $sort_array, $terms );
            } elseif ( $wc_order_by == 'name' or $wc_order_by == 'name_num' ) {
                foreach ( $terms as $term ) {
                    $sort_array[] = $term->name;
                }
                $sort_as = SORT_STRING;
                if ( $wc_order_by == 'name_num' ) {
                    $sort_as = SORT_NUMERIC;
                }
                array_multisort( $sort_array, $terms, SORT_ASC, $sort_as );
            }

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
                $slider_class .= ' berocket_filter_price_slider';
                $main_class .= ' price';

                $min = number_format( floor( $min ), 2, '.', '' );
                $max = number_format( ceil( $max ), 2, '.', '' );
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

            $slider_value1 = $min;
            $slider_value2 = $max;

            if( $attribute == 'price' and @ $_POST['price'] ){
                $slider_value1 = $_POST['price'][0];
                $slider_value2 = $_POST['price'][1];
            }
            if( $attribute != 'price' and @ $_POST['limits'] ){
                foreach( $_POST['limits'] as $p_limit ){
                    if( $p_limit[0] == $attribute ){
                        $slider_value1 = $p_limit[1];
                        $slider_value2 = $p_limit[2];
                    }
                }
            }

            set_query_var( 'slider_value1', $slider_value1 );
            set_query_var( 'slider_value2', $slider_value2 );
            set_query_var( 'filter_slider_id', $id );
            set_query_var( 'main_class', $main_class );
            set_query_var( 'slider_class', $slider_class );
            set_query_var( 'min', $min );
            set_query_var( 'max', $max );
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
        $products = BeRocket_AAPF_Widget::get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items );

        foreach ( $products as $product ) {
            $meta_values = get_post_meta( $product->ID, '_price' );
            if ( $meta_values[0] or $woocommerce_hide_out_of_stock_items != 'yes' ) {
                $price_range[] = $meta_values[0];
            }
        }

        if ( @ count( $price_range ) < 2 ) {
            $price_range = false;
        }

        return apply_filters( 'berocket_aapf_get_price_range', $price_range );
    }

    public static function get_attribute_values( $taxonomy = '', $order_by = 'id', $hide_empty = false ) {
        if ( ! $taxonomy ) return array();

        $args = array(
            'orderby'           => $order_by,
            'order'             => 'ASC',
            'hide_empty'        => $hide_empty,
        );

        return get_terms( $taxonomy, $args );
    }

    public static function get_filter_products( $wp_query_product_cat, $woocommerce_hide_out_of_stock_items ) {
        global $wpdb;

        $from = $group = '';
        $where = " AND ({$wpdb->posts}.post_status = 'publish') ";

        if ( $wp_query_product_cat != - 1 ) {
            $wp_query_product_cat = get_term_by( 'slug', $wp_query_product_cat, 'product_cat', OBJECT );
            $wp_query_product_cat_id = $wp_query_product_cat->term_id;

            $from = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id) ";
            $where = " AND ( {$wpdb->term_relationships}.term_taxonomy_id IN ( {$wp_query_product_cat_id} ) )
                AND ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'private') ";
            $group = " GROUP BY {$wpdb->posts}.ID ";
        }

        if ( $woocommerce_hide_out_of_stock_items == 'yes' ) {
            $from .= " INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) ";
            $where .= " AND ({$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'private')
                AND ( ({$wpdb->postmeta}.meta_key = '_stock_status' AND CAST({$wpdb->postmeta}.meta_value AS CHAR) = 'instock') ) ";
            $group = " GROUP BY {$wpdb->posts}.ID ";
        }

        $results = $wpdb->get_results("
                SELECT SQL_CALC_FOUND_ROWS {$wpdb->posts}.ID
                FROM {$wpdb->posts}
                {$from}
                WHERE 1=1
                AND {$wpdb->posts}.post_type = 'product'
                {$where}
                {$group}
                ORDER BY {$wpdb->posts}.post_date ASC"
            , OBJECT );

        return $results;
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
        $instance['widget_type'] = strip_tags( $new_instance['widget_type'] );
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
            'widget_type' => '',
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
        $attributes = br_aapf_get_attributes();
        $categories = self::get_product_categories( @ json_decode( $instance['product_cat'] ) );

        include AAPF_TEMPLATE_PATH . "admin.php";
    }

    /**
     * Widget ajax listener
     */
    public static function listener(){
        global $wp_query, $wp_rewrite;
        $br_options = apply_filters( 'berocket_aapf_listener_br_options', get_option('br_filters_options') );

        add_filter( 'post_class', array( __CLASS__, 'add_product_class' ) );
        add_filter( 'woocommerce_pagination_args', array( __CLASS__, 'pagination_args' ) );

        $args = apply_filters( 'berocket_aapf_listener_wp_query_args', array() );

        $args['post__in'] = BeRocket_AAPF::limits_filter( array() );
        $args['post__in'] = BeRocket_AAPF::price_filter( $args['post__in'] );
        $args['post_status'] = 'publish';
        $args['post_type'] = 'product';

        $wp_query = new WP_Query( $args );

        // here we get max products to know if current page is not too big
        if ( $wp_rewrite->using_permalinks() and preg_match( "~/page/([0-9]+)~", $_POST['location'], $mathces ) or preg_match( "~paged?=([0-9]+)~", $_POST['location'], $mathces ) ) {
            $args['paged'] = min( $mathces[1], $wp_query->max_num_pages );
            $wp_query = new WP_Query( $args );
        }

        ob_start();

        if ( $wp_query->have_posts() ) {

            do_action('woocommerce_before_shop_loop');

            woocommerce_product_loop_start();
            woocommerce_product_subcategories();

            while ( have_posts() ) {
                the_post();
                wc_get_template_part( 'content', 'product' );
            }

            woocommerce_product_loop_end();

            do_action('woocommerce_after_shop_loop');

            wp_reset_postdata();

            $_RESPONSE['products'] = ob_get_contents();
        } else {
            echo apply_filters( 'berocket_aapf_listener_no_products_message', "<div class='no-products" . ( ( $br_options['no_products_class'] ) ? ' '.$br_options['no_products_class'] : '' ) . "'>" . $br_options['no_products_message'] . "</div>" );

            $_RESPONSE['no_products'] = ob_get_contents();
        }
        ob_end_clean();

        echo json_encode( $_RESPONSE );

        die();
    }

    function get_product_categories( $current_product_cat = '' ) {
        $args = array(
            'pad_counts'         => 1,
            'show_counts'        => 1,
            'hierarchical'       => 1,
            'hide_empty'         => 0,
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

    public static function pagination_args( $args = array() ) {
        $args['base'] = str_replace( 999999999, '%#%', self::get_pagenum_link( 999999999 ) );
        return $args;
    }

    // 99% copy of WordPress' get_pagenum_link.
    public static function get_pagenum_link($pagenum = 1, $escape = true ) {
        global $wp_rewrite;

        $pagenum = (int) $pagenum;

        $request = remove_query_arg( 'paged', preg_replace( "~".home_url()."~", "", $_POST['location'] ) );

        $home_root = parse_url(home_url());
        $home_root = ( isset($home_root['path']) ) ? $home_root['path'] : '';
        $home_root = preg_quote( $home_root, '|' );

        $request = preg_replace('|^'. $home_root . '|i', '', $request);
        $request = preg_replace('|^/+|', '', $request);

        if ( !$wp_rewrite->using_permalinks() ) {
            $base = trailingslashit( get_bloginfo( 'url' ) );

            if ( $pagenum > 1 ) {
                $result = add_query_arg( 'paged', $pagenum, $base . $request );
            } else {
                $result = $base . $request;
            }
        } else {
            $qs_regex = '|\?.*?$|';
            preg_match( $qs_regex, $request, $qs_match );

            if ( !empty( $qs_match[0] ) ) {
                $query_string = $qs_match[0];
                $request = preg_replace( $qs_regex, '', $request );
            } else {
                $query_string = '';
            }

            $request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request);
            $request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request);
            $request = ltrim($request, '/');

            $base = trailingslashit( get_bloginfo( 'url' ) );

            if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
                $base .= $wp_rewrite->index . '/';

            if ( $pagenum > 1 ) {
                $request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
            }

            $result = $base . $request . $query_string;
        }

        /**
         * Filter the page number link for the current request.
         *
         * @since 2.5.0
         *
         * @param string $result The page number link.
         */
        $result = apply_filters( 'get_pagenum_link', $result );

        if ( $escape )
            return esc_url( $result );
        else
            return esc_url_raw( $result );
    }
}
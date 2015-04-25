<?php
/*
	Plugin Name: Advanced AJAX Product Filters for WooCommerce
	Plugin URI: http://berocket.com/wp-plugins/product-filters
	Description: Advanced AJAX Product Filters for WooCommerce
	Version: 1.1.0.2
	Author: BeRocket
	Author URI: http://berocket.com
*/

define( "AAPF_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );

require_once dirname( __FILE__ ).'/includes/widget.php';
require_once dirname( __FILE__ ).'/includes/functions.php';

/**
 * Class BeRocket_AAPF
 */

class BeRocket_AAPF {

	public static $defaults = array(
		"no_products_message" => "There are no products meeting your criteria",
		"no_products_class"   => "",
		"control_sorting"     => "0",
		"products_holder_id"  => "ul.products",
		"filters_turn_off"    => "0",
		"seo_friendly_urls"   => "0"
	);

	function __construct(){
		register_activation_hook(__FILE__, array( __CLASS__, 'br_add_defaults' ) );
		register_uninstall_hook(__FILE__, array( __CLASS__, 'br_delete_plugin_options' ) );

		add_action( 'admin_menu', array( __CLASS__, 'br_add_options_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_br_options' ) );

        add_shortcode( 'br_filters', array( __CLASS__, 'shortcode' ) );

        if( @ $_GET['filters'] and ! @ defined( 'DOING_AJAX' ) ) {
			add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_filters' ) );
		}
	}

	public static function br_add_options_page(){
		add_submenu_page( 'woocommerce', 'Product Filters Settings', 'Product Filters', 'manage_options', 'br-product-filters', array( __CLASS__, 'br_render_form' ) );
	}

	public static function shortcode( $atts = array() ){
        $a = shortcode_atts(
            array(
                'attribute' => '',
                'type' => 'checkbox',
                'operator' => 'OR',
                'title' => '',
                'product_cat' => '',
                'cat_propagation' => '',
                'height' => 'auto',
                'scroll_theme' => 'dark',
            ), $atts );
        if ( ! $a['attribute'] || ! $a['type']  ) return false;

        $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget();
        $BeRocket_AAPF_Widget->widget( array(), $a );
	}

	public static function br_render_form(){
		include AAPF_TEMPLATE_PATH . "admin-settings.php";
	}

	public static function apply_user_filters( $query ){
        if( $query->is_main_query() and
		    ( $query->get( 'post_type' ) == 'product' or $query->get( 'product_cat' ) )
			or
			$query->is_page() && 'page' == get_option( 'show_on_front' ) && $query->queried_object_id == wc_get_page_id('shop')
		){
            br_aapf_args_converter();
			$args = br_aapf_args_parser();

            if( @ $_POST['price'] ){
				list( $_GET['min_price'], $_GET['max_price'] ) = $_POST['price'];
				add_filter( 'loop_shop_post_in', array( 'WC_QUERY', 'price_filter' ) );
			}

			if ( @ $_POST['limits'] ) {
				add_filter( 'loop_shop_post_in', array( __CLASS__, 'limits_filter' ) );
			}

			$args_fields = array( 'meta_key', 'tax_query', 'fields', 'where', 'join', 'meta_query' );
			foreach( $args_fields as $args_field ){
				if( @ $args[$args_field] ){
					$query->set( $args_field, $args[$args_field] );
				}
			}
		}

		return $query;
	}

	public static function limits_filter( $filtered_posts ){
		global $wpdb;

		if ( @ $_POST['limits'] ) {
			$matched_products = array( 0 );

			foreach ( $_POST['limits'] as $v ) {
				$matched_products_query = $wpdb->get_results( $wpdb->prepare("
		            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
					INNER JOIN $wpdb->term_relationships as tr ON ID = tr.object_id
					INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
					INNER JOIN $wpdb->terms as t ON t.term_id = tt.term_id
					WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
					AND tt.taxonomy = %s AND t.slug BETWEEN %d AND %d
				", $v[0], $v[1], $v[2] ), OBJECT_K );

				if ( $matched_products_query ) {
					foreach ( $matched_products_query as $product ) {
						if ( $product->post_type == 'product' )
							$matched_products[] = $product->ID;
						if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
							$matched_products[] = $product->post_parent;
					}
				}
			}

			$matched_products = array_unique( $matched_products );

			// Filter the id's
			if ( sizeof( $filtered_posts ) == 0) {
				$filtered_posts = $matched_products;
			} else {
				$filtered_posts = array_intersect( $filtered_posts, $matched_products );
			}
		}

		return (array) $filtered_posts;
	}

	public static function price_filter( $filtered_posts ){
		global $wpdb;

		if ( @ $_POST['price'] ) {
			$matched_products = array( 0 );
			$min 	= floatval( $_POST['price'][0] );
			$max 	= floatval( $_POST['price'][1] );

			$matched_products_query = apply_filters( 'woocommerce_price_filter_results', $wpdb->get_results( $wpdb->prepare("
	        	SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
				INNER JOIN $wpdb->postmeta ON ID = post_id
				WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish' AND meta_key = %s AND meta_value BETWEEN %d AND %d
			", '_price', $min, $max ), OBJECT_K ), $min, $max );

			if ( $matched_products_query ) {
				foreach ( $matched_products_query as $product ) {
					if ( $product->post_type == 'product' )
						$matched_products[] = $product->ID;
					if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
						$matched_products[] = $product->post_parent;
				}
			}

			// Filter the id's
			if ( sizeof( $filtered_posts ) == 0) {
				$filtered_posts = $matched_products;
			} else {
				$filtered_posts = array_intersect( $filtered_posts, $matched_products );
			}

		}

		return (array) $filtered_posts;
	}

	/**
	* Get template part (for templates like the slider).
	*
	* @access public
	* @param string $name (default: '')
	* @return void
	*/
	public static function br_get_template_part( $name = '' ) {
	    $template = '';

		// Look in your_child_theme/woocommerce-filters/name.php
	    if ( $name ) {
			$template = locate_template( "woocommerce-filters/{$name}.php" );
		}

		// Get default slug-name.php
		if ( ! $template && $name && file_exists( AAPF_TEMPLATE_PATH . "{$name}.php" ) ) {
			$template = AAPF_TEMPLATE_PATH . "{$name}.php";
		}

	    // Allow 3rd party plugin filter template file from their plugin
	    $template = apply_filters( 'br_get_template_part', $template, $name );


	    if ( $template ) {
			load_template( $template, false );
		}
	}

	public static function register_br_options() {
		register_setting( 'br_filters_plugin_options', 'br_filters_options' );
	}

	public static function br_add_defaults(){
		$tmp = get_option('br_filters_options');
		if( @$tmp['chk_default_options_db'] == '1' or ! @is_array( $tmp ) ){
			delete_option( 'br_filters_options' );
			update_option( 'br_filters_options', BeRocket_AAPF::$defaults );
		}
	}

	public static function br_delete_plugin_options(){
		delete_option( 'br_filters_options' );
	}

}

new BeRocket_AAPF;

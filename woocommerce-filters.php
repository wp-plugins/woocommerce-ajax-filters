<?php
/*
	Plugin Name: Advanced AJAX Product Filters for WooCommerce
	Plugin URI: http://berocket.com/wp-plugins/product-filters
	Description: Advanced AJAX Product Filters for WooCommerce
	Version: 1.0.3.7
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

		if( @ $_GET['filters'] and ! @ defined( 'DOING_AJAX' ) ) {
			br_aapf_args_converter();
			add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_filters' ) );
		}
	}

	public static function br_add_options_page(){
		add_submenu_page( 'woocommerce', 'Product Filters Settings', 'Product Filters', 'manage_options', 'br-product-filters', array( __CLASS__, 'br_render_form' ) );
	}

	public static function br_render_form(){
		include AAPF_TEMPLATE_PATH . "admin-settings.php";
	}

	public static function apply_user_filters( $query ){
		if( $query->is_main_query() and ( $query->get( 'post_type' ) == 'product' or $query->get( 'product_cat' ) ) ){
			$args = br_aapf_args_parser();

			if( $_POST['price'] ){
				$_GET['min_price'] = $_POST['price'][0];
				$_GET['max_price'] = $_POST['price'][1];

				add_filter( 'loop_shop_post_in', array( 'WC_QUERY', 'price_filter' ) );
			}

			if( $args['meta_key'] ){
				$query->set( 'meta_key', $args['meta_key'] );
			}
			if( $args['tax_query'] ) {
				$query->set( 'tax_query', $args['tax_query'] );
			}
			if( $args['fields'] ) {
				$query->set( 'fields', $args['fields'] );
			}
			if( $args['where'] ) {
				$query->set( 'where', $args['where'] );
			}
			if( $args['join'] ) {
				$query->set( 'join', $args['join'] );
			}
		}
		return $query;
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
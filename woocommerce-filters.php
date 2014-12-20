<?php
/*
	Plugin Name: Advanced AJAX Product Filters for WooCommerce
	Plugin URI: http://berocket.com/wp-plugins/product-filters
	Description: Advanced AJAX Product Filters for WooCommerce
	Version: 1.0.2
	Author: BeRocket
	Author URI: http://berocket.com
*/

require_once dirname( __FILE__ ).'/includes/widget.php';
require_once dirname( __FILE__ ).'/includes/functions.php';

/**
 * Class BeRocket_AAPF
 * will be added on next release. There are no global options for now so no need in this class
 */

class BeRocket_AAPF {

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
		if ( ! $template && $name && file_exists( plugin_dir_path( __DIR__ ) . "templates/{$name}.php" ) ) {
			$template = plugin_dir_path( __DIR__ ) . "templates/{$name}.php";
		}

	    // Allow 3rd party plugin filter template file from their plugin
	    $template = apply_filters( 'br_get_template_part', $template, $name );


	    if ( $template ) {
			load_template( $template, false );
		}
	}

}
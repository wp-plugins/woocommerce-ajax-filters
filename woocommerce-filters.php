<?php
/**
 * Plugin Name: Advanced AJAX Product Filters for WooCommerce
 * Plugin URI: http://berocket.com/wp-plugins/product-filters
 * Description: Advanced AJAX Product Filters for WooCommerce
 * Version: 1.1.2
 * Author: BeRocket
 * Author URI: http://berocket.com
 */

define( "BeRocket_AJAX_filters_version", '1.1.1' );
define( "BeRocket_AJAX_domain", 'BRaapf' );

define( "AAPF_TEMPLATE_PATH", plugin_dir_path( __FILE__ ) . "templates/" );
define( "AAPF_URL", plugin_dir_url( __FILE__ ) );

require_once dirname( __FILE__ ) . '/includes/widget.php';
require_once dirname( __FILE__ ) . '/includes/functions.php';

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

        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && br_get_woocommerce_version() >= 2.1 ) {
            if ( defined('DOING_AJAX') && DOING_AJAX ) {
                add_action( 'setup_theme', array( __CLASS__, 'WPML_fix' ) );
            }
            add_action( 'admin_menu', array( __CLASS__, 'br_add_options_page' ) );
            add_action( 'admin_init', array( __CLASS__, 'register_br_options' ) );
            add_action( 'init', array( __CLASS__, 'init' ) );

            add_shortcode( 'br_filters', array( __CLASS__, 'shortcode' ) );
            add_filter( 'loop_shop_per_page', array( __CLASS__, 'loop_shop_per_page' ), 99 );

            if( @ $_GET['filters'] and ! @ defined( 'DOING_AJAX' ) ) {
                add_filter( 'pre_get_posts', array( __CLASS__, 'apply_user_filters' ) );
            }

            if( @ $_GET['explode'] == 'explode' ) {
                add_action( 'woocommerce_before_template_part', array( 'BeRocket_AAPF_Widget', 'pre_get_posts'), 999999 );
                add_action( 'wp_footer', array( 'BeRocket_AAPF_Widget', 'end_clean'), 999999 );
                add_action( 'init', array( 'BeRocket_AAPF_Widget', 'start_clean'), 1 );
            }
        } else {
			if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
                add_action( 'admin_notices', array( __CLASS__, 'update_woocommerce' ) );
            } else {
                add_action( 'admin_notices', array( __CLASS__, 'no_woocommerce' ) );
            }
        }
    }

    public static function init() {
        load_plugin_textdomain( BeRocket_AJAX_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

        wp_register_style( 'berocket_aapf_widget-style', plugins_url( 'css/widget.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url( 'js/scrollbar/Scrollbar.concat.min.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url( 'css/scrollbar/Scrollbar.min.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );
    }

    public static function no_woocommerce() {
        ?>
        <div class="error">
            <p><?php _e( 'Activate WooCommerce plugin before', BeRocket_AJAX_domain ) ?></p>
        </div>
        <?php
    }

    public static function update_woocommerce() {
        ?>
        <div class="error">
            <p><?php _e( 'Update WooCommerce plugin', BeRocket_AJAX_domain ) ?></p>
        </div>
        <?php
    }

    public static function br_add_options_page() {
        add_submenu_page( 'woocommerce', __( 'Product Filters Settings', BeRocket_AJAX_domain ), __( 'Product Filters', BeRocket_AJAX_domain ), 'manage_options', 'br-product-filters', array( __CLASS__, 'br_render_form' ) );
    }

    public static function shortcode( $atts = array() ) {
        $a = shortcode_atts(
            array(
                'widget_type'     => 'filter',
                'attribute'       => '',
                'type'            => 'checkbox',
                'filter_type'     => 'attribute',
                'operator'        => 'OR',
                'title'           => '',
                'product_cat'     => null,
                'cat_propagation' => '',
                'height'          => 'auto',
                'scroll_theme'    => 'dark',
            ), $atts
        );

        if ( isset( $a['product_cat'] ) ) {
            $a['product_cat'] = json_encode( explode( "|", $a['product_cat'] ) );
        }

        if ( ! $a['attribute'] || ! $a['type']  ) return false;

        $BeRocket_AAPF_Widget = new BeRocket_AAPF_Widget();
        $BeRocket_AAPF_Widget->widget( array(), $a );
    }

    public static function br_render_form() {
        wp_enqueue_script( 'berocket_aapf_widget-colorpicker', plugins_url( 'js/colpick.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );
        wp_enqueue_script( 'berocket_aapf_widget-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), BeRocket_AJAX_filters_version );

        wp_register_style( 'berocket_aapf_widget-colorpicker-style', plugins_url( 'css/colpick.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_register_style( 'berocket_aapf_widget-admin-style', plugins_url( 'css/admin.css', __FILE__ ), array(), BeRocket_AJAX_filters_version );
        wp_enqueue_style( 'berocket_aapf_widget-colorpicker-style' );
        wp_enqueue_style( 'berocket_aapf_widget-admin-style' );

        include AAPF_TEMPLATE_PATH . "admin-settings.php";
    }

    public static function apply_user_filters( $query ) {
        if( $query->is_main_query() and
            ( is_shop() or $query->get( 'post_type' ) == 'product' or $query->get( 'product_cat' ) )
            or
            $query->is_page() && 'page' == get_option( 'show_on_front' ) && $query->get('page_id') == wc_get_page_id('shop')
        ) {
            br_aapf_args_converter();
            $args = br_aapf_args_parser();

            if ( @ $_POST['price'] ) {
                list( $_GET['min_price'], $_GET['max_price'] ) = $_POST['price'];
                add_filter( 'loop_shop_post_in', array( 'WC_QUERY', 'price_filter' ) );
            }

            if ( @ $_POST['limits'] ) {
                add_filter( 'loop_shop_post_in', array( __CLASS__, 'limits_filter' ) );
            }

            $args_fields = array( 'meta_key', 'tax_query', 'fields', 'where', 'join', 'meta_query' );
            foreach ( $args_fields as $args_field ) {
                if ( @ $args[$args_field] ) {
                    $query->set( $args_field, $args[$args_field] );
                }
            }
        }

        return $query;
    }

    public static function remove_out_of_stock( $filtered_posts ) {
        global $wpdb;
        $matched_products_query = $wpdb->get_results( "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_stock_status' AND meta_value != 'outofstock'", OBJECT_K );
        $matched_products = array( 0 );

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }

        $matched_products = @ array_unique( $matched_products );

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }

        return (array) $filtered_posts;
    }

    public static function remove_hidden( $filtered_posts ) {
        global $wpdb;
        $matched_products_query = $wpdb->get_results( "
            SELECT DISTINCT ID, post_parent, post_type FROM $wpdb->posts
            INNER JOIN $wpdb->postmeta as meta ON ID = meta.post_id
            WHERE post_type IN ( 'product', 'product_variation' ) AND post_status = 'publish'
            AND meta_key = '_visibility' AND meta_value NOT IN ('hidden', 'search')", OBJECT_K );
        $matched_products = array( 0 );

        foreach ( $matched_products_query as $product ) {
            if ( $product->post_type == 'product' )
                $matched_products[] = $product->ID;
            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                $matched_products[] = $product->post_parent;
        }

        $matched_products = @ array_unique( $matched_products );

        if ( sizeof( $filtered_posts ) == 0) {
            $filtered_posts = $matched_products;
        } else {
            $filtered_posts = array_intersect( $filtered_posts, $matched_products );
        }

        return (array) $filtered_posts;
    }

    public static function limits_filter( $filtered_posts ) {
        global $wpdb;

        if ( @ $_POST['limits'] ) {
            $matched_products = false;

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
                    if ( $matched_products === false ) {
                        $matched_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            if ( $product->post_type == 'product' )
                                $matched_products[] = $product->ID;
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) )
                                $matched_products[] = $product->post_parent;
                        }
                    } else {
                        $new_products = array( 0 );
                        foreach ( $matched_products_query as $product ) {
                            if ( $product->post_type == 'product' && in_array($product->ID, $matched_products))
                                $new_products[] = $product->ID;
                            if ( $product->post_parent > 0 && ! in_array( $product->post_parent, $matched_products ) && in_array($product->post_parent, $matched_products))
                                $new_products[] = $product->post_parent;
                        }
                        $matched_products = $new_products;
                    }
                }
            }

            if ( $matched_products === false ) {
                $matched_products = array( 0 );
            }

            $matched_products = @ array_unique( $matched_products );

            // Filter the id's
            if ( sizeof( $filtered_posts ) == 0 ) {
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
            $min     = floatval( $_POST['price'][0] );
            $max     = floatval( $_POST['price'][1] );

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

    public static function br_add_defaults() {
        $tmp = get_option( 'br_filters_options' );
        if ( @ $tmp['chk_default_options_db'] == '1' or ! @ is_array( $tmp ) ){
            delete_option( 'br_filters_options' );
            update_option( 'br_filters_options', BeRocket_AAPF::$defaults );
        }
    }

    public static function br_delete_plugin_options() {
        delete_option( 'br_filters_options' );
    }

    public static function loop_shop_per_page() {
        return get_option( 'posts_per_page' );
    }

    public static function WPML_fix() {
        global $sitepress;
        if ( method_exists( $sitepress, 'switch_lang' ) && isset( $_POST['current_language'] ) && $_POST['current_language'] !== $sitepress->get_default_language() ) {
            $sitepress->switch_lang( $_POST['current_language'], true );
        }
    }

    public static function order_by_popularity_post_clauses( $args ) {
        global $wpdb;
        $args['orderby'] = "$wpdb->postmeta.meta_value+0 DESC, $wpdb->posts.post_date DESC";
        return $args;
    }

    public static function order_by_rating_post_clauses( $args ) {
        global $wpdb;
        $args['fields'] .= ", AVG( $wpdb->commentmeta.meta_value ) as average_rating ";
        $args['where'] .= " AND ( $wpdb->commentmeta.meta_key = 'rating' OR $wpdb->commentmeta.meta_key IS null ) ";
        $args['join'] .= "
            LEFT OUTER JOIN $wpdb->comments ON($wpdb->posts.ID = $wpdb->comments.comment_post_ID)
            LEFT JOIN $wpdb->commentmeta ON($wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id)
            ";
        $args['orderby'] = "average_rating DESC, $wpdb->posts.post_date DESC";
        $args['groupby'] = "$wpdb->posts.ID";
        return $args;
    }
}

new BeRocket_AAPF;
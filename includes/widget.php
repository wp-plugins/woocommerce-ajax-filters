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
        $this->WP_Widget( 'berocket_aapf_widget', 'Product Filters', $widget_ops, $control_ops );
	}

	/**
	 * Show widget to user
	 */
	function widget( $args, $instance ) {
		if( !is_shop() and !is_product_category() ) return;
		global $wp_query;
        
        wp_register_style( 'berocket_aapf_widget-style', plugins_url('../css/widget.css', __FILE__) );
        wp_enqueue_style( 'berocket_aapf_widget-style' );

        /* custom scrollbar */
        wp_enqueue_script( 'berocket_aapf_widget-scroll-script', plugins_url('../js/custom-scrollbar/jquery.mCustomScrollbar.concat.min.js', __FILE__), array('jquery') );
        wp_register_style( 'berocket_aapf_widget-scroll-style', plugins_url('../js/custom-scrollbar/jquery.mCustomScrollbar.min.css', __FILE__) );
        wp_enqueue_style( 'berocket_aapf_widget-scroll-style' );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'berocket_aapf_widget-script', plugins_url('../js/widget.js', __FILE__), array('jquery') );
		wp_enqueue_script( 'berocket_aapf_widget-hack-script', plugins_url('../js/hack.js', __FILE__), array('jquery') );
		wp_register_style( 'berocket_aapf_widget-ui-style', plugins_url('../css/jquery-ui.css', __FILE__) );
		wp_enqueue_style( 'berocket_aapf_widget-ui-style' );
        
        $wp_query_product_cat = '-1';
        if( @$wp_query->query['product_cat'] )
			$wp_query_product_cat = $wp_query->query['product_cat'];
		
		wp_localize_script( 'berocket_aapf_widget-script', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'product_cat' => $wp_query_product_cat ) );
		
		extract( $args );
		extract( $instance );
		
		if( $product_cat and $product_cat != $wp_query->query['product_cat'] ) return ;
		
		$terms = get_terms( array( $attribute ), $args = array( 'orderby' => 'name', 'order' => 'ASC' ) );
		$args = array(
			'post_type' => 'product',
			'orderby' => "category",
			'order' => 'ASC',
			'ignore_sticky_posts'=> 1
		);
		
		$price_range = array();
		
		$my_query = new WP_Query($args);
		if( $my_query->have_posts() ) {
			while ($my_query->have_posts()){
				$my_query->the_post();
				$meta_values = get_post_meta( $my_query->post->ID, '_price' );
				$price_range[] = $meta_values[0];
			}
		}

		$style = $class = '';
		if( @$height and $height != 'auto' ){
			$style = "style='height: {$height}px; overflow: hidden;'";
			$class = "berocket_aapf_widget_height_control";
		}
		
		if( !$scroll_theme ) $scroll_theme = 'dark';
		
		
		echo '<h3 class="widget-title berocket_aapf_widget-title">'.$title.'</h3>';
		echo "<ul class='berocket_aapf_widget {$class}' {$style} data-scroll_theme='{$scroll_theme}'>";
		if( $type == 'checkbox' ){
			foreach( $terms as $term )
				echo "
					<li data-term_id='{$term->term_id}' data-taxonomy='{$term->taxonomy}' data-operator='{$operator}'>
						<span>
							<input type='checkbox' id='radio_{$term->term_id}' /><label for='radio_{$term->term_id}'> {$term->name}</label>
						</span>
					</li>
				";
		}
		if( $type == 'radio' ){
			$x = time();
			foreach( $terms as $term )
				echo "
					<li data-term_id='{$term->term_id}' data-taxonomy='{$term->taxonomy}' data-operator='{$operator}'>
						<span>
							<input type='radio' id='radio_{$term->term_id}' name='radio_{$term->taxonomy}_{$x}' /><label for='radio_{$term->term_id}'> {$term->name}</label>
						</span>
					</li>
				";
		}
		if( $type == 'select' ){
			echo "<li>
					<span>
						<select>
							<option value=''>Any</option>";
				foreach( $terms as $term )
					echo "<option data-term_id='{$term->term_id}' data-taxonomy='{$term->taxonomy}' data-operator='{$operator}'>{$term->name}</option>";
				echo "</select>
					</span>
				</li>";
		}
		if( $type == 'slider' ){
			if( $attribute == 'price' ){
				$min = $max = false;
				foreach( $price_range as $price ){
					if( $min === false or $min > (int) $price ) $min = $price;
					if( $max === false or $max < (int) $price ) $max = $price;
				}
				$ident = rand( 0, time() );
				echo "<li class='slider price'>
						<span class='left'>
							<input type='text' disabled id='text_{$ident}_1' value='".number_format(floor($min), 2, '.', '')."' /><label for='text_{$ident}_1'>
						</span>
						<span class='right'>
							<input type='text' disabled id='text_{$ident}_2' value='".number_format(ceil($max), 2, '.', '')."' /><label for='text_{$ident}_2'>
						</span>
						<span class='slide'>
							<div class='berocket_filter_price_slider' data-taxonomy='{$ident}' data-min='".number_format(floor($min), 2, '.', '')."' data-max='".number_format(ceil($max), 2, '.', '')."' data-fields_1='text_{$ident}_1' data-fields_2='text_{$ident}_2'></div>
						</span>
					</li>";
			}else{
				$min = $max = false;
				foreach( $terms as $term ){
					if( $min === false or $min > (int) $term->slug ) $min = $term->slug;
					if( $max === false or $max < (int) $term->slug ) $max = $term->slug;
				}
				echo "<li class='slider'>
						<span class='left'>
							<input type='text' disabled id='text_{$term->taxonomy}_1' value='{$min}' /><label for='text_{$term->taxonomy}_1'> <span class='units'>in</span></label>
						</span>
						<span class='right'>
							<input type='text' disabled id='text_{$term->taxonomy}_2' value='{$max}' /><label for='text_{$term->taxonomy}_2'> <span class='units'>in</span></label>
						</span>
						<span class='slide'>
							<div class='berocket_filter_slider' data-taxonomy='{$term->taxonomy}' data-curunit='in' data-min='{$min}' data-max='{$max}' data-fields_1='text_{$term->taxonomy}_1' data-fields_2='text_{$term->taxonomy}_2'></div>
						</span>
					</li>";
			}
		}
		echo "</ul>";
	}

	/**
	 * Validating and updating widget data
	 * @return array - new merged instance
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		 
		/* Strip tags (if needed) and update the widget settings. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['attribute'] = strip_tags( $new_instance['attribute'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['product_cat'] = strip_tags( $new_instance['product_cat'] );
		$instance['scroll_theme'] = strip_tags( $new_instance['scroll_theme'] );
		
		if( $new_instance['height'] != 'auto' ) $new_instance['height'] = (float) $new_instance['height'];
		if( !$new_instance['height'] ) $new_instance['height'] = 'auto';
		$instance['height'] = $new_instance['height'];
		
		if( $new_instance['operator'] != 'OR' ) $new_instance['operator'] = 'AND';
		$instance['operator'] = $new_instance['operator'];
		
		return $instance;
	}
	/**
	 * Output admin form
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
		$instance = wp_parse_args( (array) $instance, $defaults );
		
		$attributes = $this->get_attributes();
		
		$categories = self::get_product_categories( $instance['product_cat'] )
		?>
		 
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Filter Title: </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
		<p>
			<label>Attribute:
				<select id="<?php echo $this->get_field_id( 'attribute' ); ?>" name="<?php echo $this->get_field_name( 'attribute' ); ?>" class="berocket_aapf_widget_admin_attribute_select">
					<option <?php if ($instance['attribute'] == 'price') echo 'selected'; ?> value="price">Price</option>
					<?php foreach( $attributes as $k => $v ){ ?>
						<option <?php if ($instance['attribute'] == $k) echo 'selected'; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
					<?php } ?>
				</select>
			</label>
		</p>
		<p>
			<label>Type:
				<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="berocket_aapf_widget_admin_type_select">
					<? if ($instance['attribute'] != 'pa_lengthcm' and $instance['attribute'] != 'pa_widthcm' and $instance['attribute'] != 'price' ){ ?>
						<option <?php if ($instance['type'] == 'checkbox') echo 'selected'; ?> value="checkbox">Checkbox</option>
						<option <?php if ($instance['type'] == 'radio') echo 'selected'; ?> value="radio">Radio</option>
						<!--<option <?php if ($instance['type'] == 'select') echo 'selected'; ?> value="select">Select</option>-->
					<? } ?>
					<option <?php if ($instance['type'] == 'slider') echo 'selected'; ?> value="slider">Slider</option>
				</select>
			</label>
		</p>
		<p <? if ($instance['attribute'] == 'pa_lengthcm' or $instance['attribute'] == 'pa_widthcm' or $instance['attribute'] == 'price' ) echo " style='display: none;'"; ?> >
			<label>Operator:
				<select id="<?php echo $this->get_field_id( 'operator' ); ?>" name="<?php echo $this->get_field_name( 'operator' ); ?>" class="berocket_aapf_widget_admin_operator_select">
					<option <?php if ($instance['operator'] == 'AND') echo 'selected'; ?> value="AND">AND</option>
					<option <?php if ($instance['operator'] == 'OR') echo 'selected'; ?> value="OR">OR</option>
				</select>
			</label>
		</p>
		<p>
			<a href="#" class='berocket_aapf_advanced_settings_pointer'>Advanced Settings</a>
		</p>
		<div class='berocket_aapf_advanced_settings'>
			<p>
				<label>Product Category:
					<select id="<?php echo $this->get_field_id( 'product_cat' ); ?>" name="<?php echo $this->get_field_name( 'product_cat' ); ?>" class="berocket_aapf_widget_admin_product_cat_select">
						<option value="">Select Category</option>
						<?php foreach( $categories as $category ){ ?>
							<option <?php if ($instance['product_cat'] == $category->slug) echo 'selected'; ?> value="<?php echo $category->slug ?>"><?php echo $category->name ?></option>
						<?php } ?>
					</select>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'height' ); ?>">Filter Box Height: </label>
				<input id="<?php echo $this->get_field_id( 'height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo $instance['height']; ?>" class="berocket_aapf_widget_admin_height_input" />px
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'scroll_theme' ); ?>">Scroll Theme: </label>
				<select id="<?php echo $this->get_field_id( 'scroll_theme' ); ?>" name="<?php echo $this->get_field_name( 'scroll_theme' ); ?>" class="berocket_aapf_widget_admin_scroll_theme_select">
					<?php
					$scroll_themes = array("light", "dark", "minimal", "minimal-dark", "light-2", "dark-2", "light-3", "dark-3", "light-thick", "dark-thick", "light-thin",
					"dark-thin", "inset", "inset-dark", "inset-2", "inset-2-dark", "inset-3", "inset-3-dark", "rounded", "rounded-dark", "rounded-dots",
					"rounded-dots-dark", "3d", "3d-dark", "3d-thick", "3d-thick-dark");
					foreach( $scroll_themes as $theme ): ?>
						<option <?php if ($instance['scroll_theme'] == $theme) echo 'selected'; ?>><?php echo $theme; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}

	/**
	 * Widget ajax listener
	 */
	public static function listener(){
		$attributes_terms = $tax_query = array();
		
		$attributes = self::get_attributes();
		if( @$attributes ) {
			foreach ( $attributes as $k => $v ) {
				$terms = get_terms( array( $k ), $args = array( 'orderby' => 'name', 'order' => 'ASC' ) );
				foreach ( $terms as $term ) {
					$attributes_terms[ $k ][ $term->term_id ] = $term->slug;
				}
			}
		}
		
		if( @$_POST['terms'] ){
			foreach( $_POST['terms'] as $t ){
				$taxonomies[$t[0]][] = $attributes_terms[$t[0]][$t[1]];
				$taxonomies_operator[$t[0]] = $t[2];
			}
		}
		
		if( @$taxonomies ){
			$tax_query['relation'] = 'AND';
			foreach( $taxonomies as $k=>$v ){
				if( $taxonomies_operator[$k] == 'AND' ) $op = 'AND';
				else $op = 'IN';
				
				$tax_query[] = array(
					'taxonomy' => $k,
					'field'    => 'slug',
					'terms'    => $v,
					'operator' => $op
				);
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
		
		$query = new WP_Query( $args );
		
		if( $query->have_posts() ){
			while( $query->have_posts() ){
				$query->the_post();
				$post_thumbnail = get_the_post_thumbnail( $query->post->ID, array( 298, 219 ), array( 'title' => @$image_title ) );
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
				
				echo "<li class='post-{$query->post->ID} product type-product status-publish has-post-thumbnail shipping-taxable product-type-simple instock'>
						<div class='top-product-section'>
							<a class='product-category' href='".get_permalink()."'>
								<span class='image-wrapper'>
									".$post_thumbnail."
								</span>
							</a>
							<span class='add-to-cart-button-outer'><span class='add-to-cart-button-inner'><a class='qbutton add-to-cart-button button  product_type_simple' data-product_sku='' data-product_id='{$query->post->ID}' rel='nofollow' href='".get_permalink()."'>Read More</a></span></span>
						</div>
						<a class='product-category' href='".get_permalink()."'>
							<h6>{$query->post->post_title}</h6>";
				$length = $product->get_attribute( 'pa_lengthcm' );
				$width = $product->get_attribute( 'pa_widthcm' );
				if( $length and $width )
					echo "<span class='product-size'>(" . ( round( ( $length * 0.393700787 )/100, 1 )*100 ) . "&Prime; x " . ( round( ( $width * 0.393700787 )/100, 1 )*100 ) . "&Prime;)</span>";
				if( $product_price )
					echo "<span class='price'>". $product->get_price_html() ."</span>";
				echo 
						"</a>
					</li>";
			}
			wp_reset_postdata();
		}else{
			echo "<div class='no-products'>There are no products meeting your criteria</div>";
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
		
		return $attributes;
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
}

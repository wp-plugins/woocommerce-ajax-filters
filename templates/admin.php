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
			<?php if ( $instance['attribute'] != 'price' ){ ?>
				<option <?php if ($instance['type'] == 'checkbox') echo 'selected'; ?> value="checkbox">Checkbox</option>
				<option <?php if ($instance['type'] == 'radio') echo 'selected'; ?> value="radio">Radio</option>
				<option <?php if ($instance['type'] == 'select') echo 'selected'; ?> value="select">Select</option>
			<?php } ?>
			<option <?php if ($instance['type'] == 'slider') echo 'selected'; ?> value="slider">Slider</option>
		</select>
	</label>
</p>
<p <?php if ( $instance['attribute'] == 'price' ) echo " style='display: none;'"; ?> >
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
			<label class="berocket_aapf_advanced_settings_subcategory">
				<input type="checkbox" name="<?php echo $this->get_field_name( 'cat_propagation' ); ?>" <?php if ( $instance['cat_propagation'] ) echo 'checked'; ?> value="1" class="berocket_aapf_widget_admin_height_input" />
				include subcats?
			</label>
		</label>
		<ul class="berocket_aapf_advanced_settings_categories_list">
			<?php
			$p_cat = @json_decode( $instance['product_cat'] );

			foreach( $categories as $category ){
				$selected_category = false;

				if( $p_cat )
					foreach( $p_cat as $cat ){
						if( $cat == $category->slug )
							$selected_category = true;
					}
			?>
			<li>
				<input type="checkbox" name="<?php echo $this->get_field_name( 'product_cat' ); ?>[]" <?php if ( $selected_category ) echo 'checked'; ?> value="<?php echo $category->slug ?>" class="berocket_aapf_widget_admin_height_input" />
				<?php echo $category->name ?>
			</li>
			<?php } ?>
		</ul>
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
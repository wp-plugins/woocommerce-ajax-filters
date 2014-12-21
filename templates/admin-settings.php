<div class="wrap">
	<h4>Advanced AJAX Product Filters for WooCommerce by <a href="http://berocket.com" target="_blank">BeRocket</a> &amp; <a href="http://dholovnia.me" target="_blank">Dima Holovnia</a></h4>
	<form method="post" action="options.php">
		<?php
		settings_fields('br_filters_plugin_options');
		$options = get_option('br_filters_options');
		?>
		<table class="form-table">
			<tr>
				<th scope="row">"No Products" message</th>
				<td>
					<input size="50" name="br_filters_options[no_products_message]" type='text' value='<?php echo @$options['no_products_message']?>'/>
					<br />
					<span style="color:#666666;margin-left:2px;">Text that will be shown if no products found</span>
				</td>
			</tr>
			<tr>
				<th scope="row">"No Products" class</th>
				<td>
					<input name="br_filters_options[no_products_class]" type='text' value='<?php echo @$options['no_products_class']?>'/>
					<br />
					<span style="color:#666666;margin-left:2px;">Add class and use it to style "No Products" box</span>
				</td>
			</tr>
			<tr>
				<th scope="row">Sorting control</th>
				<td>
					<input name="br_filters_options[control_sorting]" type='checkbox' value='1' <?php if( @$options['control_sorting'] ) echo "checked='checked'";?>/>
					<span style="color:#666666;margin-left:2px;">Take control over WooCommerce's sorting selectbox?</span>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>
	</form>
</div>
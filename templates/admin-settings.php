<div class="wrap">
    <form method="post" action="options.php">
        <?php
        settings_fields('br_filters_plugin_options');
        $options = get_option('br_filters_options');
        ?>
        <h2 class="nav-tab-wrapper filter_settings_tabs">
            <a href="#general" class="nav-tab nav-tab-active">General</a>
            <a href="#design" class="nav-tab">Design</a>
            <a href="#javascript" class="nav-tab">JavaScript</a>
            <a href="#customcss" class="nav-tab">Custom CSS</a>
        </h2>
        <div id="general" class="tab-item current">
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
                    <th scope="row">Products selector</th>
                    <td>
                        <input name="br_filters_options[products_holder_id]" type='text' value='<?php echo @$options['products_holder_id']?$options['products_holder_id']:'ul.products'?>'/>
                        <br />
                        <span style="color:#666666;margin-left:2px;">Selector for tag that is holding products. Don't change this if you don't know what it is</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Sorting control</th>
                    <td>
                        <input name="br_filters_options[control_sorting]" type='checkbox' value='1' <?php if( @$options['control_sorting'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">Take control over WooCommerce's sorting selectbox?</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">SEO friendly urls</th>
                    <td>
                        <input name="br_filters_options[seo_friendly_urls]" type='checkbox' value='1' <?php if( @$options['seo_friendly_urls'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">If this option is on url will be changed when filter is selected/changed</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Turn all filters off</th>
                    <td>
                        <input name="br_filters_options[filters_turn_off]" type='checkbox' value='1' <?php if( @$options['filters_turn_off'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">If you want to hide filters without losing current configuration just turn them off</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Show all values</th>
                    <td>
                        <input name="br_filters_options[show_all_values]" type='checkbox' value='1' <?php if( @$options['show_all_values'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">Check if you want to show not used attribute values too</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Jump to first page</th>
                    <td>
                        <input name="br_filters_options[first_page_jump]" type='checkbox' value='1' <?php if( @$options['first_page_jump'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">Check if you want load first page after filters change</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Scroll page to the top</th>
                    <td>
                        <input name="br_filters_options[scroll_shop_top]" type='checkbox' value='1' <?php if( @$options['scroll_shop_top'] ) echo "checked='checked'";?>/>
                        <span style="color:#666666;margin-left:2px;">Check if you want scroll page to the top of shop after filters change</span>
                    </td>
                </tr>
            </table>
        </div>
        <div id="design" class="tab-item">
            <a href="http://berocket.com/product/woocommerce-ajax-products-filter" target="_blank">
                <img src="<?php echo AAPF_URL; ?>images/paid/styler.png" style="max-width: 100%;" />
            </a>
        </div>
        <div id="javascript" class="tab-item">
            <table class="form-table">
                <tr>
                    <th scope="row">Before Update:</th>
                    <td>
                        <textarea style="min-width: 500px; height: 100px;" name="br_filters_options[user_func][before_update]"><?php echo @$options['user_func']['before_update'] ?></textarea>
                        <br />
                        <span style="color:#666666;margin-left:2px;">If you want to add own actions on filter activation, eg: alert('1');</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">On Update:</th>
                    <td>
                        <textarea style="min-width: 500px; height: 100px;" name="br_filters_options[user_func][on_update]"><?php echo @$options['user_func']['on_update'] ?></textarea>
                        <br />
                        <span style="color:#666666;margin-left:2px;">If you want to add own actions right on products update. You can manipulate data here, try: data.products = 'Ha!';</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">After Update:</th>
                    <td>
                        <textarea style="min-width: 500px; height: 100px;" name="br_filters_options[user_func][after_update]"><?php echo @$options['user_func']['after_update'] ?></textarea>
                        <br />
                        <span style="color:#666666;margin-left:2px;">If you want to add own actions after products updated, eg: alert('1');</span>
                    </td>
                </tr>
            </table>
        </div>
        <div id="customcss" class="tab-item">
            <a href="http://berocket.com/product/woocommerce-ajax-products-filter" target="_blank">
                <img src="<?php echo AAPF_URL; ?>images/paid/custom_css.png" style="max-width: 100%;" />
            </a>
        </div>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
    <h3>Receive more features and control with Paid version of the plugin:</h3>
    <ul>
        <li><b>- Filter by Attribute, Tag and Custom Taxonomy</b></li>
        <li><b>- Customize filters look through admin</b></li>
        <li><b>- Filters can be collapsed, option to collapse filter on start</b></li>
        <li><b>- Price Filter Custom Min and Max values</b></li>
        <li><b>- Tag Cloud for Tag filter</b></li>
        <li><b>- Add custom CSS on admin settings page</b></li>
        <li><b>- Slider can use strings as a value</b></li>
        <li><b>- And More...</b></li>
    </ul>
    <h4>Support the plugin by purchasing paid version. This will provide faster growth, better support and much more functionality for the plugin!</h4>
    <h4>Both <a href="https://wordpress.org/plugins/woocommerce-ajax-filters/" target="_blank">Free</a> and <a href="http://berocket.com/product/woocommerce-ajax-products-filter" target="_blank">Paid</a> versions of WooCommerce AJAX Product Filters developed by <a href="http://berocket.com" target="_blank">BeRocket</a></h4>
</div>
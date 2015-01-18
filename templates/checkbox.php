<?php
/**
* The template for displaying checkbox filters
*
* Override this template by copying it to yourtheme/woocommerce-filters/checkbox.php
*
* @author 	BeRocket
* @package 	WooCommerce-Filters/Templates
* @version  1.0.1
*/
?>
<?php foreach( $terms as $term ):?>
<li data-term_id='<?=$term->term_id?>' data-taxonomy='<?=$term->taxonomy?>' data-operator='<?=$operator?>'>
	<span>
		<input type='checkbox' id='radio_<?=$term->term_id?>'
			<?php
				if( $_POST['terms'] ){
					foreach( $_POST['terms'] as $p_term ){
						if( $p_term[0] == $term->taxonomy and $term->term_id == $p_term[1] ){
							echo ' checked="checked"';
							break;
						}
					}
				}
			?> /><label for='radio_<?=$term->term_id?>'> <?=$term->name?></label>
	</span>
</li>
<?php endforeach; ?>
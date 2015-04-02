<?php foreach( $terms as $term ): ?>
<li>
	<span>
		<input class="<?=$uo['class']['checkbox_radio']?>" type='radio' id='radio_<?=$term->term_id?>'
               name='radio_<?=$term->taxonomy?>_<?=$x?>' data-term_id='<?=$term->term_id?>'
               data-taxonomy='<?=$term->taxonomy?>' data-operator='<?=$operator?>'
			<?php
			if( @ $_POST['terms'] ){
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
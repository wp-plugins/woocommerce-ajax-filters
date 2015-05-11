<?php foreach( $terms as $term ): ?>
<li>
	<span>
		<input class="<?php echo $uo['class']['checkbox_radio'] ?>" type='radio' id='radio_<?php echo $term->term_id ?>'
               name='radio_<?php echo $term->taxonomy ?>_<?php echo $x ?>' data-term_id='<?php echo $term->term_id ?>'
               data-taxonomy='<?php echo $term->taxonomy ?>' data-operator='<?php echo $operator ?>'
			<?php
			if( @ $_POST['terms'] ){
				foreach( $_POST['terms'] as $p_term ){
					if( $p_term[0] == $term->taxonomy and $term->term_id == $p_term[1] ){
						echo ' checked="checked"';
						break;
					}
				}
			}
			?> /><label for='radio_<?php echo $term->term_id ?>'> <?php echo $term->name ?></label>
	</span>
</li>
<?php endforeach; ?>
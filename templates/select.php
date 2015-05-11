<li>
	<span>
		<select class="<?php echo $uo['class']['selectbox'] ?>">
			<option data-taxonomy='<?php echo $terms[0]->taxonomy ?>' value=''>Any</option>
			<?php foreach( $terms as $term ): ?>
			<option data-term_id='<?php echo $term->term_id ?>' data-taxonomy='<?php echo $term->taxonomy ?>'
                    data-operator='<?php echo $operator ?>'
					<?php
					if( @ $_POST['terms'] ){
						foreach( $_POST['terms'] as $p_term ){
							if( $p_term[0] == $term->taxonomy and $term->term_id == $p_term[1] ){
								echo ' selected="selected"';
								break;
							}
						}
					}
					?>
			        ><?php echo $term->name ?></option>
			<?php endforeach; ?>
		</select>
	</span>
</li>
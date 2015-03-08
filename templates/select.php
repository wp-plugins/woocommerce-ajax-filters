<li>
	<span>
		<select>
			<option data-taxonomy='<?=$terms[0]->taxonomy?>' value=''>Any</option>
			<?php foreach( $terms as $term ): ?>
			<option data-term_id='<?=$term->term_id?>' data-taxonomy='<?=$term->taxonomy?>' data-operator='<?=$operator?>'
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
			        ><?=$term->name?></option>
			<?php endforeach; ?>
		</select>
	</span>
</li>
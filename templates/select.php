<li>
	<span>
		<select>
			<option value=''>Any</option>";
			<?php foreach( $terms as $term ): ?>
			<option data-term_id='<?=$term->term_id?>' data-taxonomy='<?=$term->taxonomy?>' data-operator='<?=$operator?>'><?=$term->name?></option>
			<?php endforeach; ?>
		</select>
	</span>
</li>
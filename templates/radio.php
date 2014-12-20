<?php foreach( $terms as $term ): ?>
<li data-term_id='<?=$term->term_id?>' data-taxonomy='<?=$term->taxonomy?>' data-operator='<?=$operator?>'>
	<span>
		<input type='radio' id='radio_<?=$term->term_id?>' name='radio_<?=$term->taxonomy?>_<?=$x?>' /><label for='radio_<?=$term->term_id?>'> <?=$term->name?></label>
	</span>
</li>
<?php endforeach; ?>
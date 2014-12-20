<li class='<?=$main_class?>'>
	<span class='left'>
		<input type='text' disabled id='text_<?=$id?>_1' value='<?=$min?>' /><label for='text_<?=$id?>_1'>
	</span>
	<span class='right'>
		<input type='text' disabled id='text_<?=$id?>_2' value='<?=$max?>' /><label for='text_<?=$id?>_2'>
	</span>
	<span class='slide'>
		<div class='<?=$slider_class?>' data-taxonomy='<?=$id?>' data-min='<?=$min?>' data-max='<?=$max?>' data-fields_1='text_<?=$id?>_1' data-fields_2='text_<?=$id?>_2'></div>
	</span>
</li>
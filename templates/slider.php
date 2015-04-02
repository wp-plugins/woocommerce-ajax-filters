<?php $unique = rand( 0, time() ); ?>
<li class='<?=$main_class?>'>
	<span class='left'>
		<input type='text' disabled id='text_<?=$filter_slider_id . $unique?>_1' value='<?=$slider_value1?>'/>
	</span>
	<span class='right'>
		<input type='text' disabled id='text_<?=$filter_slider_id . $unique?>_2' value='<?=$slider_value2?>'/>
	</span>
	<span class='slide <?=$uo['class']['slider']?>'>
		<div class='<?=$slider_class?>' data-taxonomy='<?=$filter_slider_id?>' data-min='<?=$min?>' data-max='<?=$max?>' data-value1='<?=$slider_value1?>' data-value2='<?=$slider_value2?>' data-fields_1='text_<?=$filter_slider_id . $unique?>_1' data-fields_2='text_<?=$filter_slider_id . $unique?>_2'></div>
	</span>
</li>
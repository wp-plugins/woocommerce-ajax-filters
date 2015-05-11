<?php $unique = rand( 0, time() ); ?>
<li class='<?php echo $main_class ?>'>
	<span class='left'>
		<input type='text' disabled id='text_<?php echo $filter_slider_id . $unique ?>_1' value='<?php echo $slider_value1 ?>'/>
	</span>
	<span class='right'>
		<input type='text' disabled id='text_<?php echo $filter_slider_id . $unique ?>_2' value='<?php echo $slider_value2 ?>'/>
	</span>
	<span class='slide <?php echo $uo['class']['slider'] ?>'>
		<div class='<?php echo $slider_class ?>' data-taxonomy='<?php echo $filter_slider_id ?>' data-min='<?php echo $min ?>'
             data-max='<?php echo $max ?>' data-value1='<?php echo $slider_value1 ?>' data-value2='<?php echo $slider_value2 ?>'
             data-fields_1='text_<?php echo $filter_slider_id . $unique ?>_1'
             data-fields_2='text_<?php echo $filter_slider_id . $unique ?>_2'></div>
	</span>
</li>
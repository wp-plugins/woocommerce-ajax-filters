<?php 
$random_name = rand();
$hiden_value = false;
if ( $terms ):
    foreach( $terms as $term ): 
        $selected = false;
        if( @ $_POST['terms'] ){
            foreach( $_POST['terms'] as $p_term ){
                if( @ $p_term[0] == $term->taxonomy and $term->term_id == @ $p_term[1] ){
                    $selected = true;
                    break;
                }
            }
        }
        ?>
        <li class="<?php if( @ $hide_o_value && isset($term->count) && $term->count == 0 ) { echo 'berocket_hide_o_value '; $hiden_value = true; }  if( @ $hide_sel_value && $selected ) { echo 'berocket_hide_sel_value'; $hiden_value = true; } ?>">
            <span>
                <input class="<?php echo @ $uo['class']['checkbox_radio'] ?> radio_<?php echo $term->term_id?>" type='radio' id='radio_<?php echo $term->term_id?>_<?php echo $random_name ?>'
                    name='radio_<?php echo $term->taxonomy ?>_<?php echo $x ?>_<?php echo $random_name ?>'
                    data-term_id='<?php echo $term->term_id ?>' data-taxonomy='<?php echo $term->taxonomy ?>' data-operator='<?php echo $operator ?>'
                    <?php
                    if( @ $_POST['terms'] ){
                        foreach( $_POST['terms'] as $p_term ){
                            if( $p_term[0] == $term->taxonomy and $term->term_id == $p_term[1] ){
                                echo ' checked="checked"';
                                break;
                            }
                        }
                    }
                    ?> /><label for='radio_<?php echo $term->term_id ?>' class="berocket_label_widgets"> <?php echo $term->name ?></label>
            </span>
        </li>
    <?php endforeach; ?>
        <li class="berocket_widget_show_values"<?php if( !$hiden_value ) echo ' style="display: none;"'; ?>>Show value<span class="show_button"></span></li>
<?php endif; ?>
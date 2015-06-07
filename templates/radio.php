<?php 
$random_name = rand();
if ( $terms ):
    foreach( $terms as $term ): ?>
        <li>
            <span>
                <input class="<?php echo @ $uo['class']['checkbox_radio'] ?> radio_<?php echo $term->term_id?>" type='radio' id='radio_<?php echo $term->term_id?>_<?php echo $random_name ?>'
                    name='radio_<?php echo $term->taxonomy ?>_<?php echo $x ?>_<?php echo $random_name ?>'
                    data-term_id='<?php echo $term->term_id ?>' data-taxonomy='<?php echo $term->taxonomy ?>' data-operator='<?php echo $operator ?>' data-first_page='<?php echo (($first_page_jump) ? '1' : '0'); ?>'
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
    <?php endforeach;
endif;
?>
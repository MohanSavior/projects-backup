<?php

function startup_post_template( $atts )
{
    ob_start();
    $attr = shortcode_atts(
		array(
			'post_id' => get_the_ID(),
		), $atts, 'startup_post_template' );
    ?>
        <div class="startup-post-list-cls startup-post-<?=get_the_ID()?>">                           
            <?php
                printf('<div class="post-thumbnail-cls">%s</div>', the_post_thumbnail('thumbnail'));
                printf('<h2 class="post-title">%s</h2>', get_the_title());
                $result = implode(', ', array_slice(str_word_count(strip_tags(get_the_term_list($attr['post_id'], 'startup_tags', ' ', ', ')), 2), 0, 2));
                printf('<div class="tag-term-cls">%s</div>', $result);

                $field_date = strtotime(get_field( "year_of_incorporation", $attr['post_id'] ));
                printf('<div class="custom-fields-cls"><h5>Founded:</h5><h5>%s</h5></div>', date_i18n( "Y", $field_date ));
                printf('<div class="custom-fields-cls"><h5>Employees:</h5><h5>%s</h5></div>', get_field( "employees", $attr['post_id'] ));
                printf('<div class="custom-fields-cls"><h5>MRR:</h5><h5>%s</h5></div>', '$'.get_field( "mrr", $attr['post_id'] ));
                printf('<div class="custom-fields-cls"><h5>CLTV:</h5><h5>%s</h5></div>', '$'.get_field( "cltv", $attr['post_id'] ));
                printf('<div class="custom-fields-cls"><h5>CAC:</h5><h5>%s</h5></div>', '$'.get_field( "cac", $attr['post_id'] ));

                printf('<div class="post-single-btn"><a href="%s">View Company</a></div>', get_permalink( $attr['post_id'] ));

            ?>                    
        </div>	
    <?php
    $output = ob_get_contents();
    ob_get_clean();
    return $output;	
}
add_shortcode('startup_post_template','startup_post_template');
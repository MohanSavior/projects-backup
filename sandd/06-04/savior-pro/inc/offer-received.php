<?php
add_shortcode( 'offer_received', 'offer_received_author' );
function offer_received_author()
{
    ob_start();
	$startup_args = array( 
		'post_type' 		=> 'startup', 
		'posts_per_page' 	=> -1,
		'author' 			=> get_current_user_id(),
		'post_status'		=>'publish'
	);
	$startup_query = new WP_Query( $startup_args ); 

    if ( $startup_query->have_posts() ) :  ?>
    <!-- Offers Received Html -->
    <div class="offer-list-main-cls">
        <?php while ( $startup_query->have_posts() ) : $startup_query->the_post(); $startup_post_id = get_the_ID(); ?>
        <!-- Loop div start -->
        <div class="offer-post-list">
            <div class="offer-post-details-cls">
                <img src="http://sandd.saviormarketing.com/wp-content/uploads/2022/11/tensdef-app-img.png">
                <h4><?php the_title(); ?></h4>
            </div>

            <table class="subs-user-list-cls-<?=$startup_post_id?> display responsive nowrap">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Value</th>
                        <th>Sent Date</th>
                        <th>Offer Name</th>
                        <th>Activity</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $offers_args = array(
                        'post_type' 	=> 'offers',
                        'post_status' 	=> 'publish',
                        'posts_per_page'=> -1,
                        'meta_key'      => 'offer_for_post_id',
                        'meta_value'    => get_the_ID()
                    );
                    // $posts = get_posts($args);
                    $offers_query = new WP_Query( $offers_args ); 
                    if ( $offers_query->have_posts() ) : 
                        while ( $offers_query->have_posts() ) : $offers_query->the_post(); 

                            $author_id = get_post_field ('post_author', get_the_ID());
                            $user_obj = get_user_by( 'id', $author_id );
                            $ret = '';
                            foreach (explode(' ', get_the_title(get_post_meta(get_the_ID(),'offer_for_post_id', true))) as $word)
                            {
                                $ret .= strtoupper($word[0]);
                            }
        
                            printf(
                                '<tr>
                                    <td>%s</td>
                                    <td>%s</td>
                                    <td>$%s</td>
                                    <td>%s</td>
                                    <td>%s</td>
                                    <td><a href="%s">View</a></td>
                                </tr>',
                                $user_obj->first_name . ' ' . $user_obj->last_name,
                                $user_obj->user_email,
                                number_format(get_post_meta( get_the_ID(), 'offer_value', true ),0,".",","),
                                human_time_diff(get_the_time ( 'U' ), current_time( 'timestamp' ) ) . ': ' .get_the_date('d/m/Y'),
                                $ret. "#" .get_the_ID(),
                                get_permalink()
                            );
                        endwhile;
                        wp_reset_postdata(); 
                        ?>
                        <script>
                            jQuery(document).ready(function () {
                                jQuery('.subs-user-list-cls-<?=$startup_post_id?>').DataTable();
                            });
                        </script>
                    <?php  else:  
                    ?>
                    <tr>
                        <th colspan="7">
                            <p><?php _e( 'Sorry, You haven\'t received any offer on this post yet.' ); ?></p>
                        </th>
                    </tr>                    
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Loop div end -->
        <?php
            endwhile;
            wp_reset_postdata(); 
        ?>
        <?php else:  ?>
        <p><?php _e( 'Sorry, You haven\'t received any offer yet.' ); ?></p>
    </div>
    <?php endif; ?>
    <?php
    $output = ob_get_contents();
	ob_get_clean();
	return $output;	
}
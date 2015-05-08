<?php 
global $post;
//echo $post->ID;
$value5 = get_post_meta( $post->ID, 'room_schedule', true );
wp_nonce_field( 'myplugin_inner_schedule_box', 'myplugin_inner_schedule_box_nonce' );
echo "Here".$value5;
echo '<input type="hidden" id="last_index" name="room_schedule" value="' . esc_attr( $value5 ) . '"/>';
?>
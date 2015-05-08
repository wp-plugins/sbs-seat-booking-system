<?php
/**
 * Template Name: Custom Template
 *
 * Description: Twenty Twelve loves the no-sidebar look as much as
 * you do. Use this page template to remove the sidebar from any page.
 *
 * Tip: to remove the sidebar from all posts and pages simply remove
 * any active widgets from the Main Sidebar area, and the sidebar will
 * disappear everywhere.
 *
 * @package WordPress
 * @subpackage room-drag
 * @since room-drag 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'page' ); ?>
				<?php //comments_template( '', true ); ?>
			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->
        <div> 
        <?php 
        $args = array(
                'post_type' =>"room",
                'posts_per_page' => -1
            );
        $query = get_posts( $args );
        echo "<select id='select_room'>";
        echo "<option value=''>Select Room</option>";
        foreach ($query as $q)
        {
            echo "<option value='{$q->ID}'>{$q->post_title}</option>";
        }
        echo "</select>";
        ?>
            <div class="clear"></div>
            <div id="canvas_panel"></div>
        </div>
       
<?php get_footer(); ?>
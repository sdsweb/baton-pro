<?php
/*
 * This template is used for the display of single posts.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-single baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Post Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-post-container content-single-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop', 'single' ); // Loop - Single ?>

						<!-- Comments -->
						<?php comments_template(); // Comments ?>
						<!-- End Comments -->

						<div class="clear"></div>
					</section>
				</div>
				<!-- End Post Content -->

				<!-- Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
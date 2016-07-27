<?php
/*
 * This template is used for the display of single images.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-single content-wrap-single-attachment baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Image Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-attachment-container content-single-container content-attachment-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop', 'image' ); // Loop - Single ?>

						<!-- Comments -->
						<?php comments_template(); // Comments ?>
						<!-- End Comments -->

						<div class="clear"></div>
					</section>
				</div>
				<!-- End Image Content -->

				<!-- Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
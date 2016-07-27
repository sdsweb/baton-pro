<?php
/**
 * This template is used for the display of archives.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-archive baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Archive Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-archive-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop', 'archive' ); // Loop - Archive ?>

						<?php get_template_part( 'loop', 'navigation' ); // Loop - Navigation ?>
					</section>
				</div>
				<!-- End Archive Content -->

				<!-- Primary Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Primary Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
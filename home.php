<?php
/**
 * This template is used for the display of blog posts (archive; river).
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-home content-wrap-blog baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Home/Blog Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-home-container content-blog-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop', 'home' ); // Loop - Home ?>

						<?php get_template_part( 'loop', 'navigation' ); // Loop - Navigation ?>
					</section>
				</div>
				<!-- End Home/Blog Content -->

				<!-- Primary Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Primary Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
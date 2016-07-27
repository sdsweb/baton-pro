<?php
/*
 * This template is used for displaying the Front Page (when selected in Settings > Reading).
 *
 * This template is used even when the option is selected, but a page is not. It contains fallback functionality
 * to ensure content is still displayed.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-page baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<?php
					// Front page is active
					if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' ) ) :
				?>
					<?php if ( is_active_sidebar( 'front-page-sidebar' ) ) : // Front Page Sidebar ?>
						<!-- Front Page Sidebar -->
						<aside class="front-page-widgets <?php echo ( is_active_sidebar( 'front-page-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
							<?php dynamic_sidebar( 'front-page-sidebar' ); ?>
						</aside>
						<!-- End Front Page Sidebar -->
					<?php else: ?>
						<!-- Page Content -->
						<div class="baton-col baton-col-content">
							<section class="content-container content-page-container">
								<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

								<?php get_template_part( 'loop', 'page' ); // Loop - Page ?>

								<!-- Comments -->
								<?php comments_template(); // Comments ?>
								<!-- End Comments -->

								<div class="clear"></div>
							</section>
						</div>
						<!-- End Page Content -->

						<!-- Sidebar -->
						<?php get_sidebar(); ?>
						<!-- End Sidebar -->
					<?php endif; ?>
				<?php
					// No "Front Page" Selected, show posts
					else:
				?>
					<!-- Home/Blog Content -->
					<div class="baton-col baton-col-content">
						<section class="content-container content-home-container content-blog-container">
							<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

							<?php get_template_part( 'loop', 'home' ); // Loop - Home ?>

							<?php get_template_part( 'loop', 'navigation' ); // Loop - Navigation ?>
						</section>
					</div>
					<!-- End Home/Blog Content -->

					<!-- Sidebar -->
					<?php get_sidebar(); ?>
					<!-- End Sidebar -->
				<?php
					endif;
				?>

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
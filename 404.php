<?php
/*
 * This template is used for the display of 404 (Not Found) errors.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-404 baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Page Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-page-container">
						<!-- Article -->
						<article class="content error-404 no-posts cf">
							<!-- Article Header -->
							<header class="article-title-wrap">
								<h1 title="<?php _e( '404 Error', 'baton' ); ?>" class="article-title"><?php _e( '404 Error', 'baton' ); ?></h1>
							</header>
							<!-- End Article Header -->

							<!-- Article Content -->
							<div class="article-content cf">
								<p><?php _e( 'We apologize but something when wrong while trying to find what you were looking for. Please use the navigation below to navigate to your destination.', 'baton' ); ?></p>

								<div id="search-404" class="search-404">
									<p><?php _e( 'Search:', 'baton' ); ?></p>
									<?php echo get_search_form(); ?>
								</div>

								<?php sds_sitemap(); ?>

								<div class="clear"></div>
							</div>
							<!-- End Article Content -->

							<div class="clear"></div>
						</article>
						<!-- End Article -->

						<div class="clear"></div>
					</section>
				</div>
				<!-- End Page Content -->

				<!-- Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
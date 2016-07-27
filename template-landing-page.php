<?php
/*
 * Template Name: Landing Page
 * This template is used for the display of landing pages.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header( 'landing-page' ); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-page content-wrap-full-width-page content-wrap-landing-page baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Page Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-page-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop', 'page-full-width' ); // Loop - Page Full Width ?>

						<!-- Comments -->
						<?php comments_template(); // Comments ?>
						<!-- End Comments -->

						<div class="clear"></div>
					</section>
				</div>

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer( 'landing-page' ); ?>
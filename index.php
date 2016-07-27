<?php
/*
 * This template is used for the display of all post types that do not have templates (used as a fallback).
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header(); ?>

			<!-- Main -->
			<main role="main" class="content-wrap content-wrap-index baton-flex <?php echo ( baton_is_yoast_breadcrumbs_active() ) ? 'has-breadcrumbs' : 'no-breadcrumbs'; ?>">
				<!-- Index Content -->
				<div class="baton-col baton-col-content">
					<section class="content-container content-index-container">
						<?php get_template_part( 'yoast', 'breadcrumbs' ); // Yoast Breadcrumbs ?>

						<?php get_template_part( 'loop' ); // Loop ?>

						<!-- Comments -->
						<?php comments_template(); // Comments ?>
						<!-- End Comments -->

						<?php get_template_part( 'loop', 'navigation' ); // Loop - Navigation ?>
					</section>
				</div>
				<!-- End Index Content -->

				<!-- Primary Sidebar -->
				<?php get_sidebar(); ?>
				<!-- End Primary Sidebar -->

				<div class="clear"></div>
			</main>
			<!-- End Main -->

<?php get_footer(); ?>
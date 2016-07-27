<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;

	global $post;

	// Loop through posts
	if ( have_posts() ) :
?>
		<header class="search-title">
			<h1 title="<?php esc_attr_e( sprintf( __( 'Search results for \'%s\'', 'baton' ), get_search_query() ) ); ?>" class="page-title"><?php printf( __( 'Search results for "%s"', 'baton' ), get_search_query() ); ?></h1>
		</header>
<?php
		while ( have_posts() ) : the_post();
?>
		<?php if ( has_post_thumbnail() ): ?>
			<!-- Post Thumbnail/Featured Image -->
			<div class="article-thumbnail-wrap article-featured-image-wrap post-thumbnail-wrap featured-image-wrap cf">
				<?php sds_featured_image( true ); ?>
			</div>
			<!-- End Post Thumbnail/Featured Image -->
		<?php endif; ?>

		<!-- Article -->
		<article id="post-<?php the_ID(); ?>" <?php post_class( esc_attr( 'content content-' . $post->post_type  . ' content-search cf' ) ); ?>>
			<!-- Article Header -->
			<header class="article-title-wrap">
				<?php baton_categories_tags(); ?>

				<?php if ( strlen( get_the_title() ) > 0 ) : ?>
					<h1 class="article-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php endif; ?>
			</header>
			<!-- End Article Header -->

			<!-- Article Content -->
			<div class="article-content cf">
				<?php
					// Show the excerpt if it exists
					if ( has_excerpt() ) :
						the_excerpt();
					?>
						<div class="clear"></div>

						<a href="<?php the_permalink(); ?>" class="button more-link"><?php echo baton_more_link_label(); ?></a>
					<?php
					// Otherwise show the content
					else :
						the_content( baton_more_link_label() );
					endif;
				?>
			</div>
			<!-- End Article Content -->

			<div class="clear"></div>

			<?php sds_post_meta( true ); ?>

			<div class="clear"></div>
		</article>
		<!-- End Article -->

		<div class="clear"></div>
<?php
		endwhile;
	else: // No Posts
?>
	<header class="search-title search-title-no-results">
		<h1 title="<?php esc_attr_e( sprintf( __( 'No results for \'%s\'', 'baton' ), get_search_query() ) ); ?>'" class="page-title"><?php printf( __( 'No results for "%s"', 'baton' ), get_search_query() ); ?></h1>
	</header>

	<!-- Article (No Posts) -->
	<article class="content no-posts no-search-posts content-search cf">
		<!-- Article Content -->
		<div class="article-content cf">
			<?php sds_no_posts(); ?>

			<div class="search-again search-block no-posts no-search-results">
				<p><?php _e( 'Would you like to search again?', 'baton' ); ?></p>
				<?php echo get_search_form(); ?>
			</div>

			<div class="clear"></div>
		</div>
		<!-- End Article Content -->
	</article>
	<!-- End Article (No Posts) -->
<?php
	endif;
?>
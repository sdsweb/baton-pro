<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;

	global $multipage, $post;

	// Loop through posts
	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
?>
		<?php if ( has_post_thumbnail() ): ?>
			<!-- Post Thumbnail/Featured Image -->
			<div class="article-thumbnail-wrap article-featured-image-wrap post-thumbnail-wrap featured-image-wrap cf">
				<?php sds_featured_image(); ?>
			</div>
			<!-- End Post Thumbnail/Featured Image -->
		<?php endif; ?>

		<!-- Article -->
		<article id="post-<?php the_ID(); ?>" <?php post_class( esc_attr( 'content content-' . $post->post_type  . ' cf' ) ); ?>>
			<!-- Article Header -->
			<header class="article-title-wrap">
				<?php baton_categories_tags(); ?>

				<h1 class="article-title"><?php the_title(); ?></h1>
			</header>
			<!-- End Article Header -->

			<!-- Article Content -->
			<div class="article-content cf">
				<p class="article-attachment-meta">
					<?php
						$metadata = wp_get_attachment_metadata();
						printf( '<span class="meta-prep meta-prep-entry-date">Published</span> <span class="entry-date"><time class="entry-date" datetime="%1$s">%2$s</time></span> at <a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a> in <a href="%6$s" title="Return to %7$s" rel="gallery">%8$s</a>.',
								esc_attr( get_the_date( 'c' ) ),
								esc_html( get_the_time( get_option( 'date_format' ) ) ),
								esc_url( wp_get_attachment_url() ),
								$metadata['width'],
								$metadata['height'],
								esc_url( get_permalink( $post->post_parent ) ),
								esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
								get_the_title( $post->post_parent )
						);
					?>
				</p>

				<div class="attachment image">
					<?php
					/**
					 * Grab the IDs of all the image attachments in a gallery so we can get the URL of the next adjacent image in a gallery,
					 * or the first image (if we're looking at the last image in a gallery), or, in a gallery of one, just the link to that image file
					 */
					$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );
					foreach ( $attachments as $k => $attachment )
						if ( $attachment->ID == $post->ID )
							break;

					$k++;
					// If there is more than 1 attachment in a gallery
					if ( count( $attachments ) > 1 ) :
						if ( isset( $attachments[$k] ) ) :
							// get the URL of the next image attachment
							$next_attachment_url = get_attachment_link( $attachments[$k]->ID );
						else :
							// or get the URL of the first image attachment
							$next_attachment_url = get_attachment_link( $attachments[0]->ID );
						endif;
					else :
						// or, if there's only 1 image, get the URL of the image
						$next_attachment_url = wp_get_attachment_url();
					endif;
					?>

					<a href="<?php echo esc_url( $next_attachment_url ); ?>" title="<?php the_title_attribute(); ?>" rel="attachment">
						<?php echo wp_get_attachment_image( $post->ID, 'large' ); ?>
					</a>
				</div>

				<div class="clear"></div>

				<?php if ( ! empty( $post->post_excerpt ) ) : ?>
					<?php the_content(); ?>

					<div class="clear"></div>
				<?php endif; ?>

				<?php edit_post_link( __( 'Edit Image', 'baton' ) ); // Allow logged in users to edit ?>

				<div class="clear"></div>
			</div>
			<!-- End Article Content -->

			<div class="clear"></div>
		</article>
		<!-- End Article -->

		<div class="clear"></div>

		<?php sds_single_image_navigation(); ?>

		<div class="clear"></div>

		<?php get_template_part( 'loop', 'author' ); // Loop - Author ?>

		<div class="clear"></div>
<?php
		endwhile;
	endif;
?>
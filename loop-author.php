<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<?php if ( sds_show_author_meta() ) : ?>
	<!-- Author -->
	<div id="article-author" class="article-author baton-flex baton-flex-2-columns baton-flex-article-author">
		<div class="author-header baton-col baton-col-author-avatar">
			<figure class="author-avatar">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 568 ); ?>
			</figure>
		</div>

		<div class="author-meta baton-col baton-col-author-meta">
			<h3 class="author-name">
				<?php echo get_the_author_meta( 'display_name' ); ?>
			</h3>
			<p class="author-details author-description">
				<?php echo get_the_author_meta( 'description' ); ?>
			</p>
			<div class="author-url">
				<a class="author-url-link" href="<?php echo esc_url( get_the_author_meta( 'user_url' ) ); ?>"><?php echo get_the_author_meta( 'user_url' ); ?></a>
			</div>
			<div class="author-view-more-link">
				<a class="author-view-more-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php _e( 'View more posts from this author', 'baton' ); ?></a>
			</div>
		</div>
	</div>
	<!-- End Author -->
<?php endif; ?>
<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments and the comment
 * form. The actual display of comments is handled by a callback to
 * sds_comment() which is located in the /includes/theme-functions.php file.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

// If the current post is protected by a password and the visitor has not yet entered the password we will return early without loading the comments
if ( post_password_required() )
	return;
?>

<section id="comments" class="comments comments-wrap <?php echo ( comments_open() ) ? 'comments-open' : 'comments-closed'; ?> <?php echo ( ( int ) get_comments_number() !== 0 && have_comments() ) ? 'has-comments' : 'no-comments'; ?>">
	<?php if ( have_comments() ) : ?>
		<!-- Comments Header -->
		<header class="comments-title-wrap cf">
			<h3 class="comments-title">
				<?php
					printf( _n( 'One comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', get_comments_number(), 'baton' ),
					number_format_i18n( get_comments_number() ),
					'<span>' . get_the_title() . '</span>' );
				?>
			</h3>
		</header>
		<!-- End Comments Header -->

		<!-- Comments List -->
		<ol class="comments-list">
			<?php wp_list_comments( array( 'callback' => 'baton_comment', 'style' => 'ol' ) ); ?>
		</ol>
		<!-- End Comments List -->
	<?php endif;?>

	<div class="clear"></div>

	<!-- Comments Form -->
	<?php comment_form(); // Comment Form ?>
	<!-- End Comments Form -->

	<div class="clear"></div>
</section>

<!-- Comments Navigation -->
<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // Are there comments to navigate through? ?>
	<div class="comments-navigation baton-flex baton-flex-2-columns baton-flex-comments-navigation">
		<div class="comments-navigation-link comments-navigation-previous-link comments-navigation-previous baton-col baton-col-comments-previous">
			<?php previous_comments_link( __( 'Older Comments', 'baton' ) ); ?>
		</div>

		<div class="comments-navigation-link comments-navigation-next-link comments-navigation-next baton-col baton-col-comments-next">
			<?php next_comments_link( __( 'Newer Comments', 'baton' ) ); ?>
		</div>
	</div>
<?php endif; ?>
<!-- End Comments Navigation -->
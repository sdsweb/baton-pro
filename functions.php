<?php
/**
 *
 * WARNING: Please do not edit this file.
 * @see http://codex.wordpress.org/Child_Themes
 *
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * This function outputs a list of selected social networks based on options. Can be called throughout the theme and is used in the Social Media Widget.
 */
if ( ! function_exists( 'sds_social_media' ) ) {
	function sds_social_media() {
		global $sds_theme_options;

		if ( ! empty( $sds_theme_options['social_media'] ) ) {
			// Map the correct values for social icon display (FontAwesome webfont, i.e. 'fa-rss' = RSS icon)
			$social_font_map = array(
				'facebook_url' => array(
					'icon' => 'fa fa-facebook',
					'label' => __( 'Facebook', 'baton-pro' )
				),
				'twitter_url' => array(
					'icon' => 'fa fa-twitter',
					'label' => __( 'Twitter', 'baton-pro' )
				),
				'linkedin_url' => array(
					'icon' => 'fa fa-linkedin',
					'label' => __( 'LinkedIn', 'baton-pro' )
				),
				'google_plus_url' => array(
					'icon' => 'fa fa-google-plus',
					'label' => __( 'Google+', 'baton-pro' )
				),
				'youtube_url' => array(
					'icon' => 'fa fa-youtube',
					'label' => __( 'YouTube', 'baton-pro' )
				),
				'vimeo_url' => array(
					'icon' => 'fa fa-vimeo-square',
					'label' => __( 'Vimeo', 'baton-pro' )
				),
				'pinterest_url' => array(
					'icon' => 'fa fa-pinterest',
					'label' => __( 'Pinterest', 'baton-pro' )
				),
				'instagram_url' => array(
					'icon' => 'fa fa-instagram',
					'label' => __( 'Instagram', 'baton-pro' )
				),
				'flickr_url' => array(
					'icon' => 'fa fa-flickr',
					'label' => __( 'Flickr', 'baton-pro' )
				),
				'foursquare_url' => array(
					'icon' => 'fa fa-foursquare',
					'label' => __( 'Foursquare', 'baton-pro' )
				),
				'rss_url' => array(
					'icon' => 'fa fa-rss',
					'label' => __( 'RSS', 'baton-pro' )
				),
			);

			$social_font_map = apply_filters( 'sds_social_icon_map', $social_font_map );
			?>
			<div class="social-media-icons baton-flex baton-flex-5-columns baton-flex-social-media">
				<?php
				foreach( $sds_theme_options['social_media'] as $key => $url ) :
					// RSS (use site RSS feed, $url is Boolean this case)
					if ( $key === 'rss_url_use_site_feed' && $url ) :
					?>
						<a href="<?php esc_attr( bloginfo( 'rss2_url' ) ); ?>" class="rss-url baton-col baton-col-social-media" target="_blank">
							<span class="social-media-icon <?php echo esc_attr( $social_font_map['rss_url']['icon'] ); ?>"></span>
							<br />
							<span class="social-media-label rss-url-label"><?php echo $social_font_map['rss_url']['label']; ?></span>
						</a>
					<?php
					// RSS (use custom RSS feed)
					elseif ( $key === 'rss_url' && ! $sds_theme_options['social_media']['rss_url_use_site_feed'] && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_attr( $url ); ?>" class="rss-url baton-col baton-col-social-media" target="_blank">
							<span class="social-media-icon <?php echo esc_attr( $social_font_map['rss_url']['icon'] ); ?>"></span>
							<br />
							<span class="social-media-label rss-url-label"><?php echo $social_font_map['rss_url']['label']; ?></span>
						</a>
					<?php
					// All other networks
					elseif ( $key !== 'rss_url_use_site_feed' && $key !== 'rss_url' && ! empty( $url ) ) :
						$css_class = str_replace( '_', '-', $key ); // Replace _ with -
					?>
						<a href="<?php echo esc_attr( $url ); ?>" class="<?php echo esc_attr( $css_class ); ?> baton-col baton-col-social-media" target="_blank">
							<span class="social-media-icon <?php echo esc_attr( $social_font_map[$key]['icon'] ); ?>"></span>
							<br />
							<span class="social-media-label <?php echo esc_attr( $css_class ); ?>-label"><?php echo $social_font_map[$key]['label']; ?></span>
						</a>
					<?php
					endif;
				endforeach;
				?>
			</div>
		<?php
		}
	}
}

/**
 * This function displays meta for the current post (including categories and tags).
 */
if ( ! function_exists( 'sds_post_meta' ) ) {
	function sds_post_meta( $archive = false, $force_display = false ) {
		global $sds_theme_options, $post;

		// Show meta flag (determine if we should we should show the post meta based on $force_display or settings and if this is a post)
		$show_meta = ( $force_display || ( ! $sds_theme_options['hide_post_meta'] && $post->post_type === 'post' ) );

		// CSS Classes
		$css_classes = array(
			'article-post-meta',
			( $show_meta ) ? 'has-meta' : 'no-meta'
		);

		if ( $archive )
			$css_classes = array_merge( $css_classes, array(
				'article-post-meta-archive',
				'baton-flex',
				'baton-flex-3-columns',
				'baton-flex-post-meta-archive'
			) );

		$css_classes = array_filter( $css_classes, 'sanitize_html_class' );
	?>
			<!-- Post Meta -->
			<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
				<?php
					// If we should show meta
					if ( $show_meta ) :
				?>
					<span class="article-date <?php echo ( $archive ) ? 'baton-col baton-col-article-date' : false; ?>">
						<?php
							// Archives without titles
							if ( $archive && strlen( get_the_title() ) === 0 ) :
						?>
							<a href="<?php the_permalink(); ?>">
								<span class="fa fa-calendar-o"></span>
								<?php echo get_the_time( get_option( 'date_format' ) ); ?>
							</a>
						<?php
							// Everything else
							else:
						?>
								<span class="fa fa-calendar-o"></span>
						<?php
								echo get_the_time( get_option( 'date_format' ) );
							endif;
						?>
					</span>

					<?php
						if ( $archive ) :
					?>
						<span class="article-author-link <?php echo ( $archive ) ? 'baton-col baton-col-author-link' : false; ?>">
							<a class="author-view-more-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
								<span class="fa fa-user"></span>
								<?php echo get_the_author_meta( 'display_name' ); ?>
							</a>
						</span>
					<?php
						endif;
					?>

					<span class="article-comments-link <?php echo ( $archive ) ? 'baton-col baton-col-comments-link' : false; ?>">
						<a href="<?php echo esc_url( get_comments_link() ); ?>">
							<span class="fa fa-comment-o"></span>
							<?php echo get_comments_number(); ?>
						</a>
					</span>
				<?php
					endif;
				?>
			</div>
			<!-- End Post Meta -->
		<?php
	}
}

/**
 * This function outputs categories and tags.
 */
if ( ! function_exists( 'baton_categories_tags' ) ) {
	function baton_categories_tags( $force_display = false ) {
		global $sds_theme_options;

		// Bail we shouldn't show categories and tags above post titles
		if ( $sds_theme_options['baton']['hide_cats_tags_above_post_titles'] )
			return;

		// Grab categories and tags
		$categories = get_the_category();
		$tags = get_the_tags();

		// CSS Classes
		$css_classes = array(
			( $categories ) ? 'has-categories' : 'no-categories',
			( $tags ) ? 'has-tags' : 'no-tags'
		);
	?>
		<div class="article-categories-wrap <?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
			<?php
				// Categories
				if ( $categories && ( is_singular( 'post' ) || $force_display ) ) :
			?>
				<span class="categories">
					<span class="fa fa-filter"></span>
					<?php the_category( ', ' ); ?>
				</span>
			<?php
				endif;
			?>

			<?php
				// Tags
				if ( $tags && ( is_singular( 'post' ) || $force_display ) ) :
			?>
				<span class="tags">
					<span class="fa <?php echo esc_attr( ( count( $tags ) > 1 ) ? 'fa-tags' : 'fa-tag' ); ?>"></span>
					<?php the_tags( '', ', ' ); ?>
				</span>
			<?php
				endif;
			?>
		</div>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single posts.
 */
if ( ! function_exists( 'sds_single_post_navigation' ) ) {
	function sds_single_post_navigation() {
		$next_post_link = get_next_post_link( '%link', '%title' );
		$previous_post_link = get_previous_post_link( '%link', '%title' );
	?>
		<!-- Single Post Navigation -->
		<div class="article-post-navigation single-post-navigation post-navigation baton-flex baton-flex-2-columns baton-flex-post-navigation <?php echo esc_attr( ( $next_post_link || $previous_post_link ) ? 'has-links': 'no-links' ); ?>">
			<div class="previous-posts baton-col baton-col-previous-posts">
				<?php if ( $next_post_link ) : ?>
					<span class="article-post-navigation-label"><?php _e( 'Previous Post', 'baton-pro' ); ?></span>
					<?php echo $next_post_link; ?>
				<?php endif; ?>
			</div>
			<div class="next-posts baton-col baton-col-next-posts">
				<?php if ( $previous_post_link ) : ?>
					<span class="article-post-navigation-label"><?php _e( 'Next Post', 'baton-pro' ); ?></span>
					<?php echo $previous_post_link; ?>
				<?php endif; ?>
			</div>
		</div>
		<!-- End Single Post Navigation -->
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single image attachments.
 */
if ( ! function_exists( 'sds_single_image_navigation' ) ) {
	function sds_single_image_navigation() {
	?>
		<!-- Single Image Navigation -->
		<div class="article-post-navigation single-post-navigation post-navigation single-image-navigation image-navigation baton-flex baton-flex-2-columns baton-flex-post-navigation">
			<div class="previous-posts baton-col baton-col-previous-posts">
				<?php previous_image_link( false, 'Previous Image' ); ?>
			</div>
			<div class="next-posts baton-col baton-col-next-posts">
				<?php next_image_link( false, 'Next Image' ); ?>
			</div>
		</div>
		<!-- End Single Image Navigation -->
	<?php
	}
}

/**
 * This function registers all content layouts available in this theme.
 */
if ( ! function_exists( 'sds_content_layouts' ) ) {
	function sds_content_layouts() {
		$content_layouts = array(
			'default' => array( // Name used in saved option
				'label' => __( 'Default', 'baton-pro' ), // Label on options panel (required)
				'preview' => '<div class="cols cols-1 cols-default"><div class="col col-content" title="%1$s"><span class="label">%1$s</span></div></div>', // Preview on options panel (required; %1$s is replaced with values below on options panel if specified)
				'preview_values' => array( __( 'Default', 'baton-pro' ) ),
				'default' => true
			),
			'cols-1' => array( // Full Width
				'label' => __( 'Full Width', 'baton-pro' ),
				'preview' => '<div class="cols cols-1"><div class="col col-content"></div></div>',
			),
			'cols-2' => array( // Content Left, Primary Sidebar Right
				'label' => __( 'Content Left', 'baton-pro' ),
				'preview' => '<div class="cols cols-2"><div class="col col-content"></div><div class="col col-sidebar"></div></div>'
			),
			'cols-2-r' => array( // Content Right, Primary Sidebar Left
				'label' => __( 'Content Right', 'baton-pro' ),
				'preview' => '<div class="cols cols-2 cols-2-r"><div class="col col-sidebar"></div><div class="col col-content"></div></div>'
			)
		);

		return apply_filters( 'sds_theme_options_content_layouts', $content_layouts );
	}
}

/**
 * This function modifies the global $content_width value based on content layout or page template settings.
 */
if ( ! function_exists( 'baton_body_class' ) ) {
	add_filter( 'body_class', 'baton_body_class', 20 );

	function baton_body_class( $classes ) {
		global $sds_theme_options, $content_width;

		// Baton Customizer Typography
		if ( ! function_exists( 'Baton_Customizer_Typography' ) )
			include_once 'theme/class-baton-customizer-typography.php'; // Customizer Typography Class

		$baton_customizer_typography = Baton_Customizer_Typography();

		// Content layout was specified by user in Theme Options
		if ( isset( $sds_theme_options['body_class'] ) && ! empty( $sds_theme_options['body_class'] ) ) {
			// 1 Column
			if ( $sds_theme_options['body_class'] === 'cols-1' )
				$content_width = 1272;
			// 3 Columns
			//else if ( strpos( $sds_theme_options['body_class'], 'cols-3' ) !== false )
			//	$content_width = 722;
		}

		// Page Template was specified by the user for this page
		if ( ! empty( $sds_theme_options['page_template'] ) && $sds_theme_options['page_template'] !== 'default' ) {
			// Full Width or Landing Page
			if ( in_array( $sds_theme_options['page_template'], array( 'page-full-width.php', 'page-landing-page.php' ) ) )
				$content_width = 1272;
		}

		// Customizer
		if ( $baton_customizer_typography->is_customize_preview() )
			$classes['baton-customizer'] = 'customizer';

		// Front Page Sidebar/Widgets
		if ( is_front_page() && get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' ) && is_active_sidebar( 'front-page-sidebar' ) ) {
			$classes['baton-front-page-sidebar-active'] = 'front-page-sidebar-active';

			// If Conductor is active on the Front Page
			if ( class_exists( 'Conductor' ) && Conductor::is_conductor() )
				// Remove the CSS class
				unset( $classes['baton-front-page-sidebar-active'] );
		}

		// Baton Landing Page - Conductor
		if ( baton_is_conductor_baton_landing_page() )
			// If the content sidebar is active (widgets)
			if ( conductor_is_active_sidebar( 'content' ) )
				$classes['baton-conductor-landing-page'] = 'conductor-baton-landing-page-active';
			// Otherwise the content sidebar is inactive (no widgets)
			else
				$classes['baton-conductor-landing-page'] = 'conductor-baton-landing-page-inactive';

		// Baton Note Sidebars
		if ( baton_is_note_baton_sidebar_active() ) {
			$classes['baton-note-sidebars'] = 'baton-note-sidebars-active';

			// Grab the Baton instance
			$baton_instance = Baton_Instance();

			// Grab Note Baton sidebar IDs by location
			$note_sidebar_ids_by_location = $baton_instance->note_sidebar_ids_by_location;

			// Determine whether or not content wrapper sidebars are active
			$baton_note_content_wrapper_before_sidebar_active = baton_is_note_baton_sidebar_active( $note_sidebar_ids_by_location['baton-content-wrapper']['before'] );
			$baton_note_content_wrapper_after_sidebar_active = baton_is_note_baton_sidebar_active( $note_sidebar_ids_by_location['baton-content-wrapper']['after'] );

			// Note Baton Content Wrapper
			$classes['baton-note-content-wrapper-sidebar'] = ( $baton_note_content_wrapper_before_sidebar_active || $baton_note_content_wrapper_after_sidebar_active ) ? 'baton-note-content-wrapper-sidebars-active' : 'baton-note-content-wrapper-sidebars-inactive';

			// Note Baton Content Wrapper Before
			$classes['baton-note-content-wrapper-before-sidebar'] = ( $baton_note_content_wrapper_before_sidebar_active ) ? 'baton-note-content-wrapper-before-sidebar-active' : 'baton-note-content-wrapper-before-sidebar-inactive';

			// Note Baton Content Wrapper After
			$classes['baton-note-content-wrapper-after-sidebar'] = ( $baton_note_content_wrapper_after_sidebar_active ) ? 'baton-note-content-wrapper-after-sidebar-active' : 'baton-note-content-wrapper-after-sidebar-inactive';
		}

		return $classes;
	}
}

/**
 * This function adjusts Theme Option defaults.
 */
if ( ! function_exists( 'sds_theme_options_defaults' ) ) {
	add_filter( 'sds_theme_options_defaults', 'sds_theme_options_defaults' );

	function sds_theme_options_defaults( $defaults ) {
		// Apply the default featured image size filter to the default featured image size value
		$defaults['featured_image_size'] = apply_filters( 'sds_theme_options_default_featured_image_size', '' );

		return $defaults;
	}
}

/**
 * This function sets a default featured image size for use in this theme.
 */
if ( ! function_exists( 'sds_theme_options_default_featured_image_size' ) ) {
	add_filter( 'sds_theme_options_default_featured_image_size', 'sds_theme_options_default_featured_image_size' );

	function sds_theme_options_default_featured_image_size( $default ) {
		return 'baton-1200x9999';
	}
}

if ( ! function_exists( 'sds_copyright_branding' ) ) {
	add_filter( 'sds_copyright_branding', 'sds_copyright_branding', 10, 2 );

	function sds_copyright_branding( $text, $theme_name ) {
		return '<a href="http://slocumthemes.com/wordpress-themes/baton/?utm_source=' . esc_url( home_url() ) . '&amp;utm_medium=footer-plugs&amp;utm_campaign=WordPressThemes" target="_blank">' . $theme_name . ' by Slocum Studio</a>';
	}
}

/**
 * This function returns the more link label for Baton.
 */
function baton_more_link_label( $return_default_only = false ) {
	// Return default
	if ( $return_default_only )
		return __( 'Continue Reading', 'baton-pro' );

	// Get theme mod
	$label = get_theme_mod( 'baton_more_link_label' );

	return ( ! empty( $label ) ) ? $label : __( 'Continue Reading', 'baton-pro' );
}

/**
 * This function returns the Boolean value of the parameter passed.
 */
if ( ! function_exists( 'baton_boolval' ) ) {
	function baton_boolval( $var, $wp_customize_setting = false ) {
		return ( bool ) $var;
	}
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @param object $comment Comment to display.
 * @param array $args Optional args.
 * @param int $depth Depth of comment.
 */
if ( ! function_exists( 'baton_comment' ) ) {
	function baton_comment( $comment, $args, $depth ) {
		// Set the global comment variable to this comment
		$GLOBALS['comment'] = $comment;

		// Switch based on comment type
		switch ( $comment->comment_type ) :
			// Pingbacks and Trackbacks
			case 'pingback':
			case 'trackback':
			?>
				<li id="comment-<?php comment_ID(); ?>" <?php comment_class( 'pingback' ); ?>>
					<p><?php _e( 'Pingback:', 'baton-pro' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( 'Edit', '<span class="ping-meta"><span class="edit-link">', '</span></span>' ); ?></p>
				</li>
			<?php
			break;
			// All other types of comments
			default:
			?>
				<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
					<article id="comment-<?php comment_ID(); ?>-wrap">
						<div class="comment-author vcard">
							<header class="comment-author-details baton-flex">
								<div class="author-avatar baton-col baton-col-comment-author-avatar">
									<?php echo get_avatar( $comment, 60 ); ?>
								</div>

								<div class="author-meta baton-col baton-col-comment-author-meta">
									<div class="author-link"><?php comment_author_link(); ?></div>
									<div class="comment-meta">
										<cite>
											<?php
												printf( __( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>', 'baton-pro' ),
													esc_url( get_comment_link( $comment->comment_ID ) ),
													get_comment_time( 'c' ),
													sprintf( __( '%1$s at %2$s', 'baton-pro' ), get_comment_date(), get_comment_time() )
												);
											?>

											<?php edit_comment_link( __( 'Edit', 'baton-pro' ), '<span class="edit-link">', '<span>' ); ?>
										</cite>
									</div>
								</div>
							</header>
						</div>

						<div class="comment-content-wrap">
							<?php if ( $comment->comment_approved === '0' ) : ?>
								<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'baton-pro' ); ?></p>
							<?php endif; ?>

							<div class="comment-content">
								<?php comment_text(); ?>
							</div>
						</div>

						<div class="clear"></div>

						<div class="comment-reply">
							<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'baton-pro' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
						</div>
					</article>
				</li>
			<?php
			break;
		endswitch;
	}
}



/**
 * This function returns a variant (shade) of a hex color, altering by "steps" in RGB. Negative
 * step values decrease the "shade" of the color and positive step values increase the "shade"
 * of the color.
 */
if ( ! function_exists( 'baton_get_color_variant' ) ) {
	function baton_get_color_variant( $color, $steps = 20 ) {
		// Make sure the steps are between -255 and 255
		$steps = max( -255, min( 255, $steps ) );

		// Remove the hash (if passed)
		$color = ltrim( $color, '#' );

		// If we have a shorthand color, normalize it
		if ( strlen( $color ) === 3 )
			// Repeat the RGB values
			$color = str_repeat( $color[0] , 2 ) . str_repeat( $color[1] , 2 ) . str_repeat( $color[2] , 2 );

		// Bail if the color length isn't 6
		if ( strlen( $color ) !== 6 )
			return $color;

		// Setup the return data
		$r = '#';

		// Split the color string into the three primary colors (RGB)
		$primary_colors = str_split( $color, 2 );

		// Loop through primary colors
		foreach ( $primary_colors as $primary_color ) {
			// Convert the color to a decimal first
			$primary_color = hexdec( $primary_color );

			// Adjust the color by the number of steps
			$primary_color = ( int ) max( 0, min( 255, $primary_color + ( $primary_color * ( $steps / 255 ) ) ) ); // max( 0, min( 255, $primary_color + $steps ) )

			// Convert the color back to a hex value
			$primary_color = dechex( $primary_color );

			// Add the color to the return data (making sure there are 2 hex characters; padding with a 0 if there are not)
			$r .= ( strlen( $primary_color ) < 2 ) ? str_pad( $primary_color, 2, '0', STR_PAD_LEFT ) : $primary_color;
		}

		return $r;
	}
}

/**
 * This function returns a RGBa color from a given hex color and alpha value
 * The return is formatted to be added to CSS
 * example: rgba(0, 0, 0, 0.5)
 */
if ( ! function_exists( 'baton_hex_to_rgba_css' ) ) {
	function baton_hex_to_rgba_css( $hex, $alpha ) {
		// Remove the hash (if passed)
		$hex = ltrim( $hex, '#' );

		// If we have a shorthand color, normalize it
		if ( strlen( $hex ) === 3 )
			// Repeat the RGB values
			$hex = str_repeat( $hex[0] , 2 ) . str_repeat( $hex[1] , 2 ) . str_repeat( $hex[2] , 2 );

		// Bail if the color length isn't 6
		if ( strlen( $hex ) !== 6 )
			return $hex;

		// Setup the return data
		$r = 'rgba(';

		// Split the color string into the three primary colors (RGB)
		$primary_colors = str_split( $hex, 2 );

		// Loop through primary colors
		foreach ( $primary_colors as $primary_color ) {
			// Convert the color to a decimal first
			$primary_color = hexdec( $primary_color );

			// Add the primary color to the return data
			$r .= $primary_color . ', ';
		}
		
		$r .= $alpha . ')';
		
		return $r;
	}
}

/**
 * This function registers all color schemes available in this theme.
 */
if ( ! function_exists( 'sds_color_schemes' ) ) {
	function sds_color_schemes() {
		$color_schemes = array(
			// Default (any additional color schemes should contain all of these properties as well as a 'deps' property)
			'default' => array( // Name used in saved option
				'label' => __( 'Default', 'baton-pro' ), // Label on options panel (required)
				'stylesheet' => false, // Stylesheet URL, relative to theme directory (required)
				'preview' => '#7cb2c2', // Preview color on options panel (required)
				'default' => true,
				// Customizer
				'background_color' => '#f1f5f9', // Default background color
				'primary_color' => '#ffffff', // Default primary color
				'secondary_color' => '#ffffff', // Default secondary color
				'content_color' => '#4c5357', // Default content color
				'link_color' => '#3ebbbb', // Default link color
				'baton_post_title_color' => '#363a42', // Default post title color
				'baton_archive_title_color' => '#363a42', // Default archive title color
				'baton_button_text_color' => '#ffffff', // Default button text color
				'baton_button_hover_text_color' => '#ffffff', // Default button hover text color
				'baton_button_background_color' => '#3ebbbb', // Default button background color
				'baton_button_hover_background_color' => '#363a42', // Default button hover background color
				'baton_content_background_color' => '#ffffff', // Default content background color
				'baton_widget_title_color' => '#363a42', // Default widget title color
				'baton_widget_color' => '#4c5357', // Default widget title color
				'baton_widget_link_color' => '#3ebbbb', // Default widget title color
				'baton_site_title_color' => '#ffffff', // Default site title color
				'baton_tagline_color' => '#84919e', // Default tagline color
				'baton_primary_hover_active_color' => '#3ebbbb', // Default primary hover
				'baton_primary_sub_menu_color' => '#84919e', // Default primary navigation sub menu color
				'baton_primary_sub_menu_hover_color' => '#ffffff', // Default primary navigation sub menu hover color
				'baton_primary_sub_menu_background_color' => '#363a42', // Default primary navigation sub menu background color
				'baton_header_background_color' => '#363a42', // Default header background color
				'baton_secondary_hover_active_color' => '#ffffff', // Default secondary hover
				'baton_secondary_header_sub_menu_color' => '#ccf4f4', // Default secondary header navigation sub menu color
				'baton_secondary_header_sub_menu_hover_color' => '#ffffff', // Default secondary navigation sub menu hover color
				'baton_secondary_header_sub_menu_background_color' => '#3ebbbb', // Default secondary header navigation sub menu background color
				'baton_secondary_header_background_color' => '#3ebbbb', // Default secondary header background color
				'baton_footer_text_color' => '#acacac', // Default footer text color
				'baton_footer_link_color' => '#3ebbbb', // Default footer link color
				'baton_footer_heading_color' => '#ffffff', // Default footer widget title color
				'baton_footer_background_color' => '#363a42', // Default footer background color
			),
			// Blue
			'blue' => array(
				'label' => __( 'Blue', 'baton-pro' ),
				'stylesheet' => '/css/blue.css',
				'preview' => '#3f70d4',
				'deps' => 'baton',
				// Customizer
				'background_color' => '#ffffff', // Default background color
				'primary_color' => '#747f81', // Default primary color
				'secondary_color' => '#b6c3de', // Default secondary color
				'content_color' => '#282629', // Default content color
				'link_color' => '#3f70d4', // Default link color
				'baton_post_title_color' => '#282629', // Default post title color
				'baton_archive_title_color' => '#282629', // Default archive title color
				'baton_button_text_color' => '#ffffff', // Default button text color
				'baton_button_hover_text_color' => '#ffffff', // Default button hover text color
				'baton_button_background_color' => '#3f70d4', // Default button background color
				'baton_button_hover_background_color' => '#282629', // Default button hover background color
				'baton_content_background_color' => '#f1f4f9', // Default content background color
				'baton_widget_title_color' => '#282629', // Default widget title color
				'baton_widget_color' => '#282629', // Default widget title color
				'baton_widget_link_color' => '#3f70d4', // Default widget title color
				'baton_site_title_color' => '#282629', // Default site title color
				'baton_tagline_color' => '#84919e', // Default tagline color
				'baton_primary_hover_active_color' => '#3f70d4', // Default primary hover
				'baton_primary_sub_menu_color' => '#747f81', // Default primary navigation sub menu color
				'baton_primary_sub_menu_hover_color' => '#3f70d4', // Default primary navigation sub menu hover color
				'baton_primary_sub_menu_background_color' => '#f1f4f9', // Default primary navigation sub menu background color
				'baton_header_background_color' => '#f1f4f9', // Default header background color
				'baton_secondary_hover_active_color' => '#ffffff', // Default secondary hover
				'baton_secondary_header_sub_menu_color' => '#b6c3de', // Default secondary header navigation sub menu color
				'baton_secondary_header_sub_menu_hover_color' => '#ffffff', // Default secondary navigation sub menu hover color
				'baton_secondary_header_sub_menu_background_color' => '#3f70d4', // Default secondary header navigation sub menu background color
				'baton_secondary_header_background_color' => '#3f70d4', // Default secondary header background color
				'baton_footer_text_color' => '#747f81', // Default footer text color
				'baton_footer_link_color' => '#3f70d4', // Default footer link color
				'baton_footer_heading_color' => '#282629', // Default footer widget title color
				'baton_footer_background_color' => '#f1f4f9', // Default footer background color
			),
			// Green
			'green' => array(
				'label' => __( 'Green', 'baton-pro' ),
				'stylesheet' => '/css/green.css',
				'preview' => '#66bc7d',
				'deps' => 'baton',
				// Customizer
				'background_color' => '#f1f5f9', // Default background color
				'primary_color' => '#ffffff', // Default primary color
				'secondary_color' => '#ffffff', // Default secondary color
				'content_color' => '#323b48', // Default content color
				'link_color' => '#66bc7d', // Default link color
				'baton_post_title_color' => '#323b48', // Default post title color
				'baton_archive_title_color' => '#323b48', // Default archive title color
				'baton_button_text_color' => '#ffffff', // Default button text color
				'baton_button_hover_text_color' => '#ffffff', // Default button hover text color
				'baton_button_background_color' => '#66bc7d', // Default button background color
				'baton_button_hover_background_color' => '#323b48', // Default button hover background color
				'baton_content_background_color' => '#ffffff', // Default content background color
				'baton_widget_title_color' => '#323b48', // Default widget title color
				'baton_widget_color' => '#323b48', // Default widget title color
				'baton_widget_link_color' => '#66bc7d', // Default widget title color
				'baton_site_title_color' => '#ffffff', // Default site title color
				'baton_tagline_color' => '#849e8a', // Default tagline color
				'baton_primary_hover_active_color' => '#66bc7d', // Default primary hover
				'baton_primary_sub_menu_color' => '#ffffff', // Default primary navigation sub menu color
				'baton_primary_sub_menu_hover_color' => '#66bc7d', // Default primary navigation sub menu hover color
				'baton_primary_sub_menu_background_color' => '#323b48', // Default primary navigation sub menu background color
				'baton_header_background_color' => '#323b48', // Default header background color
				'baton_secondary_hover_active_color' => '#323b48', // Default secondary hover
				'baton_secondary_header_sub_menu_color' => '#ffffff', // Default secondary header navigation sub menu color
				'baton_secondary_header_sub_menu_hover_color' => '#323b48', // Default secondary navigation sub menu hover color
				'baton_secondary_header_sub_menu_background_color' => '#66bc7d', // Default secondary header navigation sub menu background color
				'baton_secondary_header_background_color' => '#66bc7d', // Default secondary header background color
				'baton_footer_text_color' => '#b6b9be', // Default footer text color
				'baton_footer_link_color' => '#66bc7d', // Default footer link color
				'baton_footer_heading_color' => '#ffffff', // Default footer widget title color
				'baton_footer_background_color' => '#323b48', // Default footer background color
			),
			// Red
			'red' => array(
				'label' => __( 'Red', 'baton-pro' ),
				'stylesheet' => '/css/red.css',
				'preview' => '#ff5b5d',
				'deps' => 'baton',
				// Customizer
				'background_color' => '#ffffff', // Default background color
				'primary_color' => '#4c5a5d', // Default primary color
				'secondary_color' => '#d6dce1', // Default secondary color
				'content_color' => '#0a3039', // Default content color
				'link_color' => '#ff5b5d', // Default link color
				'baton_post_title_color' => '#0a3039', // Default post title color
				'baton_archive_title_color' => '#0a3039', // Default archive title color
				'baton_button_text_color' => '#ffffff', // Default button text color
				'baton_button_hover_text_color' => '#ffffff', // Default button hover text color
				'baton_button_background_color' => '#ff5b5d', // Default button background color
				'baton_button_hover_background_color' => '#0a3039', // Default button hover background color
				'baton_content_background_color' => '#f8f8f8', // Default content background color
				'baton_widget_title_color' => '#000000', // Default widget title color
				'baton_widget_color' => '#4c5a5d', // Default widget title color
				'baton_widget_link_color' => '#4c5a5d', // Default widget title color
				'baton_site_title_color' => '#0a3039', // Default site title color
				'baton_tagline_color' => '#9e8484', // Default tagline color
				'baton_primary_hover_active_color' => '#ff5b5d', // Default primary hover
				'baton_primary_sub_menu_color' => '#4c5a5d', // Default primary navigation sub menu color
				'baton_primary_sub_menu_hover_color' => '#ff5b5d', // Default primary navigation sub menu hover color
				'baton_primary_sub_menu_background_color' => '#f8f8f8', // Default primary navigation sub menu background color
				'baton_header_background_color' => '#f8f8f8', // Default header background color
				'baton_secondary_hover_active_color' => '#ffffff', // Default secondary hover
				'baton_secondary_header_sub_menu_color' => '#d6dce1', // Default secondary header navigation sub menu color
				'baton_secondary_header_sub_menu_hover_color' => '#ffffff', // Default secondary navigation sub menu hover color
				'baton_secondary_header_sub_menu_background_color' => '#ff5b5d', // Default secondary header navigation sub menu background color
				'baton_secondary_header_background_color' => '#ff5b5d', // Default secondary header background color
				'baton_footer_text_color' => '#878e90', // Default footer text color
				'baton_footer_link_color' => '#ff5b5d', // Default footer link color
				'baton_footer_heading_color' => '#0a3039', // Default footer widget title color
				'baton_footer_background_color' => '#f8f8f8', // Default footer background color
			)
		);

		return apply_filters( 'sds_theme_options_color_schemes', $color_schemes );
	}
}

/**
 * This function outputs a fallback menu and is used when the Primary Menu
 * is inactive.
 */
if ( ! function_exists( 'sds_primary_menu_fallback' ) ) {
	function sds_primary_menu_fallback() {
		wp_page_menu( array(
			'menu_class'  => 'primary-nav menu',
			'echo'        => true,
			'show_home'   => true,
			'link_before' => '<span>',
			'link_after' => '</span>'
		) );
	}
}

/**
 * This function outputs a fallback menu for mobile devices and is used when the Primary Menu
 * is inactive.
 */
if ( ! function_exists( 'baton_mobile_primary_menu_fallback' ) ) {
	function baton_mobile_primary_menu_fallback() {
		wp_page_menu( array(
			'menu_class'  => 'primary-nav primary-nav-mobile menu',
			'echo'        => true,
			'show_home'   => true,
			'link_before' => '<span>',
			'link_after' => '</span>'
		) );
	}
}

/**
 * This function checks to see if Yoast Breadcrumbs are active.
 */
function baton_is_yoast_breadcrumbs_active() {
	return ( function_exists( 'yoast_breadcrumb' ) && ( ( $wpseo_internallinks = get_option( 'wpseo_internallinks' ) ) && isset( $wpseo_internallinks['breadcrumbs-enable'] ) && $wpseo_internallinks['breadcrumbs-enable'] ) );
}

/**
 * This function determines if the current page as the Baton Landing Page - Conductor
 * content layout applied.
 */
function baton_is_conductor_baton_landing_page( $conductor_content_layout = false ) {
	// Bail if Conductor isn't active or this page doesn't have a Conductor content layout
	if ( ! class_exists( 'Conductor' ) || ! Conductor::is_conductor() )
		return false;

	// Grab the Conductor content layout for this page (if necessary)
	$conductor_content_layout = ( ! $conductor_content_layout ) ? Conductor::get_conductor_content_layout() : $conductor_content_layout;

	// If this is the Baton Landing Page content layout
	return ( $conductor_content_layout['value'] === 'baton-landing-page' );
}

/**
 * This function determines if a Baton Note Sidebar is active on the current piece of content.
 * If a sidebar ID parameter is passed only that specific sidebar will be checked, otherwise
 * any/all sidebars will be checked until an active sidebar is discovered.
 */
function baton_is_note_baton_sidebar_active( $sidebar_id = false, $post_id = false ) {
	// Bail if Note doesn't exist
	if ( ! class_exists( 'Note' ) )
		return false;

	// Grab the Baton instance
	$baton_instance = Baton_Instance();

	// Grab the Note Sidebars instance
	$note_sidebars = Note_Sidebars();

	// If we have a sidebar ID, check for that specific sidebar only
	if ( $sidebar_id ) {
		// Grab the correct "post_id" for this content piece
		$note_post_id = ( $post_id ) ? $post_id : $baton_instance->note_customizer_sidebar_args_post_id( '', false, $note_sidebars );

		// Grab the Note sidebar ID for this sidebar and current content piece
		$note_sidebar_id = Note_Sidebars::get_sidebar_id( $sidebar_id, $note_post_id );

		return ( is_active_sidebar( $note_sidebar_id ) );
	}
	// Otherwise, we're just checking to see if at least one sidebar is active
	else {
		// Grab the correct "post_id" for this content piece
		$note_post_id = ( $post_id ) ? $post_id : $baton_instance->note_customizer_sidebar_args_post_id( '', false, $note_sidebars );

		// If we have Note Baton Sidebars
		if ( ! empty( $baton_instance->note_sidebar_ids ) ) {
			// Flag to determine if sidebar is active
			$is_active_sidebar = false;

			// Loop through Note Baton sidebar IDs
			foreach ( $baton_instance->note_sidebar_ids as $note_sidebar_id ) {
				// Grab the Note sidebar ID for this sidebar and current content piece
				$note_sidebar_id = Note_Sidebars::get_sidebar_id( $note_sidebar_id, $note_post_id );

				// Set the active flag
				$is_active_sidebar = is_active_sidebar( $note_sidebar_id );

				// If the sidebar is active
				if ( $is_active_sidebar )
					break;
			}

			// If we have an active sidebar
			return $is_active_sidebar;
		}
		else
			return false;
	}
}


/***************
 * WooCommerce *
 ***************/

/**
 * This function outputs WooCommerce pagination
 */
if ( ! function_exists( 'woocommerce_pagination' ) ) {
	function woocommerce_pagination() {
		get_template_part( 'loop', 'navigation' ); // Loop - Navigation
	}
}


/**
 * Load the theme function files (options panel, theme functions, widgets, etc...).
 */

include_once get_template_directory() . '/theme/class-baton.php'; // Baton Class (main functionality, actions/filters)

include_once get_template_directory() . '/includes/class-tgm-plugin-activation.php'; // TGM Activation

include_once get_template_directory() . '/includes/theme-options.php'; // SDS Theme Options
include_once get_template_directory() . '/includes/theme-functions.php'; // SDS Theme Options Functions
include_once get_template_directory() . '/includes/widget-social-media.php'; // SDS Social Media Widget

include_once get_template_directory() . '/theme/class-baton-customizer.php'; // Baton Customizer Class (specific to the customizer)

// Theme Updates
if ( ! class_exists( 'EDD_SL_Theme_Updater' ) )
	include_once get_template_directory() . '/includes/class-edd-sl-theme-updater.php';
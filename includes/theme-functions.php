<?php

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;


/**
 * SDS Theme Options Functions
 *
 * Description: This file contains functions for utilizing options within themes (displaying site logo, tagline, etc...)
 *
 * @version 1.4.2
 */


// Globalize Theme options
$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();


/***********************
 * Pluggable Functions *
 ***********************/

/**
 * This function displays either a logo or the site title depending on options.
 *
 * @uses site_url()
 * @uses get_bloginfo()
 * @uses wp_get_attachment_image()
 * @uses bloginfo()
 */
if ( ! function_exists( 'sds_logo' ) ) {
	function sds_logo() {
		global $sds_theme_options;

		// Determine HTML wrapper element
		$sds_logo_wrapper_el = ( is_front_page() || is_home() ) ? 'h1' : 'p';
		$sds_logo_wrapper_el = apply_filters( 'sds_logo_wrapper_element', $sds_logo_wrapper_el );

		// Logo
		if ( ! empty( $sds_theme_options['logo_attachment_id'] ) ) :
	?>
		<<?php echo $sds_logo_wrapper_el; ?> id="title" class="site-title site-title-logo has-logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php echo wp_get_attachment_image( $sds_theme_options['logo_attachment_id'], 'full' ); ?>
			</a>
		</<?php echo $sds_logo_wrapper_el; ?>>
	<?php
		// No logo
		else :
	?>
		<<?php echo $sds_logo_wrapper_el; ?> id="title" class="site-title site-title-no-logo no-logo">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				<?php bloginfo( 'name' ); ?>
			</a>
		</<?php echo $sds_logo_wrapper_el; ?>>
	<?php
		endif;
	}
}

/**
 * This function displays the site tagline, optionally with a CSS class to hide it depending on options.
 *
 * @uses bloginfo()
 */
if ( ! function_exists( 'sds_tagline' ) ) {
	function sds_tagline() {
		global $sds_theme_options;

		// Determine HTML wrapper element
		$sds_tagline_wrapper_el = ( is_front_page() || is_home() ) ? 'h2' : 'p';
		$sds_tagline_wrapper_el = apply_filters( 'sds_tagline_wrapper_element', $sds_tagline_wrapper_el );
	?>
		<<?php echo $sds_tagline_wrapper_el; ?> id="slogan" class="slogan <?php echo ( $sds_theme_options['hide_tagline'] ) ? 'hide hidden hide-tagline hide-slogan' : false; ?>">
			<?php bloginfo( 'description' ); ?>
		</<?php echo $sds_tagline_wrapper_el; ?>>
	<?php
	}
}

/**
 * This function displays featured images based on options.
 *
 * @param $link_image, Boolean, link featured image to post.
 *
 * @uses the_permalink()
 * @uses has_post_thumbnail()
 * @uses the_post_thumbnail()
 */
if ( ! function_exists( 'sds_featured_image' ) ) {
	function sds_featured_image( $link_image = false, $size = false ) {
		global $sds_theme_options;

		// Allow size to be over-written by function call
		if ( $size )
			$featured_image_size = $size;
		else {
			$default_featured_image_size = apply_filters( 'sds_theme_options_default_featured_image_size', '' );
			$featured_image_size = ( isset( $sds_theme_options['featured_image_size'] ) && ! empty( $sds_theme_options['featured_image_size'] ) ) ? $sds_theme_options['featured_image_size'] : $default_featured_image_size;
		}

		$featured_image_size = apply_filters( 'sds_featured_image_size', $featured_image_size, $link_image );

		// CSS classes
		$css_classes = array(
			'post-image',
			'featured-image',
			'post-thumbnail',
			$featured_image_size . '-featured-image ',
			$featured_image_size . '-post-image',
			( $link_image ) ? 'has-link' : 'no-link'
		);

		// Sanitize CSS classes
		$css_classes = array_filter( $css_classes, 'sanitize_html_class' );

		// Featured Image
		if ( has_post_thumbnail() && $link_image ) :
	?>
		<figure class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
			<a href="<?php the_permalink(); ?>">
				<?php the_post_thumbnail( $featured_image_size ); ?>
			</a>
		</figure>
	<?php
		elseif ( has_post_thumbnail() ) :
	?>
		<figure class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
			<?php the_post_thumbnail( $featured_image_size ); ?>
		</figure>
	<?php
		endif;
	}
}

/**
 * This function adds the current site name after the title in the <head> section.
 */
if ( ! function_exists( 'sds_wp_title' ) && ! function_exists( '_wp_render_title_tag' ) ) {
	add_filter( 'wp_title', 'sds_wp_title' );

	function sds_wp_title( $title ) {
		// Ignore on feeds
		if ( ! is_feed() )
			$title .= get_bloginfo( 'name' );

		return $title;
	}
}

/**
 * This function outputs a fallback menu and is used when the Primary Menu is inactive.
 */
if ( ! function_exists( 'sds_primary_menu_fallback' ) ) {
	function sds_primary_menu_fallback() {
		wp_page_menu( array(
			'menu_class'  => 'primary-nav menu',
			'echo'        => true,
			'show_home'   => true,
			'link_before' => '',
			'link_after'  => ''
		) );
	}
}

/**
 * This function outputs a sitemap (most typically found on a 404 template).
 */
if ( ! function_exists( 'sds_sitemap' ) ) {
	function sds_sitemap() {
		global $post;
	?>
		<div class="sds-sitemap sitemap">
			<?php if ( apply_filters( 'sds_sitemap_show_pages', true ) ) : // Allow pages to not be displayed ?>
				<div class="sitemap-pages page-list">
					<h2 title="<?php esc_attr_e( 'Pages', 'baton' ); ?>"><?php _e( 'Pages', 'baton' ); ?></h2>
					<ul>
						<?php wp_list_pages( array( 'title_li' => '' ) ); ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( apply_filters( 'sds_sitemap_show_monthly_archives', true ) ) : // Allow monthly archives to not be displayed ?>
				<div class="sitemap-archives sitemap-monthly-archives monthly-archives archive-list">
					<h2 title="<?php esc_attr_e( 'Monthly Archives', 'baton' ); ?>"><?php _e( 'Monthly Archives', 'baton' ); ?></h2>
					<ul>
						<?php echo apply_filters( 'sds_sitemap_monthly_archives', wp_get_archives( array( 'echo' => false ) ) ); ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( apply_filters( 'sds_sitemap_show_categories', true ) ) : // Allow categories to not be displayed ?>
				<div class="sitemap-categories category-list">
					<h2 title="<?php esc_attr_e( 'Blog Categories', 'baton' ); ?>"><?php _e( 'Blog Categories', 'baton' ); ?></h2>
					<ul>
						<?php wp_list_categories( array( 'title_li' => '' ) ); ?>
					</ul>
				</div>
			<?php endif; ?>


			<?php
				// Allow post types to be filtered
				$public_post_types = apply_filters( 'sds_sitemap_public_post_types', get_post_types( array( 'public' => true ) ) );

				// Output all public post types except attachments and pages (see above for pages)
				if ( ! empty( $public_post_types ) )
					foreach ( $public_post_types as $post_type ) :
						// Skip attachments and pages
						if ( ! in_array( $post_type, array( 'attachment', 'page' ) ) ) :
							$post_type_object = get_post_type_object( $post_type );

							$query = new WP_Query( array(
								'post_type' => $post_type,
								'posts_per_page' => wp_count_posts( $post_type )->publish
							) );

							if ( $query->have_posts() ) :
							?>
								<div class="sitemap-post-type-list sitemap-<?php echo $post_type_object->name; ?>-list post-type-list">
									<h2 title="<?php echo esc_attr( $post_type_object->labels->name ); ?>">
										<?php echo $post_type_object->labels->name; ?>
									</h2>

									<ul>
										<?php while( $query->have_posts() ) : $query->the_post(); ?>
											<li id="<?php echo esc_attr( $post_type . '-' . $post->ID ); ?>">
												<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
											</li>
										<?php endwhile; ?>
									</ul>
								</div>
							<?php
							endif;
						endif;
					endforeach;
			?>
		</div>
	<?php
	}
}

/**
 * This function outputs a title for Archive page templates.
 */
if ( ! function_exists( 'sds_archive_title' ) ) {
	function sds_archive_title() {
		global $sds_theme_options;

		// Determine if we should we output the title based on settings
		if ( $sds_theme_options['hide_archive_titles'] )
			return false;

		// Use core functionality if it exists
		if ( function_exists( 'the_archive_title' ) ) :
			$css_class = 'page-title';

			// Author
			if ( is_author() )
				$css_class .= ' author-archive-title';
			// Categories
			else if ( is_category() )
				$css_class .= ' category-archive-title';
			// Tags
			else if ( is_tag() )
				$css_class .= ' tag-archive-title';
			// Daily Archives
			else if ( is_day() )
				$css_class .= ' day-archive-title daily-archive-title';
			// Monthly Archives
			else if ( is_month() )
				$css_class .= ' month-archive-title monthly-archive-title';
			// Yearly Archives
			else if ( is_year() )
				$css_class .= ' year-archive-title yearly-archive-title';


			$css_class = apply_filters( 'sds_archive_title_css_class', $css_class );

			the_archive_title( '<h1 class="' . esc_attr( $css_class ) . '">', '</h1>' );
		// Otherwise use fallback functionality TODO: Remove
		else :
			// Author
			if ( is_author() ) :
				$author = get_user_by( 'slug', get_query_var( 'author_name' ) ); // Get user data by slug with value of author_name in query
			?>
				<h1 title="<?php esc_attr_e( 'Author Archive:', 'baton' ); ?> <?php echo ( $author instanceof WP_User ) ? $author->display_name : false; ?>" class="page-title author-archive-title">
					<?php _e( 'Author Archive:', 'baton' ); ?> <?php echo ( $author instanceof WP_User ) ? $author->display_name : false; ?>
				</h1>
			<?php
			// Categories
			elseif ( is_category() ) :
			?>
				<h1 title="<?php single_cat_title( __( 'Category Archive: ', 'baton' ) ); ?>" class="page-title category-archive-title">
					<?php single_cat_title( __( 'Category Archive: ', 'baton' ) ); ?>
				</h1>
			<?php
			// Tags
			elseif ( is_tag() ) :
			?>
				<h1 title="<?php single_tag_title( __( 'Tag Archive: ', 'baton' ) ); ?>" class="page-title tag-archive-title">
					<?php single_tag_title( __( 'Tag Archive: ', 'baton' ) ); ?>
				</h1>
			<?php
			// Daily Archives
			elseif ( is_day() ) :
				$the_date = get_the_date();
			?>
				<h1 title="<?php esc_attr_e( 'Daily Archives:', 'baton' ); ?> <?php echo $the_date; ?>" class="page-title day-archive-title daily-archive-title">
					<?php _e( 'Daily Archives:', 'baton' ); ?> <?php echo $the_date; ?>
				</h1>
			<?php
			// Monthly Archives
			elseif ( is_month() ) :
				$the_date = get_the_date( 'F Y' );
			?>
				<h1 title="<?php esc_attr_e( 'Monthly Archives:', 'baton' ); ?> <?php echo $the_date; ?>" class="page-title month-archive-title monthly-archive-title">
					<?php _e( 'Monthly Archives:', 'baton' ); ?> <?php echo $the_date; ?>
				</h1>
			<?php
			// Yearly Archives
			elseif ( is_year() ) :
				$the_date = get_the_date( 'Y' );
			?>
				<h1 title="<?php esc_attr_e( 'Yearly Archives:', 'baton' ); ?> <?php echo $the_date; ?>" class="page-title year-archive-title yearly-archive-title">
					<?php _e( 'Yearly Archives:', 'baton' ); ?> <?php echo $the_date; ?>
				</h1>
			<?php
			endif;
		endif;
	}
}

/**
 * This function outputs a "no posts" message when no posts are found in The Loop.
 */
if ( ! function_exists( 'sds_no_posts' ) ) {
	function sds_no_posts() {
	?>
		<section class="no-results no-posts">
			<p><?php _e( 'We were not able to find any posts. Please try again.', 'baton' ); ?></p>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single posts.
 */
if ( ! function_exists( 'sds_single_post_navigation' ) ) {
	function sds_single_post_navigation() {
	?>
		<section class="single-post-navigation post-navigation">
			<section class="previous-posts">
				<?php next_post_link( '%link', '&laquo; %title' ); ?>
			</section>
			<section class="next-posts">
				<?php previous_post_link( '%link', '%title &raquo;' ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs next/prev navigation on single image attachments.
 */
if ( ! function_exists( 'sds_single_image_navigation' ) ) {
	function sds_single_image_navigation() {
	?>
		<section class="single-post-navigation post-navigation single-image-navigation image-navigation">
			<section class="previous-posts">
				<?php previous_image_link( false, __( '&laquo; Previous Image', 'baton' ) ); ?>
			</section>
			<section class="next-posts">
				<?php next_image_link( false, __( 'Next Image &raquo;', 'baton' ) ); ?>
			</section>
		</section>
	<?php
	}
}

/**
 * This function outputs the site's copyright as well as the SDS copyright.
 */
if ( ! function_exists( 'sds_copyright' ) ) {
	function sds_copyright() {
		global $sds_theme_options;

		// Should the footer branding be shown?
		if ( ! $sds_theme_options['footer']['hide_branding'] ) :
	?>
			<span class="site-copyright">
				<?php
					// If the footer copyright option is not empty
					if ( ! empty( $sds_theme_options['footer']['copyright'] ) )
						echo $sds_theme_options['footer']['copyright'];
					// Show default copyright if it isn't
					else
						echo sds_get_copyright();
				?>
			</span>
			<span class="slocum-credit">
				<?php
						// If the footer branding option is not empty
						if ( ! empty( $sds_theme_options['footer']['branding'] ) )
							echo $sds_theme_options['footer']['branding'];
						// Show default branding if it isn't
						else
							echo sds_get_copyright_branding();
				?>
			</span>
	<?php
		endif;
	}
}

/**
 * This function outputs a list of selected social networks based on options. Can be called throughout the theme and is used in the Social Media Widget.
 */
if ( ! function_exists( 'sds_social_media' ) ) {
	function sds_social_media() {
		global $sds_theme_options;

		if ( ! empty( $sds_theme_options['social_media'] ) ) {
			// Map the correct values for social icon display (FontAwesome webfont, i.e. 'fa-rss' = RSS icon)
			$social_font_map = array(
				'facebook_url' => 'fa fa-facebook',
				'twitter_url' => 'fa fa-twitter',
				'linkedin_url' => 'fa fa-linkedin',
				'google_plus_url' => 'fa fa-google-plus',
				'youtube_url' => 'fa fa-youtube',
				'vimeo_url' => 'fa fa-vimeo-square', // previously fa-play
				'pinterest_url' => 'fa fa-pinterest',
				'instagram_url' => 'fa fa-instagram',
				'flickr_url' => 'fa fa-flickr',
				//'yelp_url' => '',
				'foursquare_url' => 'fa fa-foursquare',
				'rss_url' => 'fa fa-rss'
			);

			$social_font_map = apply_filters( 'sds_social_icon_map', $social_font_map );
		?>
			<section class="social-media-icons">
			<?php
				foreach ( $sds_theme_options['social_media'] as $key => $url ) :
					// RSS (use site RSS feed, $url is Boolean this case)
					if ( $key === 'rss_url_use_site_feed' && $url ) :
					?>
						<a href="<?php bloginfo( 'rss2_url' ); ?>" class="<?php echo esc_attr( 'rss_url ' . $social_font_map['rss_url'] ); ?>" target="_blank"></a>
					<?php
					// RSS (use custom RSS feed)
					elseif ( $key === 'rss_url' && ! $sds_theme_options['social_media']['rss_url_use_site_feed'] && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_attr( $url ); ?>" class="<?php echo esc_attr( 'rss_url ' . $social_font_map['rss_url'] ); ?>" target="_blank"></a>
					<?php
					// All other networks
					elseif ( $key !== 'rss_url_use_site_feed' && $key !== 'rss_url' && ! empty( $url ) ) :
					?>
						<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr(  $key . ' ' . $social_font_map[$key] ); ?>" target="_blank" rel="me"></a>
					<?php
					endif;
				endforeach;
			?>
			</section>
		<?php
		}
	}
}

/**
 * This function displays meta for the current post (including categories and tags).
 */
if ( ! function_exists( 'sds_post_meta' ) ) {
	function sds_post_meta() {
		global $sds_theme_options;

		// Determine if we should we output the post meta based on settings
		if( $sds_theme_options['hide_post_meta'] )
			return;

		$cats = get_the_category();
		$tags = get_the_tags();

		// Categories and tags
		if ( $cats && $tags ):
		?>
			<p class="post-meta has-categories has-tags">
			<?php
				printf( __( 'This entry was posted in %1$s and tagged in %2$s.', 'baton' ),
				get_the_category_list( ', ', 'single' ),
				get_the_tag_list( '', ', ' ) );
			?>
			</p>
		<?php
		// Categories and no tags
		elseif ( $cats && ! $tags ) :
		?>
			<p class="post-meta has-categories no-tags">
			<?php
				printf( __( 'This entry was posted in %1$s.', 'baton' ),
				get_the_category_list( ', ', 'single' ) );
			?>
			</p>
		<?php
		// Tags and no categories
		elseif ( $tags && ! $cats ) :
		?>
			<p class="post-meta no-categories has-tags">
			<?php
				printf( __( 'This entry was tagged in %1$s.', 'baton' ),
				get_the_tag_list( '', ', ' ) );
			?>
			</p>
		<?php
		endif;
	}
}


/**
 * This function displays pagination links based on arguments.
 * @uses paginate_links for output
 */
if ( ! function_exists( 'sds_post_navigation' ) ) {
	function sds_post_navigation( $return = false ) {
		global $wp_query;

		$pagination_links = paginate_links( array(
			'base' => esc_url( get_pagenum_link() ) . '%_%', // %_% will be replaced with format below
			'format' => ( ( get_option( 'permalink_structure' ) && ! $wp_query->is_search ) || ( is_home() && get_option( 'show_on_front' ) !== 'page' && ! get_option( 'page_on_front' ) ) ) ? '?paged=%#%' : '&paged=%#%', // %#% will be replaced with page number
			'current' => max( 1, get_query_var( 'paged' ) ), // Get whichever is the max out of 1 and the current page count
			'total' => $wp_query->max_num_pages, // Get total number of pages in current query
			'next_text' => __( 'Next &#8594;', 'baton' ),
			'prev_text' => __( '&#8592; Previous', 'baton' ),
			'type' => ( $return ) ? 'array' : 'list'  // Output this as an array or unordered list
		) );

		if ( $return )
			return $pagination_links;
		else
			echo $pagination_links;
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
if ( ! function_exists( 'sds_comment' ) ) {
	function sds_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
			// Display trackbacks differently than normal comments.
		?>
		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<p><?php _e( 'Pingback:', 'baton' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( 'Edit', '<span class="ping-meta"><span class="edit-link">', '</span></span>' ); ?></p>
		</li>
		<?php
			break;
			default :
			// Proceed with normal comments.
		?>
		<li id="li-comment-<?php comment_ID(); ?>">
			<article id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
				<section class="comment-author vcard">
					<section class="author-details">
						<?php echo get_avatar( $comment, 74 ); ?>
						<span class="author-link"><?php comment_author_link(); ?></span>
						<br />
						<header class="comment-meta">
							<cite class="fn">
								<?php
									printf( __( '<a href="%1$s"><time datetime="%2$s" itemprop="commentTime">%3$s</time></a>', 'baton' ),
										esc_url( get_comment_link( $comment->comment_ID ) ),
										get_comment_time( 'c' ),
										sprintf( __( '%1$s at %2$s', 'baton' ), get_comment_date(), get_comment_time() )
									);
								?>

								<?php edit_comment_link( __( 'Edit', 'baton' ), '<span class="edit-link">', '<span>' ); ?>
							</cite>
						</header>
					</section>
				</section>

				<section class="comment-content-container">
					<?php if ( $comment->comment_approved == '0' ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'baton' ); ?></p>
					<?php endif; ?>

					<section class="comment-content">
						<?php comment_text(); ?>
					</section>
				</section>

				<section class="clear">&nbsp;</section>

				<section class="reply">
					<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'baton' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
				</section>
			</article>
		</li>
		<?php
			break;
		endswitch;
	}
}


/**************
 * Customizer *
 **************/

/**
 * This function adds settings, sections, and controls to the Customizer.
 *
 * Each theme handles the output of the styles in the wp_head action (usually in functions.php).
 * Each theme also handles filters in their respected Class Files (/includes/ThemeName.php).
 */
add_action( 'customize_register', 'sds_customize_register', 20 );

function sds_customize_register( $wp_customize ) {
	// Load custom Customizer API assets
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-checkbox-control.php'; // Checkbox Controller
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-color-scheme-control.php'; // Color Scheme Controller
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-content-layout-control.php'; // Content Layout Controller
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-logo-control.php'; // Logo Controller
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-textarea-control.php'; // Textarea Controller
	include_once SDS_Theme_Options::sds_core_dir() . '/customizer/class-sds-theme-options-customize-web-font-control.php'; // Web Font Controller


	// SDS Theme Options defaults
	$sds_theme_options_instance = SDS_Theme_Options_Instance();
	$sds_theme_options_defaults = $sds_theme_options_instance->get_sds_theme_option_defaults();


	// SDS Theme Options backwards compatibility check (only in the admin; Customizer, not Previewer)
	if ( is_admin() )
		add_filter( 'option_sds_theme_options', 'sds_option_sds_theme_options' );

	/**
	 * Logo Upload
	 */

	// Setting (data is sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance)
	$wp_customize->add_setting(
		'sds_theme_options[logo_attachment_id]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['logo_attachment_id'],
			'type' => 'option',
			'sanitize_callback' => 'absint'
		)
	);

	// Section - overwrite the default title_tagline section properties
	$wp_customize->get_section( 'title_tagline' )->title = __( 'Logo/Site Title & Tagline', 'baton' );

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Logo_Control(
			$wp_customize,
			'logo_attachment_id',
			array(
				'label' => __( 'Logo', 'baton' ),
				'section'  => 'title_tagline',
				'settings' => 'sds_theme_options[logo_attachment_id]',
				'type' => 'sds_theme_options_logo' // Used in js controller
			)
		)
	);


	/**
	 * Show/Hide Elements
	 */
	// Section
	$wp_customize->add_section( 'sds_theme_options_show_hide', array(
		'priority' => 30, // After Title & Tagline
		'title' => __( 'Show or Hide Elements', 'baton' ),
		'description' => __( 'Use these options to show or hide various elements on your site.', 'baton' )
	) );

	/*
	 * Show/Hide Tagline
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[hide_tagline]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['hide_tagline'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[hide_tagline]', // IDs can have nested array keys
			array(
				'label' => __( 'Tagline', 'baton' ),
				'section'  => 'sds_theme_options_show_hide',
				'settings' => 'sds_theme_options[hide_tagline]',
				'priority' => 10,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-show-hide-tagline',
				'css_id' => 'sds_theme_options_hide_tagline'
			)
		)
	);

	/*
	 * Show/Hide Archive Titles
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[hide_archive_titles]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['hide_archive_titles'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[hide_archive_titles]', // IDs can have nested array keys
			array(
				'label' => __( 'Archive Titles', 'baton' ),
				'section'  => 'sds_theme_options_show_hide',
				'settings' => 'sds_theme_options[hide_archive_titles]',
				'priority' => 20,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-show-hide-archive-titles',
				'css_id' => 'sds_theme_options_hide_archive_titles'
			)
		)
	);

	/*
	 * Show/Hide Post Meta
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[hide_post_meta]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['hide_post_meta'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[hide_post_meta]', // IDs can have nested array keys
			array(
				'label' => __( 'Post Meta', 'baton' ),
				'section'  => 'sds_theme_options_show_hide',
				'settings' => 'sds_theme_options[hide_post_meta]',
				'priority' => 30,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-show-hide-post-meta',
				'css_id' => 'sds_theme_options_hide_post_meta'
			)
		)
	);

	/*
	 * Show/Hide Author Details
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[hide_author_meta]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['hide_author_meta'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[hide_author_meta]', // IDs can have nested array keys
			array(
				'label' => __( 'Author Meta', 'baton' ),
				'section'  => 'sds_theme_options_show_hide',
				'settings' => 'sds_theme_options[hide_author_meta]',
				'priority' => 40,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-show-hide-author-meta',
				'css_id' => 'sds_theme_options_hide_author_meta'
			)
		)
	);


	/**
	 * Show/Hide Footer Copyright/Branding
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[footer][hide_branding]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['footer']['hide_branding'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[footer][hide_branding]', // IDs can have nested array keys
			array(
				'label' => __( 'Footer Copyright &amp; Branding', 'baton' ),
				'section'  => 'sds_theme_options_show_hide',
				'settings' => 'sds_theme_options[footer][hide_branding]',
				'priority' => 50,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-show-hide-footer-branding',
				'css_id' => 'sds_theme_options_hide_footer_branding'
			)
		)
	);


	/**
	 * Color Scheme (if specified by the theme)
	 */
	if ( function_exists( 'sds_color_schemes' ) ) {
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[color_scheme]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['color_scheme'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_html_class'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Color_Scheme_Control(
				$wp_customize,
				'sds_theme_options[color_scheme]', // IDs can have nested array keys
				array(
					'label' => __( 'Color Scheme', 'baton' ),
					'description' => __( 'Select a color scheme to use on your site.', 'baton' ),
					'section'  => 'colors',
					'settings' => 'sds_theme_options[color_scheme]',
					'priority' => 10,
					'type' => 'radio', // Used in js controller
					// TODO: Check to see what other Customizer controls we can add here, like background color (if that changes in our themes), etc...
					'color_controls' => apply_filters( 'sds_color_scheme_customizer_color_controls', array( 'primary_color', 'secondary_color', 'content_color', 'link_color' ), $wp_customize ),
				)
			)
		);
	}


	/**
	 * Primary Theme Color
	 */

	// Setting
	$wp_customize->add_setting(
		'primary_color',
		array(
			'default' => apply_filters( 'theme_mod_primary_color', '' ),
			'sanitize_callback' => 'sanitize_hex_color',
			'sanitize_js_callback' => 'maybe_hash_hex_color'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'primary_color',
			array(
				'label' => __( 'Primary Theme Color', 'baton' ),
				'section' => 'colors',
				'settings' => 'primary_color',
				'priority' => 10
			)
		)
	);


	/**
	 * Secondary Theme Color
	 */

	// Setting
	$wp_customize->add_setting(
		'secondary_color',
		array(
			'default' => apply_filters( 'theme_mod_secondary_color', '' ),
			'sanitize_callback' => 'sanitize_hex_color',
			'sanitize_js_callback' => 'maybe_hash_hex_color'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'secondary_color',
			array(
				'label' => __( 'Secondary Theme Color', 'baton' ),
				'section' => 'colors',
				'settings' => 'secondary_color',
				'priority' => 15
			)
		)
	);


	/**
	 * Content Color
	 */

	// Setting
	$wp_customize->add_setting(
		'content_color',
		array(
			'default' => apply_filters( 'theme_mod_content_color', '' ),
			'sanitize_callback' => 'sanitize_hex_color',
			'sanitize_js_callback' => 'maybe_hash_hex_color'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'content_color',
			array(
				'label' => __( 'Content Color', 'baton' ),
				'section' => 'colors',
				'settings' => 'content_color',
				'priority' => 20
			)
		)
	);


	/**
	 * Link Color
	 */

	// Setting
	$wp_customize->add_setting(
		'link_color',
		array(
			'default' => apply_filters( 'theme_mod_link_color', '' ),
			'sanitize_callback' => 'sanitize_hex_color',
			'sanitize_js_callback' => 'maybe_hash_hex_color'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'link_color',
			array(
				'label' => __( 'Link Color', 'baton' ),
				'section' => 'colors',
				'settings' => 'link_color',
				'priority' => 25
			)
		)
	);


	/*
	 * Background Color (change priority)
	 */
	$wp_customize->get_control( 'background_color' )->priority = 30; // Move below link_color


	/**
	 * Web Font (if specified by the theme)
	 */
	if ( function_exists( 'sds_web_fonts' ) ) {
		// Section
		$wp_customize->add_section( 'fonts', array(
			'priority' => 50, // After Colors
			'title' => __( 'Fonts', 'baton' ),
		) );

		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[web_font]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['web_font'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Web_Font_Control(
				$wp_customize,
				'sds_theme_options[web_font]', // IDs can have nested array keys
				array(
					'label' => __( 'Web Fonts', 'baton' ),
					'description' => __( 'Select a web font to use on your site.', 'baton' ),
					'section'  => 'fonts',
					'settings' => 'sds_theme_options[web_font]',
					'priority' => 10,
					'type' => 'radio' // Used in js controller
				)
			)
		);
	}


	/**
	 * Featured Image Size
	 */
	// Section
	$wp_customize->add_section( 'images', array(
		'priority' => 55, // After Fonts
		'title' => __( 'Images', 'baton' )
	) );

	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[featured_image_size]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['featured_image_size'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[featured_image_size]', // IDs can have nested array keys
			array(
				'label' => __( 'Featured Image Size', 'baton' ),
				'description' => __( 'Use this section to modify how featured images are displayed on your site.', 'baton' ),
				'section'  => 'images',
				'settings' => 'sds_theme_options[featured_image_size]',
				'priority' => 10,
				'type' => 'select', // Used in js controller
				'choices' => $sds_theme_options_instance->get_available_image_size_choices()
			)
		)
	);


	/**
	 * Content Layouts
	 */
	if ( function_exists( 'sds_content_layouts' ) ) {
		// Section
		$wp_customize->add_section( 'sds_theme_options_content_layouts', array(
			'priority' => 70, // After Header Image
			'title' => __( 'Content Layouts', 'baton' ),
			'description' => __( 'Control the layout of the content on your site here. Choose a global layout scheme to be used across your entire site or specify individual content type layout schemes by adjusting the options below.', 'baton' )
		) );


		/*
		 * Global Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][global]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['global'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][global]', // IDs can have nested array keys
				array(
					'label' => __( 'Global Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied globally on your site. Select more specific content layouts below.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][global]',
					'priority' => 10,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'global'
				)
			)
		);


		/*
		 * Front Page Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][front_page]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['front_page'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][front_page]', // IDs can have nested array keys
				array(
					'label' => __( 'Front Page Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to the front page on your site (if selected in Settings &gt; Reading).', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][front_page]',
					'priority' => 20,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'front_page'
				)
			)
		);


		/*
		 * Home Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][home]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['home'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][home]', // IDs can have nested array keys
				array(
					'label' => __( 'Home Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to the blog on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][home]',
					'priority' => 30,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'home'
				)
			)
		);


		/*
		 * Single Post Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][single]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['single'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][single]', // IDs can have nested array keys
				array(
					'label' => __( 'Single Post Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to single posts on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][single]',
					'priority' => 40,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'single'
				)
			)
		);


		/*
		 * Single Page Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][page]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['page'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][page]', // IDs can have nested array keys
				array(
					'label' => __( 'Single Page Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to single pages on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][page]',
					'priority' => 50,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'page'
				)
			)
		);


		/*
		 * Archive Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][archive]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['archive'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][archive]', // IDs can have nested array keys
				array(
					'label' => __( 'Archive Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to archives on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][archive]',
					'priority' => 60,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'archive'
				)
			)
		);


		/*
		 * Category Archive Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][category]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['category'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][category]', // IDs can have nested array keys
				array(
					'label' => __( 'Category Archive Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to category archives on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][category]',
					'priority' => 70,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'category'
				)
			)
		);


		/*
		 * Tag Archive Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][tag]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['tag'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][tag]', // IDs can have nested array keys
				array(
					'label' => __( 'Tag Archive Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to tag archives on your site.', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][tag]',
					'priority' => 80,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => 'tag'
				)
			)
		);


		/*
		 * 404 Error Content Layout
		 */
		// Setting
		$wp_customize->add_setting(
			'sds_theme_options[content_layouts][404]', // IDs can have nested array keys
			array(
				'default' => $sds_theme_options_defaults['content_layouts']['404'],
				'type' => 'option',
				// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
				'sanitize_callback' => 'sanitize_text_field'
			)
		);

		// Control
		$wp_customize->add_control(
			new SDS_Theme_Options_Customize_Content_Layout_Control(
				$wp_customize,
				'sds_theme_options[content_layouts][404]', // IDs can have nested array keys
				array(
					'label' => __( '404 Error Content Layout', 'baton' ),
					'description' => __( 'Select a content layout that will be applied to the 404 error page on your site. <strong>Please Note: The Customizer does not allow for previewing of 404 error pages.</strong>', 'baton' ),
					'section'  => 'sds_theme_options_content_layouts',
					'settings' => 'sds_theme_options[content_layouts][404]',
					'priority' => 90,
					'type' => 'radio', // Used in js controller
					'content_layout_id' => '404'
				)
			)
		);
	}

	/**
	 * Social Media
	 */
	// Section
	$wp_customize->add_section( 'sds_theme_options_social_media', array(
		'priority' => 75, // After Content Layouts
		'title' => __( 'Social Media', 'baton' ),
		'description' => __( 'Enter your social media links here. This section is used throughout the site to display social media links to visitors. Some themes display social media links automatically, and some only display them within the Social Media widget.', 'baton' )
	) );

	/*
	 * Facebook
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][facebook_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['facebook_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][facebook_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Facebook', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][facebook_url]',
				'priority' => 10
			)
		)
	);

	/*
	 * Twitter
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][twitter_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['twitter_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][twitter_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Twitter', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][twitter_url]',
				'priority' => 20
			)
		)
	);

	/*
	 * LinkedIn
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][linkedin_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['linkedin_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][linkedin_url]', // IDs can have nested array keys
			array(
				'label' => __( 'LinkedIn', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][linkedin_url]',
				'priority' => 30
			)
		)
	);

	/*
	 * Google+
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][google_plus_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['google_plus_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][google_plus_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Google+', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][google_plus_url]',
				'priority' => 40
			)
		)
	);

	/*
	 * YouTube
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][youtube_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['youtube_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][youtube_url]', // IDs can have nested array keys
			array(
				'label' => __( 'YouTube', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][youtube_url]',
				'priority' => 50
			)
		)
	);

	/*
	 * Vimeo
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][vimeo_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['vimeo_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][vimeo_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Vimeo', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][vimeo_url]',
				'priority' => 60
			)
		)
	);

	/*
	 * Instagram
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][instagram_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['instagram_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][instagram_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Instagram', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][instagram_url]',
				'priority' => 70
			)
		)
	);

	/*
	 * Pinterest
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][pinterest_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['pinterest_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][pinterest_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Pinterest', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][pinterest_url]',
				'priority' => 80
			)
		)
	);

	/*
	 * Flickr
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][flickr_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['flickr_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][flickr_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Flickr', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][flickr_url]',
				'priority' => 90
			)
		)
	);

	/*
	 * Foursquare
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][foursquare_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['foursquare_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][foursquare_url]', // IDs can have nested array keys
			array(
				'label' => __( 'Foursquare', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][foursquare_url]',
				'priority' => 100
			)
		)
	);

	/*
	 * RSS (Use Site RSS Feed)
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][rss_url_use_site_feed]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['rss_url_use_site_feed'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_boolval'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Checkbox_Control(
			$wp_customize,
			'sds_theme_options[social_media][rss_url_use_site_feed]', // IDs can have nested array keys
			array(
				'label' => __( 'RSS - Use Site RSS Feed', 'baton' ),
				'description' => __( 'When "yes" is displayed, the RSS feed for your site will be used. Otherwise, you can enter a custom RSS URL to use instead.', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][rss_url_use_site_feed]',
				'priority' => 110,
				'type' => 'checkbox', // Used in js controller
				'css_class' => 'sds-theme-options-rss-url-use-site-feed',
				'css_id' => 'sds_theme_options_rss_url_use_site_feed',
				'checked_label' => __( 'Yes', 'baton' ),
				'unchecked_label' => __( 'No', 'baton' ),
				'style' => array(
					'before' => 'width: 35%; text-align: center;',
					'after' => 'right: 0; width: 35%; padding: 0 6px; text-align: center;'
				),
				'checkbox_type' => 'reverse'
			)
		)
	);

	/*
	 * RSS (Custom RSS Feed)
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[social_media][rss_url]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['social_media']['rss_url'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'esc_url'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[social_media][rss_url]', // IDs can have nested array keys
			array(
				'label' => __( 'RSS - Custom RSS Feed', 'baton' ),
				'section'  => 'sds_theme_options_social_media',
				'settings' => 'sds_theme_options[social_media][rss_url]',
				'priority' => 120,
				'active_callback' => 'sds_social_media_use_custom_rss_feed'
			)
		)
	);


	/**
	 * Custom Scripts and Styles
	 */
	// Panel
	$wp_customize->add_panel( 'sds_theme_options_custom_scripts_styles', array(
		'priority' => 130, // After Static Front Page
		'title' => __( 'Custom Scripts &amp; Styles', 'baton' )
	) );


	/*
	 * Custom Scripts
	 */
	// Section
	$wp_customize->add_section( 'sds_theme_options_custom_scripts', array(
		'priority' => 10,
		'title' => __( 'Custom Scripts', 'baton' ),
		'description' => sprintf( __( 'Add custom scripts to either the head section or the footer section of your site. See <a href="%1$s" target="_blank">WordPress Codex: Using Javascript</a> for more information.', 'baton' ), esc_url( 'http://codex.wordpress.org/Using_Javascript' ) ),
		'panel' => 'sds_theme_options_custom_scripts_styles'
	) );


	/*
	 * Custom Scripts (Head)
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[custom_scripts][wp_head]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['custom_scripts']['wp_head'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_sanitize_custom_scripts'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Textarea_Control(
			$wp_customize,
			'sds_theme_options[custom_scripts][wp_head]', // IDs can have nested array keys
			array(
				'label' => _x( 'Head', 'Custom scripts for wp_head label', 'baton' ),
				'section'  => 'sds_theme_options_custom_scripts',
				'settings' => 'sds_theme_options[custom_scripts][wp_head]',
				'priority' => 10,
				'type' => 'sds_codemirror', // Used in js controller
				'rows' => 10,
				'mode' => 'js'
			)
		)
	);


	/*
	 * Custom Scripts (Footer)
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[custom_scripts][wp_footer]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['custom_scripts']['wp_footer'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_sanitize_custom_scripts'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Textarea_Control(
			$wp_customize,
			'sds_theme_options[custom_scripts][wp_footer]', // IDs can have nested array keys
			array(
				'label' => _x( 'Footer', 'Custom scripts for wp_footer label', 'baton' ),
				'section'  => 'sds_theme_options_custom_scripts',
				'settings' => 'sds_theme_options[custom_scripts][wp_footer]',
				'priority' => 20,
				'type' => 'sds_codemirror', // Used in js controller
				'rows' => 10,
				'mode' => 'js'
			)
		)
	);


	/*
	 * Custom Styles
	 */
	// Section
	$wp_customize->add_section( 'sds_theme_options_custom_styles', array(
		'priority' => 20,
		'title' => __( 'Custom Styles', 'baton' ),
		'description' => sprintf( __( 'Add custom styles your site. See <a href="%1$s" target="_blank">Mozilla Developer Network: CSS</a> for more information.', 'baton' ), esc_url( 'https://developer.mozilla.org/en-US/docs/Web/CSS' ) ),
		'panel' => 'sds_theme_options_custom_scripts_styles'
	) );


	/*
	 * Custom Styles
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[custom_styles]', // IDs can have nested array keys
		array(
			'default' => $sds_theme_options_defaults['custom_styles'],
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_sanitize_custom_styles'
		)
	);

	// Control
	$wp_customize->add_control(
		new SDS_Theme_Options_Customize_Textarea_Control(
			$wp_customize,
			'sds_theme_options[custom_styles]', // IDs can have nested array keys
			array(
				'label' => _x( 'CSS', 'Custom styles label', 'baton' ),
				'section'  => 'sds_theme_options_custom_styles',
				'settings' => 'sds_theme_options[custom_styles]',
				'priority' => 10,
				'type' => 'sds_codemirror', // Used in js controller
				'rows' => 10,
				'mode' => 'css'
			)
		)
	);


	/**
	 * Footer Copyright and Branding
	 */
	// Section
	$wp_customize->add_section( 'sds_footer_copyright_branding', array(
		'priority' => 140, // After Custom Scripts and Styles
		'title' => __( 'Copyright &amp; Branding', 'baton' ),
		'description' => __( 'Use this section to modify the footer branding of your website. Entering a value here will over-write the default footer branding. You may use the following HTML tags: <code>&lt;a&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;span&gt;</code>', 'baton' )
	) );

	/*
	 * Footer Copyright
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[footer][copyright]', // IDs can have nested array keys
		array(
			'default' => sds_get_copyright(),
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_sanitize_footer_copyright_branding'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[footer][copyright]',
			array(
				'label' => __( 'Footer Copyright', 'baton' ),
				'section' => 'sds_footer_copyright_branding',
				'settings' => 'sds_theme_options[footer][copyright]',
				'priority' => 10
			)
		)
	);

	/*
	 * Footer Branding
	 */
	// Setting
	$wp_customize->add_setting(
		'sds_theme_options[footer][branding]', // IDs can have nested array keys
		array(
			'default' => sds_get_copyright_branding(),
			'type' => 'option',
			// Data is also sanitized upon update_option() call using the sanitize function in $sds_theme_options_instance
			'sanitize_callback' => 'sds_sanitize_footer_copyright_branding'
		)
	);

	// Control
	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'sds_theme_options[footer][branding]', // IDs can have nested array keys
			array(
				'label' => __( 'Footer Branding', 'baton' ),
				'section' => 'sds_footer_copyright_branding',
				'settings' => 'sds_theme_options[footer][branding]',
				'priority' => 20
			)
		)
	);
}

/**
 * This function re-initializes theme options to ensure the Customizer preview functions as expected.
 * It also contains a backwards compatibility check for the Remove Logo option.
 */
add_action( 'customize_preview_init', 'sds_customize_preview_init' );

function sds_customize_preview_init() {
	global $sds_theme_options;

	$sds_theme_options = SDS_Theme_Options::get_sds_theme_options();

	/**
	 * Remove Logo backwards compatibility check
	 *
	 * If 'remove-logo' is set in the options array, we need to remove it here
	 * to ensure the Customizer will save the logo information correctly.
	 * This is due to the Theme Options sanitize function running on save of Theme
	 * Customizer, which checks for 'remove-logo' and nulls the logo_attachment_id
	 * value if it's set. We're now unset()ing 'remove-logo' if it is set in Theme
	 * Options, however previous versions were not doing so. This check is necessary
	 * for backwards compatibility.
	 */
	if ( isset( $sds_theme_options['remove-logo'] ) ) {
		unset( $sds_theme_options['remove-logo'] );

		update_option( SDS_Theme_Options::get_option_name(), $sds_theme_options );
	}
}

/**
 * This function contains backwards compatibility logic for SDS Theme Options.
 *
 * Featured Image Size backwards compatibility check:
 *
 * In previous versions of our themes, the featured image size might be stored as
 * an empty value (''). The Customizer will over-ride the default setting value with this
 * empty value if it was found in our 'sds_theme_options' option. We need to ensure that
 * the featured image size is set to the "true" default value in the Customizer. We've
 * moved the 'sds_theme_options_default_featured_image_size' filter to defaults so that
 * future versions of our themes will account for this. This check is necessary for
 * backwards compatibility.
 *
 *
 * Footer Copyright and Branding backwards compatibility check:
 *
 * The footer copyright and branding values may not be set or may be empty, however there
 * are defaults that we use in our themes via the sds_get_copyright() and sds_get_copyright_branding()
 * functions. If these values are empty, we'll apply the appropriate default to the option
 * so the Customizer does not show the values as empty (even though they may be empty in the
 * database). This ensures a better user experience for the end user.
 *
 *
 * This logic is only applied in the Customizer (@see sds_customize_register()).
 */
function sds_option_sds_theme_options( $value ) {
	$sds_theme_options_defaults = SDS_Theme_Options::get_sds_theme_option_defaults(); // Defaults

	/*
	 * Featured Image Size backwards compatibility check
	 */
	// Only if the featured_image_size value is set and is empty
	if ( isset( $value['featured_image_size'] ) && empty( $value['featured_image_size'] ) ) {
		// Set the featured_image_size value to the "true" default value
		$value['featured_image_size'] = $sds_theme_options_defaults['featured_image_size'];
	}

	/*
	 * Footer Copyright and Branding backwards compatibility checks
	 */
	// Only if the footer copyright value is not set or is empty
	if ( ! isset( $value['footer']['copyright'] ) || empty( $value['footer']['copyright'] ) ) {
		// Set the footer copyright value to the "true" default value
		$value['footer']['copyright'] = sds_get_copyright();
	}

	// Only if the footer branding value is not set or is empty
	if ( ! isset( $value['footer']['branding'] ) || empty( $value['footer']['branding'] ) ) {
		// Set the footer branding value to the "true" default value
		$value['footer']['branding'] = sds_get_copyright_branding();
	}

	return $value;
}

/**
 * This function enqueues scripts and styles on the Customizer only.
 */
add_action( 'customize_controls_enqueue_scripts', 'sds_customize_controls_enqueue_scripts' );

function sds_customize_controls_enqueue_scripts() {
	// SDS Theme Options
	wp_enqueue_style( 'sds-theme-options', SDS_Theme_Options::sds_core_url() . '/css/sds-theme-options.css', false, SDS_Theme_Options::VERSION );

	// Customizer SDS Theme Options (after core SDS Theme Options)
	wp_enqueue_style( 'sds-theme-options-customizer', SDS_Theme_Options::sds_core_url() . '/css/customizer-sds-theme-options.css', array( 'sds-theme-options' ) );
}


/***************************
 * Non-Pluggable Functions *
 ***************************/

/**
 * This function sets various theme options to their defaults to prevent overlap between themes.
 */
add_action( 'after_switch_theme' , 'sds_after_switch_theme' );

function sds_after_switch_theme() {
	global $sds_theme_options;

	$sds_theme_option_defaults = SDS_Theme_Options::get_sds_theme_option_defaults(); // Defaults
	$sds_theme_options_instance = SDS_Theme_Options_Instance();

	// Color Scheme (reset if necessary)
	if ( ! empty( $sds_theme_options['color_scheme'] ) && function_exists( 'sds_color_schemes' ) ) {
		$color_scheme = $sds_theme_options['color_scheme'];
		$color_schemes = sds_color_schemes();

		if ( ! isset( $color_schemes[$color_scheme] ) )
			$sds_theme_options['color_scheme'] = $sds_theme_option_defaults['color_scheme'];
	}

	// Web Font (reset if necessary)
	if ( ! empty( $sds_theme_options['web_font'] ) && function_exists( 'sds_web_fonts' ) ) {
		$web_font = $sds_theme_options['web_font'];
		$web_fonts = sds_web_fonts();

		if ( ! isset( $web_fonts[$web_font] ) )
			$sds_theme_options['web_font'] = $sds_theme_option_defaults['web_font'];
	}

	// Featured Image Size (reset if necessary)
	if ( ! empty( $sds_theme_options['featured_image_size'] ) ) {
		$featured_image_size = $sds_theme_options['featured_image_size'];
		$avail_image_sizes = $sds_theme_options_instance->get_available_image_sizes();

		if ( ! isset( $avail_image_sizes[$featured_image_size] ) )
			$sds_theme_options['featured_image_size'] = $sds_theme_option_defaults['featured_image_size'];
	}

	// Reset Footer (Copyright/Branding)
	$sds_theme_options['footer']['copyright'] = $sds_theme_option_defaults['footer']['copyright'];
	$sds_theme_options['footer']['branding'] = $sds_theme_option_defaults['footer']['branding'];

	// Content Layouts (reset if necessary)
	if ( function_exists( 'sds_content_layouts' ) ) {
		$content_layouts = $sds_theme_options['content_layouts'];
		$sds_content_layouts = sds_content_layouts();

		foreach( $content_layouts as $content_layout_id => $content_layout )
			if ( $content_layout && ! isset( $sds_content_layouts[$content_layout] ) )
				$sds_theme_options['content_layouts'][$content_layout_id] = $sds_theme_option_defaults['content_layouts'][$content_layout_id];
	}

	// License (validation)
	if ( isset( $sds_theme_options['license'] ) && isset( $sds_theme_options['license']['key'] ) && ! empty( $sds_theme_options['license']['key'] ) ) {
		$api_args = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $sds_theme_options['license']['key'],
			'item_name' => urlencode( $sds_theme_options_instance->theme->get( 'Name' ) ),
			'url'       => home_url()
		);
		$response = wp_remote_get( esc_url_raw( add_query_arg( $api_args, SDS_Theme_Options::$update_url ) ), array( 'timeout' => 15, 'sslverify' => false ) );

		// Make sure we have a valid response
		if ( ! is_wp_error( $response ) && ( $license_data = json_decode( wp_remote_retrieve_body( $response ) ) ) )
			// Validate that the request was successful and we have a valid license
			if ( $license_data->success && $license_data->license === 'valid' )
				$sds_theme_options['license']['status'] = 'valid';
			// Otherwise reset the license data
			else
				$sds_theme_options['license'] = $sds_theme_option_defaults['license'];
		// Otherwise we do not have a valid response, reset the license data
		else
			$sds_theme_options['license']['status'] = $sds_theme_option_defaults['license'];
	}

	// Update the option with new values
	update_option( SDS_Theme_Options::get_option_name(), $sds_theme_options );
}

/**
 * This function ties into the TGM Plugin Activation Class and recommends plugins to the user.
 */
add_action( 'tgmpa_register', 'sds_tgmpa_register' );

function sds_tgmpa_register() {
	$plugins = array(
		// One-Click Child Themes for Slocum Themes
		array(
			'name' => 'One-Click Child Themes for Slocum Themes',
			'slug' => 'sds-one-click-child-themes-master',
			'source' => 'https://github.com/sdsweb/sds-one-click-child-themes/archive/master.zip',
			'required' => false,
			'force_activation' => false,
			'force_deactivation' => false,
			'external_url' => 'https://github.com/sdsweb/sds-one-click-child-themes/'
		),

		// Note
		array(
			'name' => 'Note - A live text widget',
			'slug' => 'note',
			'required' => false
		)
	);

	$plugins = apply_filters( 'sds_tgmpa_plugins', $plugins );

	tgmpa( $plugins );
}

/**
 * This function enqueues all necessary scripts/styles based on options.
 */
add_action( 'wp_enqueue_scripts', 'sds_wp_enqueue_scripts' );

function sds_wp_enqueue_scripts() {
	global $sds_theme_options;

	// Color Schemes
	if ( $selected_color_scheme = sds_get_color_scheme() )
		wp_enqueue_style( $selected_color_scheme['deps'] . '-' . $sds_theme_options['color_scheme'], get_template_directory_uri() . $selected_color_scheme['stylesheet'], array( $selected_color_scheme['deps'] ) );

	// Web Fonts
	if ( function_exists( 'sds_web_fonts' ) && ! empty( $sds_theme_options['web_font'] ) ) {
		$protocol = is_ssl() ? 'https' : 'http';

		if ( ! empty( $sds_theme_options['web_font'] ) )
			wp_enqueue_style( 'sds-google-web-font', $protocol . '://fonts.googleapis.com/css?family=' . $sds_theme_options['web_font'] );
	}

	// Theme Option Fonts (Social Media)
	// TODO: Provide logic to determine if the theme uses sds_social_media() in templates, or if this is active only when the Social Media widget is active
	if ( ! empty( $sds_theme_options['social_media'] ) ) {
		$social_networks_active = false;

		foreach( $sds_theme_options['social_media'] as $network => $url )
			if ( ! empty( $url ) ) {
				$social_networks_active = true;
				break;
			}

		if ( $social_networks_active )
			wp_enqueue_style( 'font-awesome-css-min', SDS_Theme_Options::sds_core_url() . '/css/font-awesome.min.css' );
	}

	// Comment Replies
	if ( is_singular() )
		wp_enqueue_script( 'comment-reply' );
}

/**
 * This function is a fallback for 'title-tag' theme support added in WordPress 4.1.
 */
if ( ! function_exists( '_wp_render_title_tag' ) ) {
	add_action( 'wp_head', 'sds_wp_head_title', 1 );

	function sds_wp_head_title() {
	?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php
	}
}

/**
 * This function outputs necessary scripts/styles in the head section based on options (web font, custom scripts/styles).
 */
add_action( 'wp_head', 'sds_wp_head' );

function sds_wp_head() {
	global $sds_theme_options, $is_IE;

	// Web Fonts
	if ( function_exists( 'sds_web_fonts' ) && ! empty( $sds_theme_options['web_font'] ) ) :
		$web_fonts = sds_web_fonts();
		$selected_web_font = array_key_exists( $sds_theme_options['web_font'], $web_fonts ) ? $web_fonts[$sds_theme_options['web_font']] : false;

		if ( ! empty( $selected_web_font ) && isset( $selected_web_font['css'] ) ) :
		?>
			<style type="text/css">
				<?php echo apply_filters( 'sds_web_font_css_selector', 'html, body' ); ?> {
					<?php echo $selected_web_font['css']; ?>
				}
			</style>
		<?php
		endif;
	endif;

	// Custom Scripts
	if ( ! empty( $sds_theme_options['custom_scripts']['wp_head'] ) ) {
		// If the user included script tags
		if ( $sds_theme_options['custom_scripts']['wp_head_has_tags'] )
			echo $sds_theme_options['custom_scripts']['wp_head'];
		// Otherwise add opening and closing script (JavaScript) tags
		else
			echo '<script type="text/javascript">' . $sds_theme_options['custom_scripts']['wp_head'] . '</script>';
	}

	// Custom Styles
	if ( ! empty( $sds_theme_options['custom_styles'] ) ) {
		// If the user included style tags
		if ( $sds_theme_options['custom_styles_has_tags'] )
			echo $sds_theme_options['custom_styles'];
		// Otherwise add opening and closing style tags
		else
			echo '<style type="text/css">' . $sds_theme_options['custom_styles'] . '</style>';
	}

	// HTML5 Shiv (IE only, conditionally for less than IE9)
	if ( $is_IE )
		echo '<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
}

/**
 * This function outputs the necessary CSS classes in the body_class() function based on content layout settings.
 */
add_filter( 'body_class', 'sds_body_class' );

function sds_body_class( $classes ) {
	global $sds_theme_options, $post;

	// If theme supports content layouts
	if ( function_exists( 'sds_content_layouts' ) ) {
		// If single page, determine if specific page template is set
		$wp_page_template = ( is_page() ) ? get_post_meta( $post->ID, '_wp_page_template', true ) : false;
		$sds_theme_options['page_template'] = $wp_page_template;

		// Global
		if ( ! empty( $sds_theme_options['content_layouts']['global'] ) ) {
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['global'];

			// Remove content layout styles if a page template is selected
			if ( ! empty( $wp_page_template ) && $wp_page_template !== 'default' ) {
				unset( $sds_theme_options['body_class'] );
				unset( $classes['sds-content-layout'] );
			}
		}

		// 404 Error
		if ( is_404() && ! empty( $sds_theme_options['content_layouts']['404'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['404'];

		// Single Post
		if ( is_single() && ! empty( $sds_theme_options['content_layouts']['single'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['single'];

		// Home (Blog)
		if ( is_home() && ! empty( $sds_theme_options['content_layouts']['home'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['home'];

		// Single Page
		if ( is_page() && ! empty( $sds_theme_options['content_layouts']['page'] ) ) {
			// Add content layout styles only if a page template is not selected
			if ( empty( $wp_page_template ) || $wp_page_template === 'default' )
				$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['page'];
		}

		// Front Page
		if ( is_front_page() && ! empty( $sds_theme_options['content_layouts']['front_page'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['front_page'];

		// Archive
		if ( is_archive() && ! empty( $sds_theme_options['content_layouts']['archive'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['archive'];

		// Category Archive
		if ( is_category() && ! empty( $sds_theme_options['content_layouts']['category'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['category'];

		// Tag Archive
		if ( is_tag() && ! empty( $sds_theme_options['content_layouts']['tag'] ) )
			$sds_theme_options['body_class'] = $classes['sds-content-layout'] = $sds_theme_options['content_layouts']['tag'];
	}

	return $classes;
}


/**
 * This function outputs necessary scripts in the footer section based on options (custom scripts).
 */
add_action( 'wp_footer', 'sds_wp_footer' );

function sds_wp_footer() {
	global $sds_theme_options;

	// Custom Scripts
	if ( ! empty( $sds_theme_options['custom_scripts']['wp_footer'] ) ) {
		// If the user included script tags
		if ( $sds_theme_options['custom_scripts']['wp_footer_has_tags'] )
			echo $sds_theme_options['custom_scripts']['wp_footer'];
		// Otherwise add opening and closing script (JavaScript) tags
		else
			echo '<script type="text/javascript">' . $sds_theme_options['custom_scripts']['wp_footer'] . '</script>';
	}

}

/**
 * This function configures/sets up theme options/features.
 */
add_action( 'after_setup_theme', 'sds_after_setup_theme' );

function sds_after_setup_theme() {
	// Enable Featured Images
	add_theme_support( 'post-thumbnails' );

	// Enable Automatic Feed Links
	add_theme_support( 'automatic-feed-links' );

	// Enable Title Tag Support (4.1)
	add_theme_support( 'title-tag' );

	// Enable excerpts on Pages
	add_post_type_support( 'page', 'excerpt' );

	// Register WordPress Menus
	register_nav_menus( array(
		'top_nav' => __( 'Top Navigation', 'baton' ),
		'primary_nav' => __( 'Primary Navigation', 'baton' ),
		'footer_nav' => __( 'Footer Navigation', 'baton' )
	) );
}

/**
 * This function configures sidebars for use throughout the theme
 */
add_action( 'widgets_init', 'sds_widgets_init' );

function sds_widgets_init() {
	// Register SDS Social Media Widget (/includes/widget-social-media.php)
	register_widget( 'SDS_Social_Media_Widget' );

	// Primary sidebar
	register_sidebar( array(
		'name'          => __( 'Primary Sidebar', 'baton' ),
		'id'            => 'primary-sidebar',
		'description'   => __( 'This widget area is the primary widget area.', 'baton' ),
		'before_widget' => '<section id="primary-sidebar-%1$s" class="widget primary-sidebar primary-sidebar-widget %2$s">',
		'after_widget'  => '<section class="clear"></section></section>',
		'before_title'  => '<h3 class="widgettitle widget-title primary-sidebar-widget-title">',
		'after_title'   => '</h3>',
	) );

	// Secondary sidebar
	register_sidebar( array(
		'name'          => __( 'Secondary Sidebar', 'baton' ),
		'id'            => 'secondary-sidebar',
		'description'   => __( 'This widget area is the secondary widget area.', 'baton' ),
		'before_widget' => '<section id="secondary-sidebar-%1$s" class="widget secondary-sidebar secondary-sidebar-widget %2$s">',
		'after_widget'  => '<section class="clear"></section></section>',
		'before_title'  => '<h3 class="widgettitle widget-title secondary-sidebar-widget-title">',
		'after_title'   => '</h3>',
	) );

	// Front Page Slider
	register_sidebar( array(
		'name'          => __( 'Front Page Slider', 'baton' ),
		'id'            => 'front-page-slider-sidebar',
		'description'   => __( '*This widget area is only displayed if a Front Page is selected via Settings &gt; Reading in the Dashboard.* This widget area is displayed above the content on the Front Page.', 'baton' ),
		'before_widget' => '<section id="front-page-slider-%1$s" class="widget front-page-slider front-page-slider-widget slider %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title front-page-slider-title">',
		'after_title'   => '</h3>'
	) );

	// Front Page
	register_sidebar( array(
		'name'          => __( 'Front Page', 'baton' ),
		'id'            => 'front-page-sidebar',
		'description'   => __( '*This widget area is only displayed if a Front Page is selected via Settings &gt; Reading in the Dashboard.* This widget area is displayed below the Front Page Slider on the Front Page and will replace the Front Page content.', 'baton' ),
		'before_widget' => '<section id="front-page-%1$s" class="widget front-page front-page-sidebar %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title front-page-title">',
		'after_title'   => '</h3>'
	) );

	// Header Call To Action
	register_sidebar( array(
		'name'          => __( 'Header Call To Action', 'baton' ),
		'id'            => 'header-call-to-action-sidebar',
		'description'   => __( 'This widget area is used to display a call to action in the header.', 'baton' ),
		'before_widget' => '<section id="header-call-to-action-%1$s" class="widget header-call-to-action-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title header-call-to-action-widget-title">',
		'after_title'   => '</h3>',
	) );

	// After Posts
	register_sidebar( array(
		'name'          => __( 'After Posts', 'baton' ),
		'id'            => 'after-posts-sidebar',
		'description'   => __( 'This widget area is displayed below the content on single posts only.', 'baton' ),
		'before_widget' => '<section id="after-posts-%1$s" class="widget after-posts after-posts-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title after-posts-title">',
		'after_title'   => '</h3>'
	) );

	// Footer
	register_sidebar( array(
		'name'          => __( 'Footer', 'baton' ),
		'id'            => 'footer-sidebar',
		'description'   => __( 'This widget area is displayed in the footer of all pages.', 'baton' ),
		'before_widget' => '<section id="footer-widget-%1$s" class="widget footer-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title footer-widget-title">',
		'after_title'   => '</h3>'
	) );

	// Copyright
	register_sidebar( array(
		'name'          => __( 'Copyright Area', 'baton' ),
		'id'            => 'copyright-area-sidebar',
		'description'   => __( 'This widget area is designed for small text blurbs or disclaimers at the bottom of the website.', 'baton' ),
		'before_widget' => '<section id="copyright-area-widget-%1$s" class="widget copyright-area copyright-area-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widgettitle widget-title copyright-area-widget-title">',
		'after_title'   => '</h3>',
	) );
}

/**
 * This function outputs the Primary Sidebar.
 */
function sds_primary_sidebar() {
	if ( is_active_sidebar( 'primary-sidebar' ) )
		dynamic_sidebar( 'primary-sidebar' );
}

/**
 * This function outputs the Secondary Sidebar.
 */
function sds_secondary_sidebar() {
	if ( is_active_sidebar( 'secondary-sidebar' ) )
		dynamic_sidebar( 'secondary-sidebar' );
}

/**
 * This function outputs the Front Page Slider Sidebar.
 */
function sds_front_page_slider_sidebar() {
	if ( is_active_sidebar( 'front-page-slider-sidebar' ) )
		dynamic_sidebar( 'front-page-slider-sidebar' );
}

/**
 * This function outputs the Header Call to Action Sidebar.
 */
function sds_header_call_to_action_sidebar() {
	if ( is_active_sidebar( 'header-call-to-action-sidebar' ) )
		dynamic_sidebar( 'header-call-to-action-sidebar' );
}

/**
 * This function outputs the After Posts Sidebar.
 */
function sds_after_posts_sidebar() {
	if ( is_active_sidebar( 'after-posts-sidebar' ) )
		dynamic_sidebar( 'after-posts-sidebar' );
}

/**
 * This function outputs the Footer Sidebar.
 */
function sds_footer_sidebar() {
	if ( is_active_sidebar( 'footer-sidebar' ) )
		dynamic_sidebar( 'footer-sidebar' );
}

/**
 * This function outputs the Copyright Area Sidebar.
 */
function sds_copyright_area_sidebar() {
	if ( is_active_sidebar( 'copyright-area-sidebar' ) )
		dynamic_sidebar( 'copyright-area-sidebar' );
}


/**
 * This function is a conditional that checks to see if the author meta should be shown on the front-end based on settings.
 */
function sds_show_author_meta() {
	global $sds_theme_options;

	// Return the opposite of the hide setting
	return ! $sds_theme_options['hide_author_meta'];
}

/**
 * This function returns the default copyright.
 */
function sds_get_copyright() {
	return apply_filters( 'sds_copyright', sprintf( wp_kses( __( 'Copyright &copy; %1$s <a href="%2$s">%3$s</a>. All Rights Reserved.', 'baton' ), array( 'a' => array( 'href' => array() ) ) ), date( 'Y' ), esc_url( home_url( '/' ) ), get_bloginfo( 'name' ) ) );
}

/**
 * This function returns the default copyright branding.
 */
function sds_get_copyright_branding() {
	$sds_theme = SDS_Theme_Options_Instance()->get_parent_theme();

	return apply_filters( 'sds_copyright_branding', sprintf( __( '<a href="%1$s" target="_blank">%2$s by Slocum Studio</a>', 'baton' ), esc_url( sprintf( 'http://slocumthemes.com/?utm_source=%1$s&amp;utm_medium=footer-plugs&amp;utm_campaign=WordPressThemes', esc_url( home_url( '/' ) ) ) ), $sds_theme->get( 'Name' ) ), $sds_theme->get( 'Name' ), $sds_theme );
}


/**
 * This function determines whether or not the user has selected a color scheme and returns
 * the color scheme details if they have.
 *
 * The default color scheme can be ignored and thus if the user has selected the default color
 * scheme it will not be returned.
 */
function sds_get_color_scheme( $ignore_default = true ) {
	global $sds_theme_options;

	// Return value
	$r = false;

	// Default and all other color schemes (when user has selected options)
	if ( function_exists( 'sds_color_schemes' ) && ! empty( $sds_theme_options['color_scheme'] ) ) {
		$color_schemes = sds_color_schemes();

		if ( ! empty( $sds_theme_options['color_scheme'] ) && isset( $color_schemes[$sds_theme_options['color_scheme']] ) ) {
			$selected_color_scheme = array_key_exists( $sds_theme_options['color_scheme'], $color_schemes ) ? $color_schemes[$sds_theme_options['color_scheme']] : false;

			// Is this the default color scheme?
			$default_color_scheme = ( isset( $selected_color_scheme['default'] ) && $selected_color_scheme['default'] ) ? true : false;

			// If we're not ignoring the default, or we are and this isn't a default color scheme
			if ( ( ! $ignore_default || ! $default_color_scheme ) )
				$r = $selected_color_scheme;
		}
	}
	// Default
	else if ( function_exists( 'sds_color_schemes' ) && empty( $sds_theme_options['color_scheme'] ) && ! $ignore_default ) {
		$color_schemes = sds_color_schemes();

		$r = $color_schemes['default'];
	}

	return apply_filters( 'sds_color_scheme', $r, $ignore_default );
}

/**
 * This function determines if a specific theme mod is set, and whether or not
 * it is set to the default value.
 *
 * $color_scheme_properties is an optional array of keys for which to check against in the
 * theme defaults.
 */
function sds_get_theme_mod( $name, $color_scheme_properties = array() ) {
	$theme_mod = get_theme_mod( $name ); // Get theme mod
	$selected_color_scheme = sds_get_color_scheme( false ); // Get selected color scheme data
	$color_scheme_properties = ( ! empty( $color_scheme_properties ) ) ? $color_scheme_properties : array( $name );

	// First make sure the theme mod isn't empty
	if ( empty( $theme_mod ) )
		return false;

	// Next we check to make sure the theme mod isn't equal to any one of the color scheme defaults
	// TODO: If set, this function should return an array of $color_scheme_properties?
	foreach ( $color_scheme_properties as $property )
		// If the theme mod is equal to one of the color scheme defaults
		if ( isset( $selected_color_scheme[$property] ) && $theme_mod === $selected_color_scheme[$property] )
			return false;

	return $theme_mod;
}

/**
 * This function determines if the RSS feed of the site should be used instead of a custom RSS URL. This function
 * is typically passed as a 'active_callback' for settings/controls within the Customizer.
 */
function sds_social_media_use_site_rss_feed() {
	global $sds_theme_options;

	return $sds_theme_options['social_media']['rss_url_use_site_feed'];
}

/**
 * This function determines if a custom RSS feed should be used. It returns the opposite value of
 * sds_social_media_site_rss_feed(). This function is typically passed as a 'active_callback' for
 * settings/controls within the Customizer.
 *
 * @see sds_social_media_site_rss_feed()
 */
function sds_social_media_use_custom_rss_feed() {
	return ! sds_social_media_use_site_rss_feed();
}

/**
 * This function returns the Boolean value of the $var parameter passed. The $wp_customize_setting (optional)
 * parameter is set to false by default because this function is typically passed as a 'sanitize_callback'
 * for settings/controls within the Customizer and the 'sanitize_callback' filter logic  passes 2 parameters
 * to the callback function.
 */
function sds_boolval( $value, $wp_customize_setting = false ) {
	return ( bool ) $value;
}

/**
 * This function sanitizes custom script data. It runs the data through wp_kses() to allow
 * only certain HTML elements. The $wp_customize_setting (optional) parameter is set to false by default
 * because this function is typically passed as a 'sanitize_callback' for settings/controls within the Customizer
 * and the 'sanitize_callback' filter logic  passes 2 parameters to the callback function.
 */
function sds_sanitize_custom_scripts( $value, $wp_customize_setting = false ) {
	// wp_kses the data (allowing <script> elements)
	$value = wp_kses( $value, array(
		'script' => array(
			'async' => array(),
			'src' => array(),
			'type' => array(),
			'language' => array()
		)
	) );

	// Convert stray HTML entities back as they could be part of a script
	$value = htmlspecialchars_decode( $value );

	return $value;
}

/**
 * This function sanitizes custom style data. It runs the data through wp_kses() to allow
 * only certain HTML elements. The $wp_customize_setting (optional) parameter is set to false by default
 * because this function is typically passed as a 'sanitize_callback' for settings/controls within the Customizer
 * and the 'sanitize_callback' filter logic  passes 2 parameters to the callback function.
 */
function sds_sanitize_custom_styles( $value, $wp_customize_setting = false ) {
	// wp_kses the data (allowing <script> elements)
	$value = wp_kses( $value, array(
		'style' => array(
			'type' => array(),
			'media' => array()
		)
	) );

	// Convert stray HTML entities back as they could be part of styles
	$value = htmlspecialchars_decode( $value );

	return $value;
}

/**
 * This function sanitizes footer branding and copyright data. It runs the data through wp_kses() to allow
 * only certain HTML elements. The $wp_customize_setting (optional) parameter is set to false by default
 * because this function is typically passed as a 'sanitize_callback' for settings/controls within the Customizer
 * and the 'sanitize_callback' filter logic  passes 2 parameters to the callback function.
 */
function sds_sanitize_footer_copyright_branding( $value, $wp_customize_setting = false ) {
	// wp_kses the data (allowing <a>, <strong>, <em>, <span> elements)
	$value = wp_kses( $value, array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'target' => array()
		),
		'strong' => array(),
		'em' => array(),
		'span' => array()
	) );

	return $value;
}
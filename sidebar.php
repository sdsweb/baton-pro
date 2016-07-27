<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<div class="baton-col baton-col-sidebar <?php echo ( is_active_sidebar( 'primary-sidebar' ) ) ? 'has-primary-sidebar' : false ?> <?php echo ( is_active_sidebar( 'secondary-sidebar' ) ) ? 'has-secondary-sidebar' : false ?>">
	<section class="sidebar-container">
		<?php
			global $sds_theme_options;

			// Primary Sidebar
			if ( ! isset( $sds_theme_options['body_class'] ) || ( ! empty( $sds_theme_options['body_class'] ) && strpos( $sds_theme_options['body_class'], 'cols-1' ) === false ) ) :
		?>
				<!-- Primary Sidebar-->
				<aside class="sidebar <?php echo ( is_active_sidebar( 'primary-sidebar' ) ) ? 'widgets' : 'no-widgets'; ?>">
					<?php
						// Primary Sidebar
						if ( is_active_sidebar( 'primary-sidebar' ) )
							sds_primary_sidebar();
						// Social Media Fallback
						else
							sds_social_media();
					?>
				</aside>
				<!-- End Primary Sidebar-->
		<?php
			endif;
		?>
	</section>
</div>
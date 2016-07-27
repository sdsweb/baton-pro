<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;

	// SDS Theme Options
	global $sds_theme_options;
?>

<!DOCTYPE html>
<!--[if lt IE 7 ]><html class="ie ie6" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]><html class="ie ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?>><!--<![endif]-->
	<head>
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
		<?php do_action( 'baton_header_before' ); ?>

		<!-- Header	-->
		<header id="header" class="cf">
			<!-- Primary Navigation (Mobile) -->
			<div id="primary-nav-wrap-mobile" class="primary-nav-wrap-mobile">
				<nav id="primary-nav-mobile-container" class="primary-nav-mobile-container cf-768">
					<a href="#" id="primary-nav-button" class="primary-nav-button button" title="<?php esc_attr_e( 'Toggle Navigation', 'baton' ); ?>">
						<span class="primary-nav-button-inner">
							<?php
								// Primary Navigation label
								if ( ( $nav_menu_locations = get_nav_menu_locations() ) && isset( $nav_menu_locations['primary_nav'] ) && ( $primary_nav_menu_object = wp_get_nav_menu_object( $nav_menu_locations['primary_nav'] ) ) ) {
									// Output the navigation name
									echo $primary_nav_menu_object->name;
								}
								// Fallback
								else
									_e( 'Navigation', 'baton' );
							?>
						</span>
					</a>

					<?php
						// Primary Navigation Menu
						wp_nav_menu( array(
							'theme_location' => 'primary_nav',
							'container' => false,
							'menu_class' => 'primary-nav primary-nav-mobile menu',
							'menu_id' => 'primary-nav-mobile',
							'fallback_cb' => 'baton_mobile_primary_menu_fallback',
							'link_before' => '<span>',
							'link_after' => '</span>'
						) );
					?>
				</nav>
			</div>
			<!-- End Primary Navigation (Mobile) -->

			<div class="in baton-flex baton-flex-2-columns baton-flex-header-in">
				<!-- Logo/Site Title & Tagline -->
				<div id="title-tagline" class="baton-col baton-col-center-vertically baton-col-title-tagline title-tagline">
					<?php sds_logo(); ?>
					<?php sds_tagline(); ?>
				</div>
				<!-- End Logo/Site Title & Tagline -->

				<!-- Primary Navigation -->
				<div id="primary-nav-wrap" class="baton-col baton-col-center-vertically baton-col-primary-nav-wrap primary-nav-wrap">
					<nav id="primary-nav-container" class="primary-nav-container cf-768">
						<?php
							// Primary Navigation Menu
							wp_nav_menu( array(
								'theme_location' => 'primary_nav',
								'container' => false,
								'menu_class' => 'primary-nav menu',
								'menu_id' => 'primary-nav',
								'fallback_cb' => 'sds_primary_menu_fallback',
								'link_before' => '<span>',
								'link_after' => '</span>'
							) );
						?>
					</nav>
				</div>
				<!-- End Primary Navigation -->
			</div>

			<div class="clear"></div>
		</header>
		<!-- End Header -->

		<?php do_action( 'baton_header_after' ); ?>

		<div class="clear"></div>

		<?php do_action( 'baton_secondary_navigation_before' ); ?>

		<!-- Secondary Navigation -->
		<?php if ( has_nav_menu( 'secondary_nav' ) ) : // Secondary Navigation Menu ?>
		<div id="secondary-nav-wrap" class="secondary-nav-wrap">
			<nav id="secondary-nav-container" class="secondary-nav-container">
				<a href="#" id="secondary-nav-button" class="secondary-nav-button button" title="<?php esc_attr_e( 'Toggle Navigation', 'baton' ); ?>">
					<span class="secondary-nav-button-inner">
						<?php
							// Secondary Navigation label
							if ( ( $nav_menu_locations = get_nav_menu_locations() ) && isset( $nav_menu_locations['secondary_nav'] ) && ( $secondary_nav_menu_object = wp_get_nav_menu_object( $nav_menu_locations['secondary_nav'] ) ) ) {
								// Output the navigation name
								echo $secondary_nav_menu_object->name;
							}
							// Fallback
							else
								_e( 'Navigation', 'baton' );
						?>
					</span>
				</a>

				<div class="in secondary-nav-container-in">
					<?php
						// Secondary Nav Menu
						wp_nav_menu( array(
							'theme_location' => 'secondary_nav',
							'container' => false,
							'menu_class' => 'secondary-nav menu',
							'menu_id' => 'secondary-nav',
						) );
					?>
				</div>
			</nav>
		</div>
		<?php endif; ?>
		<!-- End Secondary Navigation -->

		<?php do_action( 'baton_secondary_navigation_after' ); ?>

		<?php do_action( 'baton_content_wrapper_before' ); ?>

		<!-- Content Wrapper -->
		<div class="in content-wrapper-in cf">

			<?php do_action( 'baton_content_wrapper_in_before' ); ?>
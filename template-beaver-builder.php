<?php
/*
 * Template Name: Beaver Builder
 * This template is used for the display of Beaver Builder layouts.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

get_header( 'beaver-builder' );

	if ( have_posts() ) :
		while ( have_posts() ) : the_post();
			the_content();
		endwhile;
	endif;

get_footer( 'beaver-builder' );
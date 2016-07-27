<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<form id="search-form" class="search-form cf" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<span class="screen-reader-text"><?php _x( 'Search for:', 'label', 'baton' ) ?></span>
	<input type="text" id="search-term" class="search-term" name="s" placeholder="<?php esc_attr_e( 'Search...', 'baton' ); ?>" value="<?php echo get_search_query(); ?>" />
	<button type="submit" id="search-submit" class="button search-submit fa fa-search" value="" title="<?php esc_attr_e( 'Search', 'baton' ); ?>"></button>
</form>
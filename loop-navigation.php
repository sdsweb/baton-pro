<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<?php if ( sds_post_navigation( true ) ) : ?>
	<footer class="pagination">
		<?php sds_post_navigation(); ?>
	</footer>
<?php endif; ?>
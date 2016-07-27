<?php
	// Bail if accessed directly
	if ( ! defined( 'ABSPATH' ) )
		exit;
?>

<?php if ( baton_is_yoast_breadcrumbs_active() ) : // Breadcrumbs ?>
	<div class="yoast-breadcrumbs baton-breadcrumbs">
		<?php yoast_breadcrumb( '<span id="breadcrumbs" class="breadcrumbs">','</span>' ); ?>
	</div>
<?php endif; ?>
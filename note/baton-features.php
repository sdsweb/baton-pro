<?php
/**
 * This is the features template used for displaying Note Widget content.
 *
 *
 * Available Variables:
 * @var $number int, Reference to the row/column/content area number that is being displayed
 * @var $instance array, Reference to the widget instance (settings)
 * @var $args array, Reference to the widget args
 * @var $widget Note_Widget, Reference to the PHP instance of the Note Widget
 * @var $template string, Template ID
 *
 * Widget Functions:
 * $widget->template_css_class( $context, $instance ) - Output an HTML class attribute pre-populated with CSS classes based on
 * 													    parameters. Valid $context - 'content'.
 * $widget->template_content( $instance ) - Output content for a particular content area based on parameters.
 * 											Will output placeholder if content area is empty.
 */

// Bail if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;
?>

<?php // Standard Note Widget Content ?>
<div class="widget-content <?php echo esc_attr( $widget->get_template_css_class( 'content', $instance ) ); ?>">
	<?php $widget->template_content( $instance ); ?>
</div>

<?php // Default Note Widget (outputs columns based on template configuration) ?>
<?php $widget->load_template( $widget->get_template( 'default' ), $instance['template'], 'template', $instance, $args, $widget ); // Load Template ?>
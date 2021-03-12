<?php
/**
 * Form view.
 *
 * @since 2.0.0
 */
?>
<form 
	id="wu_form" 
	method="post" 
	class="wu_checkout_form_<?php echo esc_attr($checkout_form_name); ?> wu-styling wu-relative"
>

	<?php

	/**
	 * Instantiate the form for the order details.
	 *
	 * @since 2.0.0
	 */
	$form = new \WP_Ultimo\UI\Form("checkout-{$step_name}", wu_create_checkout_fields($step['fields']), array(
		'title' => $display_title ? $step['name'] : '',
		'views' => 'checkout/fields',
	));

	/**
	 * Render form fields.
	 *
	 * @see /view/checkout/fields/ for the template files for each field type.
	 * @since 2.0.0
	 */
	$form->render();

	/**
	 * Add a security nonce field.
	 */
	wp_nonce_field('wu_checkout');

	?>

	<input type="hidden" name="checkout_action" value="wu_checkout">

	<input type="hidden" name="checkout_step" value="<?php echo esc_attr($step_name); ?>">

	<input type="hidden" name="checkout_form" value="<?php echo esc_attr($checkout_form_name); ?>">

</form>

<?php

/**
 * Renders additional things after the form ios over.
 */
do_action("wu_checkout_{$checkout_form_name}_after_form");

?>

<?php
/**
 * Confirm paypal view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-confirm-details wu-styling" id="billing_info">

	<h3><?php _e('Please confirm your payment', 'wp-ultimo'); ?></h3>

	<p class="wu-mt-4">

		<strong>

			<?php echo sprintf('%s %s', wu_get_isset($checkout_details, 'FIRSTNAME', ''), wu_get_isset($checkout_details, 'LASTNAME', '')); ?>

		</strong>

		<br />
		
		<?php _e('PayPal Status:', 'wp-ultimo'); ?> <?php echo ucfirst(wu_get_isset($checkout_details, 'PAYERSTATUS', 'none')); ?><br />
		
		<?php _e('Email:', 'wp-ultimo'); ?> <?php echo wu_get_isset($checkout_details, 'EMAIL', '--'); ?>
		
	</p>
</div>

<table>

	<thead class="wu-bg-gray-100">

		<tr>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php _e('Product', 'wp-ultimo'); ?></th>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php _e('Total', 'wp-ultimo'); ?></th>
		</tr>

	</thead>

	<tbody>
		
		<?php foreach ($payment->get_line_items() as $line_item) : ?>

			<tr>

				<td class="wu-py-2 wu-px-4">
					<?php echo $line_item->get_title(); ?>
					<code class="wu-ml-1">x<?php echo $line_item->get_quantity(); ?></code>
				</td>

				<td class="wu-py-2 wu-px-4">
					<?php echo wu_format_currency($line_item->get_subtotal(), $payment->get_currency()); ?>
				</td>

			</tr>

		<?php endforeach; ?>

	</tbody>

	<tfoot class="wu-bg-gray-100">

		<tr>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php _e('Subtotal', 'wp-ultimo'); ?></th>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php echo wu_format_currency($payment->get_subtotal(), $payment->get_currency()); ?></th>
		</tr>

		<?php foreach ($payment->get_tax_breakthrough() as $rate => $total) : ?>

			<tr>
				<th class="wu-text-left wu-py-2 wu-px-4"><?php printf(__('Tax (%s%%)', 'wp-ultimo'), $rate); ?></th>
				<th class="wu-text-left wu-py-2 wu-px-4"><?php echo wu_format_currency($total, $payment->get_currency()); ?></th>
			</tr>
			
		<?php endforeach; ?>

		<tr>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php _e('Total', 'wp-ultimo'); ?></th>
			<th class="wu-text-left wu-py-2 wu-px-4"><?php echo wu_format_currency($payment->get_total(), $payment->get_currency()); ?></th>
		</tr>

	</tfoot>

</table>

<form id="wu-paypal-express-confirm-form" action="<?php echo esc_url(add_query_arg('wu-confirm', 'paypal')); ?>" method="post">

	<input type="hidden" name="confirmation" value="yes" />
	<input type="hidden" name="token" value="<?php echo esc_attr($_GET['token']); ?>" />
	<input type="hidden" name="payer_id" value="<?php echo esc_attr($_GET['PayerID']); ?>" />
	<input type="hidden" name="wu_ppe_confirm_nonce" value="<?php echo wp_create_nonce('wu-ppe-confirm-nonce'); ?>"/>
	<input type="submit" class="wu-button" value="<?php esc_attr_e('Confirm', 'wp-ultimo'); ?>" />

</form>

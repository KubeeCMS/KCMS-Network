<?php
/**
 * Confirm paypal view.
 *
 * @since 2.0.0
 */
?>
<div class="rcp-confirm-details" id="billing_info">

	<h3><?php _e( 'Please confirm your payment', 'wp-ultimo' ); ?></h3>

	<p><strong><?php echo isset( $checkout_details['FIRSTNAME'] ) ? esc_html( $checkout_details['FIRSTNAME'] ) : ''; ?> <?php echo isset( $checkout_details['LASTNAME'] ) ? esc_html( $checkout_details['LASTNAME'] ) : ''; ?></strong><br />
	<?php _e( 'PayPal Status:', 'wp-ultimo' ); ?> <?php echo isset( $checkout_details['PAYERSTATUS'] ) ? esc_html( $checkout_details['PAYERSTATUS'] ) : ''; ?><br />
	<?php _e( 'Email:', 'wp-ultimo' ); ?> <?php echo isset( $checkout_details['EMAIL'] ) ? esc_html( $checkout_details['EMAIL'] ) : ''; ?></p>
</div>

<table id="order_summary" class="rcp-table">
	<thead>
		<tr>
			<th><?php _e( 'Description', 'wp-ultimo' ); ?></th>
			<th><?php _e( 'Amount', 'wp-ultimo' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-th="<?php esc_attr_e( 'Subscription', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-name"><?php echo isset( $checkout_details['DESC'] ) ? esc_html( $checkout_details['DESC'] ) : esc_html( $payment->subscription ); ?></td>
			<td data-th="<?php esc_attr_e( 'Subtotal', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-price"><?php echo ( $membership->get_amount()); ?></td>
		</tr>
	</tbody>
	<tfoot>
		<?php if ( !empty( $payment->discount_amount ) ) : ?>
			<tr>
				<th scope="row" class="rcp-ppe-confirm-name"><?php _e( 'Discount:', 'wp-ultimo' ); ?></th>
				<td data-th="<?php esc_attr_e( 'Discount', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-price"><?php echo ( -1 * abs( $payment->discount_amount )); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( !empty( $payment->fees ) ) : ?>
			<tr>
				<th scope="row" class="rcp-ppe-confirm-name"><?php _e( 'Fees:', 'wp-ultimo' ); ?></th>
				<td data-th="<?php esc_attr_e( 'Fees', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-price"><?php echo ( $payment->fees); ?></td>
			</tr>
		<?php endif; ?>
		<?php if ( !empty( $payment->credits ) ) : ?>
			<tr>
				<th scope="row" class="rcp-ppe-confirm-name"><?php _e( 'Credits:', 'wp-ultimo' ); ?></th>
				<td data-th="<?php esc_attr_e( 'Credits', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-price"><?php echo ( -1 * abs( $payment->credits )); ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<th scope="row" class="rcp-ppe-confirm-name"><?php _e( 'Total Today:', 'wp-ultimo' ); ?></th>
			<td data-th="<?php esc_attr_e( 'Total Today', 'wp-ultimo' ); ?>" class="rcp-ppe-confirm-price"><?php echo ( $payment->amount); ?></td>
		</tr>
		<?php if ( !empty( $_GET['rcp-recurring'] ) ) : ?>
			<?php
			if ( $membership->get_duration() == 1 ) {
				$recurring_heading = sprintf( __( 'Total Recurring Per %s:', 'wp-ultimo' ), wu_filter_duration_unit( $membership->get_duration_unit(), 1 ) );
			} else {
				$recurring_heading = sprintf( __( 'Total Recurring Every %1$s %2$s:', 'wp-ultimo' ), $membership->get_duration(), wu_filter_duration_unit( $membership->get_duration_unit(), $membership->get_duration() ) );
			} // end if;
			?>
			<tr>
				<th scope="row" class="rcp-ppe-confirm-name"><?php echo $recurring_heading; ?></th>
				<td data-th="<?php echo esc_attr( $recurring_heading ); ?>" class="rcp-ppe-confirm-price"><?php echo ( $checkout_details['PAYMENTREQUEST_0_AMT']); // @todo ?></td>
			</tr>
		<?php endif; ?>
	</tfoot>
</table>

<form id="rcp-paypal-express-confirm-form" action="<?php echo esc_url(add_query_arg('wu-confirm', 'paypal')); ?>" method="post">

	<input type="hidden" name="confirmation" value="yes" />
	<input type="hidden" name="token" value="<?php echo esc_attr($_GET['token']); ?>" />
	<input type="hidden" name="payer_id" value="<?php echo esc_attr($_GET['PayerID']); ?>" />
	<input type="hidden" name="wu_ppe_confirm_nonce" value="<?php echo wp_create_nonce('wu-ppe-confirm-nonce'); ?>"/>
	<input type="submit" class="rcp-button" value="<?php esc_attr_e('Confirm', 'wp-ultimo'); ?>" />

</form>

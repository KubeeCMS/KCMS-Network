<?php
/**
 * Manual Gateway.
 *
 * This gateway is the simplest one possible.
 * It doesn't do anything with the payments,
 * as they need to be manually approved by the super admin
 * but it serves as a good example of how
 * to implement a custom gateway for WP Ultimo.
 *
 * @package WP_Ultimo
 * @subpackage Gateways
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use \WP_Ultimo\Gateways\Base_Gateway;
use \WP_Ultimo\Database\Memberships\Membership_Status;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Manual Payments Gateway
 *
 * @since 2.0.0
 */
class Manual_Gateway extends Base_Gateway {

	/**
	 * Holds the ID of a given gateway.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	protected $id = 'manual';

	/**
	 * Adds the necessary hooks for the manual gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {
		/*
		 * Adds payment instructions to the thank you page.
		 */
		add_action('wu_thank_you_before_info_blocks', array($this, 'add_payment_instructions_block'), 10, 3);

	} // end hooks;

	/**
	 * Declares support to recurring payments.
	 *
	 * Manual payments need to be manually paid,
	 * so we return false here.
	 *
	 * @since 2.0.0
	 * @return false
	 */
	public function supports_recurring() {

		return false;

	} // end supports_recurring;

	/**
	 * Declares support to free trials
	 *
	 * @since 2.0.0
	 * @return false
	 */
	public function supports_free_trials() {

		return false;

	} // end supports_free_trials;

	/**
	 * Adds the Stripe Gateway settings to the settings screen.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {

		wu_register_settings_field('payment-gateways', 'manual_header', array(
			'title'           => __('Manual', 'wp-ultimo'),
			'desc'            => __('Use the settings section below to configure the manual payment method. This method allows your customers to manually pay for their memberships, but those payments require manual confirmation on your part.', 'wp-ultimo'),
			'type'            => 'header',
			'show_as_submenu' => true,
			'require'         => array(
				'active_gateways' => 'manual',
			),
		));

		wu_register_settings_field('payment-gateways', 'manual_payment_instructions', array(
			'title'      => __('Payment Instructions', 'wp-ultimo'),
			'desc'       => __('This instructions will be shown to the customer on the thank you page, as well as be sent via email.', 'wp-ultimo'),
			'type'       => 'wp_editor',
			'allow_html' => true,
			'default'    => __('Payment instructions here.', 'wp-ultimo'),
			'require'    => array(
				'active_gateways' => 'manual',
			),
		));

	}  // end settings;

	/**
	 * Process a checkout.
	 *
	 * It takes the data concerning
	 * a new checkout and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @param \WP_Ultimo\Checkout\Cart     $cart The cart object.
	 * @param string                       $type The checkout type. Can be 'new', 'retry', 'upgrade', 'downgrade', 'addon'.
	 *
	 * @throws \Exception When saving a membership fails.
	 *
	 * @return bool
	 */
	public function process_checkout($payment, $membership, $customer, $cart, $type) {
		/*
		 * Let's lay out the payment process logic in here.
		 * Basically, it all depends on the cart object, cart type, the membership that was created
		 * and the pending payment that was created.
		 *
		 * With that info, we can decide what to do with a given cart
		 * and process that with the gateway accordingly.
		 *
		 * For the manual payments gateway, the payment keeps it's pending status
		 * So we don't need to do anything on the cart_type = new.
		 *
		 * We have 6 different cart types:
		 *
		 * - display:   This type should not be processed at all. These carts are
		 *              created to be able to display itemized list tables in pages when that's
		 *              required.
		 * - new:       This is the first time registration of a new membership. In cases like this,
		 *              we should handle the first payment with the additional fees, if they exist,
		 *              as well as setup recurring profiles, if necessary.
		 * - renewal:   This type of cart handles a membership renewal. In here, you should also check
		 *              for an auto_renew flag, to be able to set up a auto-recurring profile, if needed.
		 * - upgrade:   This cart changes the membership plan. It is the same for downgrade.
		 * - downgrade: @see upgrade.
		 * - retry:     This cart is created when a customer tries to settle a pending or failed payment.
		 * - addon:     Contains only services or packages that should be added to the membership in question.
		 */
		$status = true;

		/*
		 * We'll organize the code into if-else statements
		 * to make things more spaced and allow for
		 * greater readability, but there's nothing
		 * preventing you from using a simple switch statement
		 * to deal with the different types.
		 *
		 * This gateway relies on the super admin manually
		 * approving a payment, so there isn't any logic to take care in here.
		 *
		 * The exception are memberships that contain
		 * free trials.
		 *
		 * This will not be the case for other gateways.
		 * See the stripe implementation of this method more
		 * a better example.
		 *
		 * If you wish to stop the process at any point
		 * due to some error, API failure or such,
		 * simply throw a exception and WP Ultimo will
		 * catch it and rollback any changes.
		 */
		if ($type === 'new') {

			// Your logic here.

		} elseif ($type === 'renewal') {

			// Your logic here.

		} elseif ($type === 'downgrade') {
			/*
			 * When downgrading, we need to schedule a swap for the end of the
			 * current expiration date.
			 */
			$membership->schedule_swap($cart);

			/*
			 * Mark the membership as pending, as we need to
			 * wait for the payment confirmation.
			 */
			$membership->set_status(Membership_Status::ON_HOLD);

			/*
			 * Saves the membership with the changes.
			 */
			$status = $membership->save();

		} elseif ($type === 'upgrade' || $type === 'addon') {
			/*
			* After everything is said and done,
			* we need to swap the membership to the new products
			* (plans and addons), and save it.
			*
			* The membership swap method takes in a Cart object
			* and handled all the changes we need to make to the
			* membership.
			*
			* It updates the products, the recurring status,
			* the initial and recurring amounts, etc.
			*
			* It doesn't save the membership, though, so
			* you'll have to do that manually (example below).
			*/
			$membership->swap($cart);

			/*
			 * Mark the membership as pending, as we need to
			 * wait for the payment confirmation.
			 */
			$membership->set_status(Membership_Status::ON_HOLD);

			/*
			 * Saves the membership with the changes.
			 */
			$status = $membership->save();

		} // end if;

		/*
		 * We want to check the status
		 * for a possible wp_error.
		 *
		 * If that happens, we need to throw an exception
		 * WP Ultimo will capture that exception and
		 * rollback database changes for us,
		 * to avoid problems with data integrity.
		 *
		 * That means that if you throw an exception in here,
		 * every change made to memberships, payments and such
		 * will be undone, including the swap above.
		 */
		if (is_wp_error($status)) {

			throw new \Exception($status->get_error_message(), $status->get_error_code());

		} // end if;

		/*
		 * You don't need to return anything,
		 * but if you return false from this method,
		 * WP Ultimo will assume that you want to handle redirection
		 * and such by yourself.
		 *
		 * This can be useful for some gateways that require
		 * extra redirects.
		 */
		return true;

	} // end process_checkout;

	/**
	 * Process a cancellation.
	 *
	 * It takes the data concerning
	 * a membership cancellation and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void|bool
	 */
	public function process_cancellation($membership, $customer) {} // end process_cancellation;

	/**
	 * Process a checkout.
	 *
	 * It takes the data concerning
	 * a refund and process it.
	 *
	 * @since 2.0.0
	 *
	 * @param float                        $amount The amount to refund.
	 * @param \WP_Ultimo\Models\Payment    $payment The payment associated with the checkout.
	 * @param \WP_Ultimo\Models\Membership $membership The membership.
	 * @param \WP_Ultimo\Models\Customer   $customer The customer checking out.
	 * @return void|bool
	 */
	public function process_refund($amount, $payment, $membership, $customer) {

		$status = $payment->refund($amount);

		if (is_wp_error($status)) {

			throw new \Exception($status->get_error_code(), $status->get_error_message());

		} // end if;

	} // end process_refund;

	/**
	 * Adds additional fields to the checkout form for a particular gateway.
	 *
	 * In this method, you can either return an array of fields (that we will display
	 * using our form display methods) or you can return plain HTML in a string,
	 * which will get outputted to the gateway section of the checkout.
	 *
	 * @since 2.0.0
	 * @return array|string
	 */
	public function fields() {

		$message = __('After you finish signing up, we will send you an email with instructions to finalize the payment. Your account will be pending until the payment is finalized and confirmed.', 'wp-ultimo');

		return sprintf('<p v-if="!order.has_trial" class="wu-p-4 wu-bg-yellow-200">%s</p>', $message);

	} // end fields;

	/**
	 * Adds the payment instruction block.
	 *
	 * @since 2.0.0
	 *
	 * @param \WP_Ultimo\Models\Payment    $payment The current payment.
	 * @param \WP_Ultimo\Models\Membership $membership the current membership.
	 * @param \WP_Ultimo\Models\Customer   $customer the current customer.
	 * @return void
	 */
	public function add_payment_instructions_block($payment, $membership, $customer) {

		if ($payment->get_gateway() !== $this->id) {

			return;

		} // end if;

		// phpcs:disable

		if ($payment->get_total() > 0 && $payment->get_status() === 'pending') : ?>

			<!-- Instructions for Payment -->
			<div id="wu-thank-you-instructions-for-payment">

				<!-- Title Element -->
				<div class="wu-element-header wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100'); ?>">        

					<h4 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

						<?php _e('Instructions for Payment', 'wp-ultimo'); ?>

					</h4>

				</div>
				<!-- Title Element - End -->

				<!-- Body Content -->
				<div class="wu-thank-you-instructions-for-payment wu-px-4 wu-mb-4">

					<div class="wu-bg-gray-100 wu-rounded wu-p-4">

						<?php echo do_shortcode(wu_get_setting('manual_payment_instructions')); ?>

					</div>

				</div>
				<!-- Body Content - End -->

			</div>
			<!-- Instructions for Payment - End -->

		<?php endif;

		// phpcs:enable

	} // end add_payment_instructions_block;

} // end class Manual_Gateway;

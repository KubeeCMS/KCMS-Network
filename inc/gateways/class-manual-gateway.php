<?php
/**
 * Base Gateway.
 *
 * Base Gateway class. Should be extended to add new payment gateways.
 *
 * @package WP_Ultimo
 * @subpackage Managers/Site_Manager
 * @since 2.0.0
 */

namespace WP_Ultimo\Gateways;

use WP_Ultimo\Gateways\Base_Gateway;

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Base Gateway class. Should be extended to add new payment gateways.
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
	 * Initialize the gateway configuration
	 *
	 * This is used to populate the $supports property, setup any API keys, and set the API endpoint.
	 *
	 * @access public
	 * @return void
	 */
	public function init() {

		$this->supports[] = 'one-time';
		$this->supports[] = 'fees';

	} // end init;

	/**
	 * Adds the necessary hooks for the manual gateway.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function hooks() {

		add_action('wu_thank_you_before_info_blocks', array($this, 'add_payment_instructions_block'), 10, 3);

	} // end hooks;

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
	 * Adds the payment instruction block
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
				<div class="wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100'); ?>">        

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

	/**
	 * Process registration
	 *
	 * This is where you process the actual payment. If non-recurring, you'll want to use
	 * the $this->initial_amount value. If recurring, you'll want to use $this->initial_amount
	 * for the first payment and $this->amount for the recurring amount.
	 *
	 * After a successful payment, redirect to $this->return_url.
	 *
	 * @access public
	 * @return void
	 */
	public function process_signup() {

	} // end process_signup;

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

		return '
		<p class="wu-p-4 wu-bg-yellow-200">
			After you finish signing up, we will send you an email with instructions to finalize the payment. Your account will be pending until the payment is finalized and confirmed.
		</p>
		';

	} // end fields;

} // end class Manual_Gateway;

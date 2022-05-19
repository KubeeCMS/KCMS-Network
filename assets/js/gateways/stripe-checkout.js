/* eslint-disable */
/* global wu_stripe_checkout, Stripe */

const stripeCheckout = function(publicKey) {

  wp.hooks.addAction('wu_on_form_success', 'nextpress/wp-ultimo', async function(checkout, results) {

    if (checkout.gateway === 'stripe-checkout' && results.gateway.slug !== 'free') {

      // When the customer clicks on the button, redirect
      // them to Checkout.
      const stripe = await Stripe(publicKey);

      stripe.redirectToCheckout({
        sessionId: results.gateway.data.stripe_session_id,
      })
        .then(function(result) {
          if (result.error) {

            console.log(result.error.message);

            var displayError = document.getElementById('error-message');

            displayError.textContent = result.error.message;

          }
        });
      
    } // end if;

  });

};

/**
 * Initializes the Stripe checkout onto the checkout form on load.
 */
wp.hooks.addAction('wu_checkout_loaded', 'nextpress/wp-ultimo', function() {

  stripeCheckout(wu_stripe_checkout.pk_key);

});
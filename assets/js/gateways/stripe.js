/* eslint-disable */
/* global wu_stripe, Stripe */
let _stripe;
let stripeElement;
let card;

const stripeElements = function(publicKey) {

  _stripe = Stripe(publicKey);

  const elements = _stripe.elements();

  card = elements.create('card', {
    hidePostalCode: true,
  });

  wp.hooks.addFilter('wu_before_form_submitted', 'nextpress/wp-ultimo', function(promises, checkout, gateway) {

    if (gateway === 'stripe' && checkout.order.totals.total > 0) {

      promises.push(new Promise( async (resolve, reject) => {

        try {

          const paymentMethod = await _stripe.createPaymentMethod({type: 'card', card});

          if (paymentMethod.error) {
            
            reject(paymentMethod.error);
    
          } // end if;

        } catch(err) {

        } // end try;

        resolve();

      }));

    } // end if;

    return promises;
      
  });

  wp.hooks.addAction('wu_on_form_success', 'nextpress/wp-ultimo', function(checkout, results) {

    if (checkout.gateway === 'stripe' && checkout.order.totals.total > 0) {
      
      handlePayment(null, results, card);

    } // end if;

  });
  
  wp.hooks.addAction('wu_on_form_updated', 'nextpress/wp-ultimo', function(form) {

    if (form.gateway === 'stripe') {

      try {

        card.mount('#card-element');

        wu_stripe_update_styles(card, '#field-payment_template');

        /*
         * Prevents the from from submitting while Stripe is 
         * creating a payment source.
         */
        form.set_prevent_submission(form.order && form.order.should_collect_payment && form.payment_method === 'add-new');

      } catch (error) {

        // Silence

      } // end try;

    } else {

      form.set_prevent_submission(false);

      try {

        card.unmount('#card-element');

      } catch (error) {

        // Silence is golden

      } // end try;

    } // end if;

  });

  // Element focus ring
  card.on('focus', function() {

    const el = document.getElementById('card-element');

    el.classList.add('focused');

  });

  card.on('blur', function() {

    const el = document.getElementById('card-element');

    el.classList.remove('focused');

  });

};

wp.hooks.addFilter('wu_before_form_init', 'nextpress/wp-ultimo', function(data) {

  data.add_new_card = wu_stripe.add_new_card;

  data.payment_method = wu_stripe.payment_method;

  return data;

});

wp.hooks.addAction('wu_checkout_loaded', 'nextpress/wp-ultimo', function() {

  stripeElement = stripeElements(wu_stripe.pk_key);

});

/**
 * Copy styles from an existing element to the Stripe Card Element.
 *
 * @param {Object} cardElement Stripe card element.
 * @param {string} selector Selector to copy styles from.
 *
 * @since 3.3
 */
function wu_stripe_update_styles(cardElement, selector) {

  if (undefined === typeof selector) {

    selector = '#field-payment_template';

  }

  const inputField = document.querySelector(selector);

  if (null === inputField) {

    return;

  }

  const inputStyles = window.getComputedStyle(inputField);

  const styleTag = document.createElement('style');

  styleTag.innerHTML = '.StripeElement {' +
    'background-color:' + inputStyles.getPropertyValue('background-color') + ';' +
    'border-top-color:' + inputStyles.getPropertyValue('border-top-color') + ';' +
    'border-right-color:' + inputStyles.getPropertyValue('border-right-color') + ';' +
    'border-bottom-color:' + inputStyles.getPropertyValue('border-bottom-color') + ';' +
    'border-left-color:' + inputStyles.getPropertyValue('border-left-color') + ';' +
    'border-top-width:' + inputStyles.getPropertyValue('border-top-width') + ';' +
    'border-right-width:' + inputStyles.getPropertyValue('border-right-width') + ';' +
    'border-bottom-width:' + inputStyles.getPropertyValue('border-bottom-width') + ';' +
    'border-left-width:' + inputStyles.getPropertyValue('border-left-width') + ';' +
    'border-top-style:' + inputStyles.getPropertyValue('border-top-style') + ';' +
    'border-right-style:' + inputStyles.getPropertyValue('border-right-style') + ';' +
    'border-bottom-style:' + inputStyles.getPropertyValue('border-bottom-style') + ';' +
    'border-left-style:' + inputStyles.getPropertyValue('border-left-style') + ';' +
    'border-top-left-radius:' + inputStyles.getPropertyValue('border-top-left-radius') + ';' +
    'border-top-right-radius:' + inputStyles.getPropertyValue('border-top-right-radius') + ';' +
    'border-bottom-left-radius:' + inputStyles.getPropertyValue('border-bottom-left-radius') + ';' +
    'border-bottom-right-radius:' + inputStyles.getPropertyValue('border-bottom-right-radius') + ';' +
    'padding-top:' + inputStyles.getPropertyValue('padding-top') + ';' +
    'padding-right:' + inputStyles.getPropertyValue('padding-right') + ';' +
    'padding-bottom:' + inputStyles.getPropertyValue('padding-bottom') + ';' +
    'padding-left:' + inputStyles.getPropertyValue('padding-left') + ';' +
    '}';

  document.body.appendChild(styleTag);

  cardElement.update({
    style: {
      base: {
        color: inputStyles.getPropertyValue('color'),
        fontFamily: inputStyles.getPropertyValue('font-family'),
        fontSize: inputStyles.getPropertyValue('font-size'),
        fontWeight: inputStyles.getPropertyValue('font-weight'),
        fontSmoothing: inputStyles.getPropertyValue('-webkit-font-smoothing'),
      },
    },
  });

}

function wu_stripe_handle_intent(handler, client_secret, args, card) {

  const _handle_error = function (e) {

    wu_checkout_form.unblock();

    if (e.error) {

      wu_checkout_form.errors.push(e.error);
      
    } // end if;

  } // end _handle_error;

  try {

    if (!args.payment_method) {

      _stripe[handler](client_secret, card, args).then(function(results) {
        
        if (results.error) {

          _handle_error(results);

          return;

        } // end if;

        wu_checkout_form.resubmit();

      }, _handle_error);

    } // end if;

    _stripe[handler](client_secret, args).then(function(results) {
      
      if (results.error) {

        _handle_error(results);

        return;

      } // end if;
      
      wu_checkout_form.resubmit();

    }, _handle_error);

  } catch(e) {} // end if;

} // end if;

/**
 * After registration has been processed, handle card payments.
 *
 * @param form
 * @param response
 * @param card
 */
function handlePayment(form, response, card) {

  // Trigger error if we don't have a client secret.
  if (! response.gateway.data.stripe_client_secret) {

    return;

  } // end if;

  const handler = 'payment_intent' === response.gateway.data.stripe_intent_type ? 'handleCardPayment' : 'handleCardSetup';

  const args = {
    payment_method_data: {
      billing_details: { 
        name: response.customer.display_name,
        email: response.customer.user_email,
        address: {
          country: response.customer.billing_address_data.billing_country,
          postal_code: response.customer.billing_address_data.billing_zip_code,
        },
      },
    },
  };

  /**
   * Handle payment intent / setup intent.
   */
  wu_stripe_handle_intent(
    handler, response.gateway.data.stripe_client_secret, args, card
  );

}

/* eslint-disable */
/* global wu_stripe, Stripe */
let stripe;

const stripeElements = function(publicKey) {

  stripe = Stripe(publicKey);

  const elements = stripe.elements();

  const card = elements.create('card', {
    hidePostalCode: true,
  });

  jQuery('body').on('wu_on_form_success', function(e, checkout, results) {

    if (checkout.gateway === 'stripe') {

      // createPaymentMethod(stripe, card, checkout);
      handlePayment(e, null, results, card);

    }

  });

  jQuery('body').on('wu_on_form_updated', function(e, form) {

    if (form.gateway === 'stripe' && form.payment_method === 'add-new') {

      try {

        card.mount('#card-element');

        wu_stripe_update_styles(card, '#field-payment_template');

      } catch (error) {

        // Silence

      }

    } else {

      try {

        card.unmount('#card-element');

      } catch (error) {

        // Silence is golden

      }

    }

  });

  // var idealBank = elements.create("idealBank");

  // idealBank.mount("#ideal-bank-element");

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

jQuery('body').on('wu_before_form_init', function(e, data) {

  data.add_new_card = wu_stripe.add_new_card;

  data.payment_method = wu_stripe.payment_method;

});

jQuery('body').on('wu_checkout_loaded', function(e) {

  stripeElements(wu_stripe.pk_key);

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

  if (! args.payment_method) {

    return stripe[handler](client_secret, card, args);

  }

  return stripe[handler](client_secret, args);

}

/**
 * After registration has been processed, handle card payments.
 *
 * @param event
 * @param form
 * @param response
 * @param card
 */
function handlePayment(event, form, response, card) {

  // Trigger error if we don't have a client secret.
  if (! response.gateway.data.stripe_client_secret) {

    return;

  }

  const cardHolderName = 'Test Emerson'; //$('.card-name').val();

  const handler = 'payment_intent' === response.gateway.data.stripe_intent_type ? 'handleCardPayment' : 'handleCardSetup';

  const args = {
    payment_method_data: {
      billing_details: { name: cardHolderName },
    },
  };

  // if (RCP_Stripe_Registration.paymentMethodID) {
  //   args = {
  //     payment_method: RCP_Stripe_Registration.paymentMethodID
  //   };
  // }

  /**
   * Handle payment intent / setup intent.
   */
  wu_stripe_handle_intent(
    handler, response.gateway.data.stripe_client_secret, args, card
  ).then(function(paymentResult) {

  });

}

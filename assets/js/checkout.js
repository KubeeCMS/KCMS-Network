/* global Vue, moment, _, wu_checkout, pwsL10n, wu_checkout_form */
(function($) {

  /**
   * Allows for cross-sells
   */
  $(document).on('click', '[href|="#wu-checkout-add"]', function(event) {

    event.preventDefault();

    const el = $(this);

    const product_slug = el.attr('href').split('#').pop().replace('wu-checkout-add-', '');

    if (typeof wu_checkout_form !== 'undefined') {

      if (wu_checkout_form.products.indexOf(product_slug) === -1) {

        wu_checkout_form.add_product(product_slug);

        el.html(wu_checkout.i18n.added_to_order);

      } // end if;

    } // end if;

  });

  /**
   * Setup
   */
  $(document).ready(function() {

    /*
     * Prevent app creation when vue is not available.
     */
    if (typeof window.Vue === 'undefined') {

      return;

    } // end if;

    Object.defineProperty(Vue.prototype, '$moment', { value: moment });

    const initial_data = {
      errors: [],
      order: false,
      products: wu_checkout.products,
      template_id: 0,
      template_category: '',
      gateway: wu_checkout.gateway,
      request_billing_address: wu_checkout.request_billing_address,
      country: wu_checkout.country,
      discount_code: '',
      toggle_discount_code: 0,
      payment_method: '',
      username: '',
    };

    jQuery('body').trigger('wu_before_form_init', [initial_data]);

    if (! jQuery('#wu_form').length) {

      return;

    } // end if;

    // eslint-disable-next-line no-unused-vars
    window.wu_checkout_form = new Vue({
      el: '#wu_form',
      data: initial_data,
      methods: {
        remove_product(product_id, product_slug) {

          this.products = _.filter(this.products, function(item) {

            // eslint-disable-next-line eqeqeq
            return item != product_id && item != product_slug;

          });

        },
        add_product(product_id) {

          this.products.push(product_id);

        },
        wu_format_money(value) {

          return window.wu_format_money(value);

        },
        create_order() {

          /*
           * Bail if there is no order summary to update.
           */
          if (! jQuery('#wu-order-summary-content').length) {

            return;

          } // end if;

          this.block();

          this.order = false;

          const that = this;

          this.request('wu_create_order', {
            products: that.products,
            gateway: that.gateway,
            discount_code: that.discount_code,
            country: that.country,
          }, function(results) {

            that.order = results.data.order;

            that.unblock();

          }, this.handle_errors);

        },
        get_errors() {

          const result = this.errors.map(function(e) {

            return e.message;

          });

          return result.length > 0 ? result : false;

        },
        get_error(field) {

          const result = this.errors.filter(function(e) {

            return e.code === field;

          });

          return result.length > 0 ? result[0] : false;

        },
        form_success(results) {

          if (! _.isEmpty(results.data)) {

            jQuery('body').trigger('wu_on_form_success', [this, results.data]);

            const fields = results.data.gateway.data;

            fields.payment_id = results.data.payment_id;

            jQuery.each(results.data.memberships, function(index, value) {

              fields['memberships[]'] = parseInt(value.id, 10);

            });

            // Append the hidden fields
            jQuery.each(Object.assign({}, fields), function(index, value) {

              const hidden = document.createElement('input');

              hidden.type = 'hidden';

              hidden.name = index;

              hidden.value = value;

              jQuery('#wu_form').append(hidden);

            });

          } // end if;

        },
        validate_form() {

          let form_data = jQuery('#wu_form').serialize();

          form_data += '&' + jQuery.param({
            products: this.products,
          });

          this.errors = [];

          const that = this;

          this.request('wu_validate_form', form_data, function(results) {

            if (results.success === false) {

              that.errors = results.data;

              that.unblock();

              return;

            } // end if;

            if (! that.errors.length) {

              that.form_success(results);

              jQuery('#wu_form').get(0).submit();

            } // end if;

          }, this.handle_errors);

        },
        handle_errors(errors) {

          this.unblock();

          // eslint-disable-next-line no-console
          console.error(errors);

        },
        on_submit(event) {

          event.preventDefault();

        },
        on_change_product(new_value, old_value) {

          jQuery('body').trigger('wu_on_change_product', [new_value, old_value, this]);

          this.create_order();

        },
        on_change_gateway(new_value, old_value) {

          jQuery('body').trigger('wu_on_change_gateway', [new_value, old_value, this]);

        },
        on_change_country(new_value, old_value) {

          jQuery('body').trigger('wu_on_change_country', [new_value, old_value, this]);

          this.create_order();

        },
        on_change_discount_code(new_value, old_value) {

          jQuery('body').trigger('wu_on_change_discount_code', [new_value, old_value, this]);

          this.create_order();

        },
        block() {

          /*
           * Get the first bg color from a parent.
           */
          const bg_color = jQuery(this.$el).parents().filter(function() {

            return $(this).css('backgroundColor') !== 'rgba(0, 0, 0, 0)';

          }).first().css('backgroundColor');

          jQuery(this.$el).block({
            message: '<div class="spinner is-active wu-float-none" style="float: none !important;"></div>',
            overlayCSS: {
              backgroundColor: bg_color ? bg_color : '#ffffff',
              opacity: 0.6,
            },
            css: {
              padding: 0,
              margin: 0,
              width: '50%',
              fontSize: '14px !important',
              top: '40%',
              left: '35%',
              textAlign: 'center',
              color: '#000',
              border: 'none',
              backgroundColor: 'none',
              cursor: 'wait',
            },
          });

        },
        unblock() {

          jQuery(this.$el).unblock();

        },
        request(action, data, success_handler, error_handler) {

          jQuery.ajax({
            method: 'POST',
            url: wu_checkout.ajaxurl + '?action=' + action,
            data,
            success: success_handler,
            error: error_handler,
          });

        },
      },
      updated() {

        this.$nextTick(function() {

          jQuery('body').trigger('wu_on_form_updated', [this]);

        });

      },
      mounted() {

        const that = this;

        jQuery(this.$el).on('click', function(e) {

          $(this).data('submited_via', $(e.target));

        });

        jQuery(this.$el).on('submit', function(e) {

          e.preventDefault();

          /**
           * Handle button submission.
           */
          const submit_el = jQuery(this).data('submited_via');

          if (submit_el) {

            const new_input = jQuery('<input>');

            new_input.attr('type', 'hidden');

            new_input.attr('name', submit_el.attr('name'));

            new_input.attr('value', submit_el.val());

            jQuery(this).append(new_input);

          } // end if;

          that.block();

          that.validate_form();

          jQuery('body').trigger('wu_on_form_submitted', that, that.gateway);

        });

        this.create_order();

        jQuery('body').trigger('wu_checkout_loaded');

        jQuery('body').trigger('wu_on_change_gateway', this.gateway, this.gateway);

      },
      watch: {
        products(new_value, old_value) {

          this.on_change_product(new_value, old_value);

        },
        discount_code(new_value, old_value) {

          this.on_change_discount_code(new_value, old_value);

        },
        gateway(new_value, old_value) {

          this.on_change_gateway(new_value, old_value);

        },
        country(new_value, old_value) {

          this.on_change_country(new_value, old_value);

        },
      },
    });

  });

}(jQuery));

function check_pass_strength(pass1_el) {

  const pass1 = jQuery(pass1_el).val();

  jQuery('#pass-strength-result')
    .attr('class', 'wu-py-2 wu-px-4 wu-bg-gray-100 wu-block wu-text-sm wu-border-solid wu-border wu-border-gray-200');

  if (! pass1) {

    jQuery('#pass-strength-result').addClass('empty').html('Enter Password');

    return;

  }

  const strength = wp.passwordStrength.meter(pass1, wp.passwordStrength.userInputBlacklist(), pass1);

  switch (strength) {

  case -1:
    jQuery('#pass-strength-result').addClass('wu-bg-red-200 wu-border-red-300').html(pwsL10n.unknown);

    break;

  case 2:
    jQuery('#pass-strength-result').addClass('wu-bg-red-200 wu-border-red-300').html(pwsL10n.bad);

    break;

  case 3:
    jQuery('#pass-strength-result').addClass('wu-bg-green-200 wu-border-green-300').html(pwsL10n.good);

    break;

  case 4:
    jQuery('#pass-strength-result').addClass('wu-bg-green-200 wu-border-green-300').html(pwsL10n.strong);

    break;

  case 5:
    jQuery('#pass-strength-result').addClass('wu-bg-yellow-200 wu-border-yellow-300').html(pwsL10n.mismatch);

    break;

  default:
    jQuery('#pass-strength-result').addClass('wu-bg-yellow-200 wu-border-yellow-300').html(pwsL10n.short);

  }

}

jQuery('body').on('wu_checkout_loaded', function() {

  jQuery('#field-password').on('input' + ' pwupdate', function() {

    check_pass_strength(this);

  });

});

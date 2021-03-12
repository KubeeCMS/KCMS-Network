/* global Vue, ajaxurl, wu_tax_rates, _ */
/**
 * Handles pulling from VAT quotes.
 *
 * @param {Object} event
 */
const wu_pull_vat_data = function wu_pull_vat_data(event) {

  const that = wu_tax_rates;

  wu_tax_rates.loading = true;

  event.preventDefault();

  // Start ajax Call
  jQuery
    .getJSON(
      ajaxurl +
      '?action=wu_get_eu_vat_tax_rates&rate_type=' +
      this.rate_type
    )
    .done(function(response) {

      that.loading = false;

      // Remove VAT
      const no_vat = _.reject(that.data[that.tax_category].rates, function(item) {

        return item.type === 'eu-vat';

      });

      that.data[that.tax_category].rates = no_vat.concat(response.data);

    })
    .fail(function(error) {

      that.loading = false;

      that.error = true;

      that.errorMessage = error.statusText;

    });

}; // end pull_vat_data;

// Listen for the event.
window.addEventListener('vue_loaded', function(e) {

  if (typeof e.vue !== 'undefined') {

    Object.defineProperty(Vue.prototype, 'pull_vat_data', {
      value: wu_pull_vat_data,
    });

  } // end if;

  // e.target matches window

}, false);

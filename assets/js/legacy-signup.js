(function($, hooks) {

  $(document).ready(function() {

    hooks.addFilter('wu_before_form_init', 'nextpress/wp-ultimo', function(data) {

      if (typeof data !== 'undefined') {

        data.billing_option = 1;

        data.default_billing_option = 12;

      } // end if;

      return data;

    });

  });

}(jQuery, wp.hooks));

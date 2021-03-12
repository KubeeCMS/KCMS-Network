(function($) {

  $(document).ready(function() {

    jQuery('body').on('wu_before_form_init', function(e, data) {

      data.billing_option = 1;

      data.default_billing_option = 12;

    });

  });

}(jQuery));

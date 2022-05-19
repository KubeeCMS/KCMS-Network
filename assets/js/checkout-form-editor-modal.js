/* global wu_modal_set_width */
wp.hooks.addAction('wu_add_checkout_form_field_mounted', 'nextpress/wp-ultimo', function(data) {

  if (data.type === '') {

    wu_modal_set_width(600);

  } // end if;

});

wp.hooks.addAction('wu_add_checkout_form_field_changed', 'nextpress/wp-ultimo', function(val, data) {

  if (data.type === '') {

    wu_modal_set_width(600);

  } else {

    wu_modal_set_width(400);

  }// end if;

});

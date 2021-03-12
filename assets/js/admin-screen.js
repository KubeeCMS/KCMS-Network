/* eslint-disable max-len */
/* global wu_admin_screen, wu_block_ui */
(function($) {

  $(document).ready(function() {

    $('body').on('click', '#wu-admin-screen-customize', function() {

      wu_block_ui('#wpcontent');

    });

    const is_edit_mode = $('body').hasClass('wu-customize-admin-screen');

    let $elem = `<a id="wu-admin-screen-customize" href="${ wu_admin_screen.customize_link }" class="button show-settings">${ wu_admin_screen.i18n.customize_label }</button>`;

    const $page_elem = `<a title="${ wu_admin_screen.i18n.page_customize_label }" id="wu-admin-screen-page-customize" href="${ wu_admin_screen.page_customize_link }" class="wubox button show-settings">${ wu_admin_screen.i18n.page_customize_label }</button>`;

    if (is_edit_mode) {

      $elem = `<a id="wu-admin-screen-customize" href="${ wu_admin_screen.close_link }" class="button show-settings wu-font-medium"><span class="wu-text-sm wu-align-text-bottom wu-text-red-500 wu-mr-2 wu--ml-1 dashicons-wu-circle-with-cross"></span>${ wu_admin_screen.i18n.close_label }</button>`;

    } else {

      $($page_elem).prependTo('#screen-options-link-wrap');

    } // end if;

    $($elem).appendTo('#screen-options-link-wrap');

  });

}(jQuery));

/* eslint-disable no-undef */
(function($) {

  $(document).ready(function() {

    // Adds WUChosen.js to our custom select input
    const $jumper = $('#wu-jumper-select').selectize({
      create: false,
      maxItems: 1,
      optgroupField: 'group',
      optgroupValueField: 'value',
      searchField: ['text', 'name', 'display_name', 'domain', 'title', 'desc', 'code'],
      render: {
        option(option) {

          if (typeof option.model === 'undefined') {

            option.model = 'jumper-link';

          } // end if;

          if (typeof option.text === 'undefined') {

            option.text = option.reference_code || option.name || option.title || option.display_name || option.code;

          } // end if;

          if (typeof option.group === 'undefined') {

            option.group = option.model;

          } // end if;

          const template_html = jQuery('#wu-template-' + option.model).length ?
            jQuery('#wu-template-' + option.model).html() :
            jQuery('#wu-template-default').html();

          const template = _.template(template_html, {
            interpolate: /\{\{(.+?)\}\}/g,
          });

          return template(option);

        },
      },
      load(query, callback) {

        if (! query.length) {

          return callback();

        } // end if;

        $('#wu-jumper .wu-jumper-loading').show();

        jQuery.ajax({
          // eslint-disable-next-line no-undef
          url: wu_jumper_vars.ajaxurl,
          type: 'POST',
          data: {
            action: 'wu_search',
            model: 'all',
            number: 99,
            query: {
              search: '*' + query + '*',
            },
          },
          error() {

            callback();

          },
          success(res) {

            $('#wu-jumper .wu-jumper-loading').hide();

            callback(res);

          },
        });

      },
    });

    const is_local_url = function(url) {

      return url.toLowerCase().indexOf(wu_jumper_vars.base_url) >= 0 || url.toLowerCase().indexOf(wu_jumper_vars.network_base_url) >= 0;

    }; // end is_local_url

    // Every time the value changes, we need to redirect the user
    $jumper.on('change', function() {

      // Check if we need to open this in a new tab
      if (is_local_url($(this).val())) {

        window.location.href = $(this).val();

        $(this).parent().parent().find('.wu-jumper-redirecting').show();

      } else {

        window.open($(this).val(), '_blank');

        $($jumper.parent()).hide();

      } // end if;

    });

    // Closes on clicking other elements
    $(document).on('click', ':not(#wu-jumper-button-trigger)', function(e) {

      const target = e.target;

      if ($(target).attr('id') === 'wu-jumper-button-trigger' || $(target).parent().attr('id') === 'wu-jumper-button-trigger') {

        return;

      } // end if;

      if (! $(target).is($jumper.parent()) && ! $(target).parents().is($jumper.parent())) {

        $($jumper.parent().parent()).hide();

      } // end if;

    });

    const trigger_key = wu_jumper_vars.trigger_key.charAt(0);

    // Our bar is hidden by default, we need to display it when a certain shortcut is pressed
    Mousetrap.bind(['command+option+' + trigger_key, 'ctrl+alt+' + trigger_key], function(e) {

      e.preventDefault();

      open_jumper();

    }); // end mousetrap;

    $(document).on('click', '#wu-jumper-button-trigger', function(e) {

      e.preventDefault();

      open_jumper();

    });

    /**
     * Actually opens the jumper.
     */
    function open_jumper() {

      $('#wu-jumper').show();

      $('#wu-jumper').find('input').focus();

      return false;

    } // end open_jumper;

  });

}(jQuery));


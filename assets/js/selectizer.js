/* global _ */
(function($) {

  $(document).ready(function() {

    jQuery('[data-selectize]').selectize();

    jQuery('[data-selectize-categories]').selectize({
      maxItems: 10,
      create(input) {

        return {
          value: input,
          text: input,
        };

      },
    });

    $.each($('[data-model]'), function(index, item) {

      wu_selector({
        el: item,
        valueField: $(item).data('value-field'),
        labelField: $(item).data('label-field'),
        searchField: $(item).data('search-field'),
        maxItems: $(item).data('max-items'),
        selected: $(item).data('selected'),
        options: [],
        data: {
          action: 'wu_search',
          model: $(item).data('model'),
          number: 10,
          exclude: $(item).data('exclude'),
          include: $(item).data('include'),
        },
      });

    });

  });

}(jQuery));

function wu_selector(options) {

  options = _.defaults(options, {
    options: [],
    maxItems: 1,
  });

  if (jQuery(options.el).data('init')) {

    return;

  } // end if;

  const select = jQuery(options.el).selectize({
    valueField: options.valueField,
    labelField: options.labelField,
    searchField: ['text', 'name', 'display_name', 'domain', 'title', 'desc', 'code', 'post_title', 'reference_code'],
    options: options.options,
    maxItems: options.maxItems,
    create: false,
    render: {
      option(option) {

        const template_html = jQuery('#wu-template-' + options.data.model).length ?
          jQuery('#wu-template-' + options.data.model).html() :
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

      jQuery.ajax({
        // eslint-disable-next-line no-undef
        url: ajaxurl,
        type: 'POST',
        data: {
          ...options.data,
          query: {
            search: '*' + query + '*',
          },
        },
        error() {

          callback();

        },
        success(res) {

          selectize.savedItems = res;

          callback(res.slice(0, 10));

        },
      });

    },
  });

  jQuery(options.el).attr('data-init', 1);

  const selectize = select[0].selectize;

  /*
   * Makes sure this is reactive for vue
   */
  selectize.on('change', function(value) {

    const input = jQuery(select[0]);

    const vue_app = input.parents('[data-wu-app]').data('wu-app');

    if (vue_app && typeof window['wu_' + vue_app] !== 'undefined') {

      window['wu_' + vue_app][input.attr('name')] = value;

    } // end if;

  });

  selectize.on('item_add', function(value) {

    let active_item = {
      url: null,
    };

    jQuery.each(selectize.savedItems, function(index, item) {

      if (item.setting_id === value) {

        active_item = item;

      } // end if;

    });

    if (active_item.url) {

      window.location.href = active_item.url;

    } // end if;

  });

  if (options.selected) {

    selectize.options = [];

    selectize.clearOptions();

    const selected_values = _.isArray(options.selected) ? options.selected : [options.selected];

    selectize.addOption(selected_values);

    const selected = _.isArray(options.selected) ? _.pluck(options.selected, options.valueField) : options.selected[options.valueField];

    selectize.setValue(selected, false);

  } // end if;

}

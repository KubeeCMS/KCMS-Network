/* eslint-disable no-undef */
/* eslint-disable no-unused-vars */
/* global wu_settings, wu_input_masks, wu_money_input_masks, Cleave, ClipboardJS, wu_fields, tinymce, wu_media_frame, fontIconPicker */
window.wu_initialize_tooltip = function() {

  jQuery('[role="tooltip"]').tipTip({
    attribute: 'aria-label',
  });

}; // end wu_initialize_tooltip;

window.wu_initialize_editors = function() {

  jQuery('textarea[data-editor]').each(function() {

    tinymce.remove('#' + jQuery(this).attr('id'));

    tinymce.init({
      selector: '#' + jQuery(this).attr('id'), // change this value according to your HTML
      menubar: '',
      theme: 'modern',
      ...wp.editor.getDefaultSettings().tinymce,
    });

  });

}; // end wu_initialize_editors

window.wu_initialize_imagepicker = function() {

  jQuery('.wu-wrapper-image-field').each(function() {

    const that = jQuery(this);

    that.find('img').css({
      maxWidth: '246px',
      maxHeight: '246px',
    });

    that.on('click', 'a.wu-add-image', function() {

      if (typeof wu_media_frame !== 'undefined') {

        wu_media_frame.open();

        return;

      } // end if;

      wu_media_frame = wp.media({
        title: wu_fields.l10n.image_picker_title,
        multiple: false,
        button: {
          text: wu_fields.l10n.image_picker_button_text,
        },
      });

      wu_media_frame.on('select', function() {

        const mediaObject = wu_media_frame.state().get('selection').first().toJSON();

        that.find('img').removeClass('wu-absolute').attr('src', mediaObject.url);

        that.find('input').val(mediaObject.id);

        that.find('.wu-remove-image').show();

      });

      wu_media_frame.open();

    });

    that.find('.wu-remove-image').on('click', function(e) {

      e.preventDefault();

      that.find('img').removeAttr('src').addClass('wu-absolute');

      that.find('input').val('');

      that.find('.wu-remove-image').hide();

    });

  });

}; // end wu_initialize_imagepicker

window.wu_initialize_colorpicker = function() {

  jQuery(document).ready(function() {

    jQuery('.wu_color_field').each(function() {

      jQuery(this).wpColorPicker();

    });

  });

}; // end wu_initialize_colorpicker;

window.wu_initialize_iconfontpicker = function() {

  jQuery(document).ready(function() {

    jQuery('.wu_select_icon').fontIconPicker({
      theme: 'wu-theme',
    });

  });

}; // end wu_initialize_iconfontpicker;

window.wu_initialize_clipboardjs = function() {

  new ClipboardJS('.wu-copy');

}; // end wu_initialize_clipboardjs;

// DatePicker;
window.wu_initialize_datepickers = function() {

  jQuery('.wu-datepicker, [wu-datepicker]').each(function() {

    const $this = jQuery(this);

    const format = $this.data('format'),
      allow_time = $this.data('allow-time');

    $this.flatpickr({
      animate: false,
      // locale: wpu.datepicker_locale,
      time_24hr: true,
      enableTime: typeof allow_time === 'undefined' ? true : allow_time,
      dateFormat: format,
      allowInput: true,
    });

  });

}; // end wu_initialize_datepickers;

window.wu_update_clock = function() {

  // eslint-disable-next-line no-undef
  const yourTimeZoneFrom = wu_ticker.server_clock_offset; //time zone value where you are at

  const d = new Date();
  //get the timezone offset from local time in minutes

  // eslint-disable-next-line no-mixed-operators
  const tzDifference = yourTimeZoneFrom * 60 + d.getTimezoneOffset();

  //convert the offset to milliseconds, add to targetTime, and make a new Date
  const offset = tzDifference * 60 * 1000;

  function callback_update_clock() {

    const tDate = new Date(new Date().getTime() + offset);

    const in_years = tDate.getFullYear();

    let in_months = tDate.getMonth() + 1;

    let in_days = tDate.getDate();

    let in_hours = tDate.getHours();

    let in_minutes = tDate.getMinutes();

    let in_seconds = tDate.getSeconds();

    if (in_months < 10) {

      in_months = '0' + in_months;

    }

    if (in_days < 10) {

      in_days = '0' + in_days;

    }

    if (in_minutes < 10) {

      in_minutes = '0' + in_minutes;

    }

    if (in_seconds < 10) {

      in_seconds = '0' + in_seconds;

    }

    if (in_hours < 10) {

      in_hours = '0' + in_hours;

    }

    jQuery('#wu-ticker').text(in_years + '-' + in_months + '-' + in_days + ' ' + in_hours + ':' + in_minutes + ':' + in_seconds);

  }

  function start_clock() {

    setInterval(callback_update_clock, 500);

  }

  start_clock();

};

function wu_initialize_input_masks() {

  /*
   * First, money inputs
   */

  if (jQuery('[data-money]').length) {

    let prefix_symbol = wu_settings.currency_symbol;

    let tail = false;

    if (wu_settings.currency_position === '%v%s') {

      tail = true;

    } else if (wu_settings.currency_position === '%s %v') {

      prefix_symbol += ' ';

    } else if (wu_settings.currency_position === '%v %s') {

      prefix_symbol = ' ' + prefix_symbol;

      tail = true;

    } // end if;

    jQuery('[data-money]').each(function() {

      wu_money_input_masks = new Cleave(this, {
        numeral: true,
        numericOnly: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: wu_settings.decimal_separator,
        delimiter: wu_settings.thousand_separator,
        delimiterLazyShow: true,
        prefix: prefix_symbol,
        tailPrefix: tail,
      });

    });

  } // end if;

  /*
  * First, money inputs
  */

  if (jQuery('[data-cleave]').length) {

    jQuery('[data-cleave]').each(function() {

      if (parseInt(jQuery(this).data('cleave'), 10) !== 1) {

        return;

      } // end if;

      wu_input_masks = new Cleave(this, {
        prefix: jQuery(this).data('prefix') || '',
        tailPrefix: jQuery(this).data('prefix-tail') || false,
      });

    });

  } // end if;

} // end wu_initialize_input_masks;

// eslint-disable-next-line no-unused-vars
function wu_on_load(vue) {

  wu_initialize_tooltip();

  wu_initialize_datepickers();

  wu_initialize_colorpicker();

  wu_initialize_iconfontpicker();

  wu_initialize_editors();

  wu_update_clock();

  wu_initialize_input_masks();

  wu_initialize_clipboardjs();

  wu_initialize_imagepicker();

  wu_image_preview('.wu-image-preview');

} // end wu_on_load;

window.wu_on_load = wu_on_load;

// eslint-disable-next-line no-unused-vars
window.wu_block_ui = function(el) {

  jQuery(el).block({
    message: '<div class="spinner is-active wu-float-none" style="float: none !important;"></div>',
    overlayCSS: {
      backgroundColor: '#FFF',
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

  return jQuery(el);

};

function wu_format_money(value) {

  value = parseFloat(value.toString().replace(/[^0-9\.]/g, ''));

  accounting.settings = {
    currency: {
      symbol: wu_settings.currency_symbol, // default currency symbol is '$'
      format: wu_settings.currency_position, // controls output: %s = symbol, %v = value/number (can be object: see below)
      decimal: wu_settings.decimal_separator, // decimal point separator
      thousand: wu_settings.thousand_separator, // thousands separator
      precision: wu_settings.precision, // decimal places
    },
    number: {
      precision: 0, // default precision on numbers is 0
      thousand: ',',
      decimal: ',',
    },
  };

  return accounting.formatMoney(value);

} // end wu_format_money;

function wu_modal_refresh() {

  let height = jQuery('.wu_form').height();

  const max_height = jQuery(window).height() - 150;

  height = height >= max_height ? max_height : height;

  // eslint-disable-next-line no-undef
  WUB_HEIGHT = height;

  jQuery(document).find('#WUB_ajaxContent').animate({
    height,
  }, 300, function() {

    jQuery(document).find('#WUB_window').animate({
      marginTop: '-' + parseInt((height / 2), 10) + 'px',
    }, 300);

  });

} // end wu_modal_refresh;

window.wu_image_preview = function(selector) {

  const xOffset = 10;

  const yOffset = 30;

  const preview_el = '#wu-image-preview';

  /* END CONFIG */
  jQuery(selector).hover(function(e) {

    this.t = this.title;

    this.title = '';

    jQuery(preview_el).remove();

    const img = jQuery(this).data('image');

    const el_id = preview_el.replace('#', '');

    jQuery('body').append(
      "<div id='" + el_id + "' class='wu-rounded wu-p-1 wp-ui-primary' style='max-width: 600px;'>" +
        "<img class='wu-rounded wu-block wu-m-0 wu-p-0 wu-bg-gray-100' style='max-width: 100%;' src='" + img + "' alt='" + this.t + "'>" +
      '</div>'
    );

    jQuery(preview_el)
      .css({
        position: 'absolute',
        display: 'none',
      })
      .css('top', (e.pageY - xOffset) + 'px')
      .css('left', (e.pageX + yOffset) + 'px')
      .fadeIn('fast');

  },
  function() {

    this.title = this.t;

    jQuery(preview_el).fadeOut('fast', function() {

      jQuery(this).remove();

    });

  });

  jQuery(selector).mousemove(function(e) {

    jQuery(preview_el)
      .css('top', (e.pageY - xOffset) + 'px')
      .css('left', (e.pageX + yOffset) + 'px');

  });

};

// eslint-disable-next-line no-undef
window.wu_initialize_code_editors = function() {

  if (jQuery('[data-code-editor]').length) {

    if (typeof window.wu_editor_instances === 'undefined') {

      window.wu_editor_instances = {};

    } // end if;

    jQuery('[data-code-editor]').each(function() {

      const code_editor = jQuery(this);

      const editor_id = code_editor.attr('id');

      if (typeof window.wu_editor_instances[editor_id] === 'undefined') {

        if (! code_editor.is(':visible')) {

          return;

        } // end if;

        window.wu_editor_instances[editor_id] = wp.codeEditor.initialize(editor_id, {
          codemirror: {
            mode: code_editor.data('code-editor'),
            lint: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 2,
            indentWithTabs: true,
            lineNumbers: true,
            lineWrapping: true,
            styleActiveLine: true,
            continueComments: true,
            inputStyle: 'contenteditable',
            direction: 'ltr', // Code is shown in LTR even in RTL languages.
            gutters: [],
            extraKeys: {
              'Ctrl-Space': 'autocomplete',
              'Ctrl-/': 'toggleComment',
              'Cmd-/': 'toggleComment',
              'Alt-F': 'findPersistent',
            },
          },
        });

      } // end if;

    });

  } // end if;

}; // end wu_initialize_code_editors;

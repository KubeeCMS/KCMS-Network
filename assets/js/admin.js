/* global wu_on_load, Vue, wu_format_money, wu_block_ui, _ */
(function($) {

  // eslint-disable-next-line no-undef
  wu = {
    tables: {},
    configs: {},
  };

  $(document).ready(function() {

    wu_on_load();

  });

}(jQuery));

(function($) {

  $(document).ready(function() {

    $('#scraper').on('click', function(e) {

      e.preventDefault();

      const block = wu_block_ui('#wp-ultimo-image-widget');

      $('.wu-scraper-note, .wu-scraper-error').hide();

      jQuery.ajax({
        // eslint-disable-next-line no-undef
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wu_get_screenshot',
          site_id: $('#id').val(),
        },
        error() {

          block.unblock();

        },
        success(res) {

          block.unblock();

          if (res.success) {

            $('#wp-ultimo-image-widget img').attr('src', res.data.attachment_url);

            $('#wp-ultimo-image-widget input').val(res.data.attachment_id);

            $('.wu-scraper-note').show();

          } else {

            $('.wu-scraper-error').show();

            $('.wu-scraper-error-message').text(res.data.pop().message);

          } // end if;

        },
      });

    });

    /**
     * ColorPicker Component
     */
    Vue.component('colorPicker', {
      props: ['value'],
      template: '<input type="text">',
      mounted() {

        const vm = this;

        $(this.$el)
          .val(this.value)
          // WordPress color picker
          .wpColorPicker({
            width: 200,
            defaultColor: this.value,
            change(event, ui) {

              // emit change event on color change using mouse
              vm.$emit('input', ui.color.toString());

            },
          });

      },
      watch: {
        value(value) {

          // update value
          $(this.$el).wpColorPicker('color', value);

        },
      },
      destroyed() {

        $(this.$el).off().wpColorPicker('destroy'); // (!) Not tested

      },
    });

    /**
     * WP Editor Component
     */
    Vue.component('wpEditor', {
      props: ['value', 'id', 'name'],
      template: '<textarea v-bind="$props"></textarea>',
      mounted() {

        wp.editor.remove(this.id);

        wp.editor.initialize(this.id, {
          tinymce: true,
        });

      },
      destroyed() {

        wp.editor.remove(this.id);

      },
    });

    /**
     * Romoves app on close
     */
    jQuery('body').on('wutb_unload', function() {

      const modal = jQuery('#WUB_window');

      const app = modal.find('ul[data-wu-app]');

      const app_name = 'wu_' + app.data('wu-app');

      delete (window[app_name]);

      delete (window[app_name + '_errors']);

    });

    $.each($('[data-wu-app]'), function(index, item) {

      /*
       * Prevent app creation when vue is not available.
       */
      if (typeof window.Vue === 'undefined') {

        return;

      } // end if;

      const app_id = $(item).data('wu-app');

      /*
       * Prevent re-mount of existent apps.
       */
      if (typeof window['wu_' + app_id] === 'object') {

        const exclusion_list = [
          'add_checkout_form_field',
        ];

        /*
         * Manually exclude apps for editing fields and steps ont he checkout.
         * This is a dirty fix, unfortunately.
         * @todo: refactor this.
         */
        if (! _.contains(exclusion_list, app_id)) {

          return;

        } // end if;

      } // end if;

      window['wu_' + app_id] = new Vue({
        name: typeof app_id === 'string' ? app_id : null,
        el: item,
        data() {

          return $(item).data('state');

        },
        computed: {
          shortcode() {

            if (typeof this.id === 'undefined' || typeof this.attributes === 'undefined') {

              return '';

            } // end if;

            const vm = this;

            return '[' + _.reduce({ id: this.id, ...this.attributes }, function(memo, value, key) {

              if (value === vm.defaults[key]) {

                return memo;

              } // end if;

              return memo + ' ' + key + '=\"' + value + '\"';

            }) + ']';

          },
        },
        mounted() {

          wu_on_load();

          const cb = $(item).data('on-load');

          if (typeof window[cb] === 'function') {

            window[cb]();

          } // end if;

        },
        updated() {

          if (! this._priorState) {

            this._priorState = this.$options.data();

          }

          const self = this;

          const changedProp = _.findKey(this._data, (val, key) => {

            return ! _.isEqual(val, self._priorState[key]);

          });

          this._priorState = { ...this._data };

          this.$nextTick(function() {

            jQuery('body').trigger('wu_' + app_id + '_changed', [changedProp]);

            window.wu_initialize_code_editors();

            window.wu_modal_refresh();

          });

        },
        methods: {
          send(scope, function_name, value) {

            if (scope === 'window') {

              return window[function_name](value);

            }

            return window[scope][function_name](value);

          },
          get_value(variable_name) {

            return window[variable_name];

          },
          set_value(key, value) {

            this[key] = value;

          },
          get_state_value(value, default_value) {

            return typeof this[value] === 'undefined' ? default_value : this[value];

          },
          duplicate_and_clean($event, target) {

            const $target = jQuery(target);

            const $clone = $target.clone();

            $clone
              .attr('id', $clone.attr('id') + '_copy')
              .find('input, textarea')
              .val('')
              .end()
              .insertAfter($target);

          },
          wu_format_money(value) {

            return wu_format_money(value);

          },
          require(data, value) {

            if (Object.prototype.toString.call(this[data]) === '[object Array]') {

              return this[data].indexOf(value) > -1;

            }

            if (Object.prototype.toString.call(value) === '[object Array]') {

              return value.indexOf(this[data]) > -1;

            }

            // eslint-disable-next-line eqeqeq
            return this[data] == value;

          },
          open($event) {

            $event.preventDefault();

            this.edit = true;

          },
        },
      });

    });

  });

}(jQuery));


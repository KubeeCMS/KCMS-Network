/* global Vue, _, wu_format_money, wu_on_load, wu_settings, wu_load_apps, wu_modal_refresh */
(function($, hooks) {

  window.wu_load_apps = function(callback) {

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
        directives: {
          init: {
            bind(el, binding, vnode) {

              vnode.context[binding.arg] = binding.value;

            },
          },
          initempty: {
            bind(el, binding, vnode) {

              if (vnode.context[binding.arg] === '') {

                vnode.context[binding.arg] = binding.value;

              }

            },
          },
        },
        data() {

          let prefix = wu_settings.currency_symbol;

          let suffix = '';

          if (wu_settings.currency_position === '%v%s') {

            prefix = '';

            suffix = wu_settings.currency_symbol;

          } else if (wu_settings.currency_position === '%s %v') {

            prefix = wu_settings.currency_symbol + ' ';

          } else if (wu_settings.currency_position === '%v %s') {

            prefix = '';

            suffix = ' ' + wu_settings.currency_symbol;

          } // end if;

          const settings = {
            money_settings: {
              prefix,
              suffix,
              decimal: wu_settings.decimal_separator,
              thousands: wu_settings.thousand_separator,
              precision: parseInt(wu_settings.precision, 10),
              masked: false,
            },
          };

          return Object.assign({}, $(item).data('state'), settings);

        },
        computed: {
          hooks: () => hooks,
          console: () => console,
          window: () => window,
          shortcode() {

            if (typeof this.id === 'undefined' || typeof this.attributes === 'undefined') {

              return '';

            } // end if;

            const vm = this;

            return '[' + _.reduce({ id: this.id, ...this.attributes }, function(memo, value, key) {

              if (value === vm.defaults[key]) {

                return memo;

              } // end if;

              if (value === false) {

                value = 'false';

              } else if (value === true) {

                value = ' true';

              } // end if;

              return memo + ' ' + key + '=\"' + (_.isString(value) ? value.trim() : value) + '\"';

            }) + ']';

          },
        },
        mounted() {

          wu_on_load();

          hooks.doAction('wu_' + app_id + '_mounted', this.$data);

          const cb = $(item).data('on-load');

          if (typeof window[cb] === 'function') {

            window[cb]();

          } // end if;

          if (typeof callback === 'function') {

            callback();

          } // end if;

          this.$nextTick(function() {

            window.wu_initialize_code_editors();

            window.wu_modal_refresh();

          });

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

            hooks.doAction('wu_' + app_id + '_changed', changedProp, self.$data);

            window.wu_initialize_code_editors();

            window.wu_modal_refresh();

          });

        },
        methods: {
          send(scope, function_name, value, cb) {

            if (scope === 'window') {

              return window[function_name](value, cb);

            }

            return window[scope][function_name](value, cb);

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

      window['wu_' + app_id].$watch('section', function(new_value) {

        try {

          const url = new URL(window.location.href);

          url.searchParams.set(app_id, new_value); // setting your param

          history.pushState({}, null, url);

        } catch (err) {

          // eslint-disable-next-line no-console
          console.warn('Browser does not support pushState.', err);

        } // end try;

      });

    });

  }; // end wu_load_apps;

  $(document).ready(function() {

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

        if (typeof wp.editor === 'undefined') {

          return;

        } // end if;

        const that = this;

        wp.editor.remove(this.id);

        wp.editor.initialize(this.id, {
          tinymce: {
            setup(editor) {

              editor.on('init', function() {

                wu_modal_refresh();

              });

              editor.on('keyup', () => {

                if (editor.isDirty()) {

                  that.$emit('input', editor.getContent());

                } // end if;

              });

            },
          },
        });

      },
      destroyed() {

        if (typeof wp.editor === 'undefined') {

          return;

        } // end if;

        wp.editor.remove(this.id);

      },
    });

    /**
     * Removes app on close
     */
    jQuery('body').on('wutb_unload', function() {

      const modal = jQuery('#WUB_window');

      const app = modal.find('ul[data-wu-app]');

      const app_name = 'wu_' + app.data('wu-app');

      delete (window[app_name]);

      delete (window[app_name + '_errors']);

    });

    jQuery('body').on('wubox:load', function() {

      wu_load_apps();

    });

    wu_load_apps();

  });

}(jQuery, wp.hooks));

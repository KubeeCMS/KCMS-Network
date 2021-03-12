/* global vuedraggable, Vue, wu_checkout_forms_editor_app, wu_checkout_form, _, ajaxurl, wu_initialize_tooltip */
(function($) {

  $(document).ready(function() {

    const draggable_table = {
      components: {
        vuedraggable,
      },
      template: '#wu-table',
      props: ['list', 'headers', 'step_name'],
      name: 'wu-draggable-table',
      data() {

        return {
          delete_field_id: '',
        };

      },
      methods: {
        remove_field(field) {

          wu_checkout_forms_editor_app.remove_field(this.step_name, field);

          this.delete_field_id = '';

        },
      },
    };

    wu_checkout_forms_editor_app = new Vue({
      el: '#wu-checkout-editor-app',
      name: 'CheckoutEditor',
      data() {

        return Object.assign({}, {
          dragging: false,
          search: '',
          delete_step_id: '',
          preview_error: false,
          preview: false,
          loading_preview: false,
          preview_content: '',
        }, wu_checkout_form);

      },
      components: {
        vuedraggable,
        'wu-draggable-table': draggable_table,
      },
      computed: {
        field_count() {

          return _.reduce(this.steps, function(memo, step) {

            return memo + step.fields.length;

          }, 0);

        },
      },
      mounted() {

        this.update_session();

      },
      methods: {
        get_preview(type = null) {

          if (type === null) {

            this.preview = ! this.preview;

          } // end if;

          if (this.preview) {

            this.loading_preview = true;

            const that = this;

            $.ajax({
              method: 'post',
              url: ajaxurl,
              data: {
                action: 'wu_generate_checkout_form_preview',
                settings: that.steps,
                type,
              },
              success(data) {

                that.preview_error = false;

                that.loading_preview = false;

                if (data.success) {

                  that.preview_content = data.data.content;

                } else {

                  that.preview_error = true;

                } // end if;

              },
            });

          } else {

            // eslint-disable-next-line no-console
            console.log('no preview');

          } // end if;

        },
        add_step(data) {

          const existing_step = this.find_step(data.id);

          if (typeof existing_step !== 'undefined') {

            const index = _.indexOf(this.steps, existing_step);

            data = Object.assign({}, existing_step, data);

            data.fields = existing_step.fields;

            Vue.set(this.steps, index, data);

          } else {

            this.steps.push(data);

          } // end if;

          this.update_session();

        },
        add_field(data) {

          const step = _.findWhere(this.steps, {
            id: data.step,
          });

          const existing_field = this.find_field(data.step, data.id);

          if (typeof existing_field !== 'undefined') {

            const index = _.indexOf(step.fields, existing_field);

            Vue.set(step.fields, index, data);

          } else {

            step.fields.push(data);

          } // end if;

          this.update_session();

        },
        find_step(step_name) {

          return _.findWhere(this.steps, {
            id: step_name,
          });

        },
        find_field(step_name, field_name) {

          const step = _.findWhere(this.steps, {
            id: step_name,
          });

          const field = _.findWhere(step.fields, {
            id: field_name,
          });

          return field;

        },
        remove_step(step_name) {

          this.steps = _.reject(this.steps, function(item) {

            return item.id === step_name;

          });

          this.delete_step_id = '';

        },
        remove_field(step_name, field_name) {

          const step = _.findWhere(this.steps, {
            id: step_name,
          });

          step.fields = _.reject(step.fields, function(item) {

            return item.id === field_name;

          });

        },
        update_session() {

          wu_initialize_tooltip();

          const that = this;

          $.ajax({
            method: 'post',
            url: ajaxurl,
            data: {
              action: 'wu_save_editor_session',
              settings: that.steps,
              form_id: that.form_id,
            },
            success() {},
          });

        },
      },
    });

  });

}(jQuery));

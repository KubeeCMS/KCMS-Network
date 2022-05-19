(function(blocks, element, components, wu_blocks, _) {

  const el = element.createElement,
    registerBlockType = blocks.registerBlockType,
    ServerSideRender = wp.serverSideRender,
    PanelBody = wp.components.PanelBody,
    TextControl = wp.components.TextControl,
    NumberControl = wp.components.__experimentalNumberControl,
    RangeControl = wp.components.RangeControl,
    ToggleControl = wp.components.ToggleControl,
    TextareaControl = wp.components.TextareaControl,
    Notice = wp.components.Notice,
    SelectControl = wp.components.SelectControl,
    InspectorControls = wp.blockEditor.InspectorControls;

  _.each(wu_blocks, function(block, index) {

    registerBlockType(block.id, {
      icon: wu_badge(),
      category: 'wp-ultimo',
      title: block.title,
      description: block.description,
      keywords: block.keywords,
      supports: {
        multiple: false,
        html: false,
      },
      edit(props) {

        return [
          el(ServerSideRender, {
            key: block.id + index,
            block: block.id,
            attributes: props.attributes,
          }),
          wu_fields_to_block_options(block.fields, props),
        ];

      },
      save() {

        return null;

      },
    });

  }); // end each;

  function wu_fields_to_block_options(fields, props) {

    const gt_panels = [];

    let gt_fields = [];

    let current_panel = null;

    let current_panel_slug = null;

    let new_panel = false;

    let first_panel = true;

    _.each(fields, function(field, field_slug) {

      if (field.type === 'group') {

        const sub_fields = field.fields;

        field = _.first(_.values(sub_fields));

        field.desc = '';

        field_slug = _.first(_.keys(sub_fields));

      } // end if;

      const component = wu_get_field_component(field.type, field);

      if (! _.isObject(field.required)) {

        field.required = {};

      } // end if;

      let should_display = true;

      _.each(field.required, function(value, key) {

        // eslint-disable-next-line eqeqeq
        should_display = props.attributes[key] == value;

      });

      if (should_display) {

        gt_fields.push(el(component, {
          key: field_slug,
          label: field.title,
          help: field.desc,
          value: props.attributes[field_slug],
          checked: props.attributes[field_slug],
          options: wu_format_options(field.options),
          min: field.min,
          max: field.max,
          onChange(value) {

            const obj = {};

            obj[field_slug] = value;

            props.setAttributes(obj);

          },
        }));

      } // end if;

      /*
       * Handle header types differently
       */
      if (field.type === 'header') {

        gt_fields.pop();

        if (current_panel !== null) {

          new_panel = true;

        } // end if;

        if (new_panel) {

          gt_panels.push(
            el(PanelBody, {
              key: current_panel_slug,
              title: current_panel.title,
              description: current_panel.desc,
              initialOpen: first_panel,
            }, gt_fields)
          );

          first_panel = false;

        } // end if;

        current_panel = field;

        current_panel_slug = field_slug;

        gt_fields = [];

      } // end if;

    });

    gt_panels.push(
      el(PanelBody, {
        key: current_panel_slug,
        title: current_panel.title,
        help: current_panel.desc,
        initialOpen: first_panel,
      }, gt_fields)
    );

    return el(InspectorControls, { key: 'wp-ultimo' }, gt_panels);

  } // end wu_fields_to_block_options;

  function wu_format_options(options) {

    const formatted_options = [];

    _.each(options, function(label, value) {

      formatted_options.push({
        label,
        value,
      });

    });

    return formatted_options;

  }

  function wu_get_field_component(field_type, field) {

    let component = TextControl;

    switch (field_type) {

    case 'toggle':

      component = ToggleControl;

      break;

    case 'number':

      if (field.max && field.min) {

        component = RangeControl;

      } else {

        component = NumberControl;

      } // end if;

      break;

    case 'textarea':

      component = TextareaControl;

      break;

    case 'select':

      component = SelectControl;

      break;

    case 'note':

      component = Notice;

      break;

    default:

      component = TextControl;

      break;

    } // end switch;

    return component;

  }

  /* eslint-disable */
  function wu_badge() {

    return el('svg', {
      width: '116px',
      height: '116px',
      viewBox: '0 0 116 116',
      version: '1.1',
      xmlnsXlink: 'http://www.w3.org/1999/xlink',
    }, el('g', {
      transform: 'translate(24.000000, 0.000000)',
      fill: '#000',
      stroke: 'none',
      strokeWidth: 1,
      fillRule: 'evenodd',
    }, el('g', {
      transform: 'translate(7.555556, 0.000000)',
    }, el('polygon', {
      points: '19.5185185 51.1370873 53.4139809 1.0658141e-14 30.1083134 54.9623572',
    }), el('polygon', {
      transform: 'translate(16.947731, 88.302800) scale(-1, -1) translate(-16.947731, -88.302800) ',
      points: '-1.55687808e-13 111.958709 33.8954624 60.8216216 10.5897949 115.783979',
    }), el('polygon', {
      points: '19.5185185 51.4162162 23.300226 60.62179 33.9358783 64.4738951 30.0960764 55.2115998',
    })), el('path', {
      d: 'M15.401 86.662C6.127 80.616 0 70.177 0 58.314c0-18.7 15.222-33.86 34-33.86 2.012 0 3.984.174 5.9.508l-5.802 9.002c-13.569.154-24.52 11.155-24.52 24.704 0 7.837 3.663 14.821 9.378 19.347l-3.555 8.647zm12.932 5.043l5.26-8.343c.263.008.528.012.793.012 13.701 0 24.808-11.061 24.808-24.706 0-8.207-4.018-15.48-10.203-19.973l3.58-8.748C61.861 35.99 68 46.438 68 58.314c0 18.7-15.222 33.859-34 33.859-1.93 0-3.824-.16-5.667-.468z',
    })));

  }
  /* eslint-enable */

}(
  window.wp.blocks,
  window.wp.element,
  window.wp.components,
  window.wu_blocks,
  window._
));

<?php
/**
 * Steps view.
 *
 * @since 2.0.0
 */
?>
<div id="wu-checkout-editor-app">

  <!-- Add new Step Section -->
  <div id="wp-ultimo-list-table-add-new-1" class="postbox wu-mb-0" v-cloak>

    <div class="wu-bg-white wu-px-4 wu-py-3 wu-flex wu-items-center">

      <div class="wu-w-1/2">

        <span class="wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">

          <?php printf(__('%1$s steps and %2$s fields', 'wp-ultimo'), '{{ steps.length }}', '{{ field_count }}'); ?>

        </span>

      </div>

      <div class="wu-w-1/2 wu-text-right">

        <ul class="wu-m-0 wu-overflow-hidden wu-flex wu-justify-end">

          <li class="wu-m-0 wu-ml-4">
            <a
              title="<?php _e('Preview', 'wp-ultimo'); ?>"
              href="#"
              type="button"
              class="wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800"
              @click.prevent="get_preview()"
            >
              <span class="dashicons-wu-eye wu-align-middle"></span>
              <span v-show="!preview"><?php _e('Preview', 'wp-ultimo'); ?></span>
              <span v-cloak v-show="preview"><?php _e('Editor', 'wp-ultimo'); ?></span>
            </a>
          </li>

          <li class="wu-m-0 wu-ml-4" v-show="!preview">
            <a
              title="<?php _e('Add new Checkout Step', 'wp-ultimo'); ?>"
              href="<?php echo wu_get_form_url('add_new_form_step', array(
                'checkout_form' => $checkout_form,
              )); ?>"
              type="button"
              class="wubox wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800"
            >
              <span class="dashicons-wu-circle-with-plus wu-align-middle"></span>
              <?php _e('Add new Checkout Step', 'wp-ultimo'); ?>
            </a>
          </li>

        </ul>

      </div>

    </div>

  </div>
  <!-- /Add new Step Section -->

  <!-- Editor -->
  <div 
    v-cloak 
    class="wu-px-4 wu-py-1 wu-bg-gray-200 wu-border wu-border-solid wu-border-gray-400 wu-border-t-0 wu-border-b-0"
    :class="dragging ? 'is-dragging' : ''"
  >

    <!-- Editor Proper -->
    <draggable
      :list="steps"
      :tag="'div'"
      group="step"
      handle=".hndle"
      ghost-class="wu-draggable-ghost"
      drag-class="wu-hide-inside"
      @start="dragging = true"
      @end="dragging = false"
      v-show="!preview"
    >

      <div 
        :id="'wp-ultimo-list-table-' + step.id" 
        class="postbox wu-my-4"
        v-cloak 
        v-for="(step, idx) in steps"
      >

        <div class="postbox-header">
          <h2 class="hndle ui-sortable-handle">
            <span class="wu-text-gray-700 ">
              <span class="wu-text-2xs wu-font-mono wu-uppercase wu-mr-4"><?php printf(__('Step %s', 'wp-ultimo'), '{{ idx + 1 }}'); ?></span> {{ step.name }}
            </span>
          </h2>
        </div>

        <div class="inside" style="margin-top: 0 !important;">

          <!-- Visibility -->
          <div v-if="step.logged && step.logged !== 'always'" class="wu-py-2 wu-px-4 wu--mx-3 wu-bg-blue-100 wu-text-blue-600 wu-border-solid wu-border-0 wu-border-b wu-border-gray-400">
              
              <span class="dashicons-wu-eye wu-mr-1 wu-align-middle"></span>
              
              <span v-if="step.logged == 'guests_only'">
                <?php _e('This step is only visible for <strong>guests</strong>', 'wp-ultimo'); ?>  
              </span>
              
              <span v-else>
                <?php _e('This step is only visible for <strong>logged-in users</strong>', 'wp-ultimo'); ?>
              </span>
              
          </div>
          <!-- Visibility - End -->
          
          <div class="wu-advanced-filters wu-widget-list-table wu--mx-3 wu--mb-3">

            <div id="wu-checkout_form_section_list_table" class="wu-list-table wu-mode-list">

              <wu-draggable-table
                :list="step.fields"
                :headers="headers"
                :step_name="step.id"
              ></wu-draggable-table>

            </div>

          </div>

          <div
            class="wu-bg-gray-100 wu-px-4 wu-py-3 wu--m-3 wu-mt-3 wu-border-t wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-400 wu-border-solid wu-text-right">

            <ul class="wu-m-0 wu-overflow-hidden md:wu-flex wu-w-full md:wu-w-auto wu-justify-end">

              <li class="wu-m-0 md:wu-ml-4 wu-text-center">

                <a
                  v-show="delete_step_id !== step.id"
                  v-on:click.prevent="delete_step_id = step.id"
                  title="<?php _e('Delete'); ?>"
                  href="#"
                  class="wu-text-red-500 wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-p-4 md:wu-p-0 wu-inline-block"
                >
                  <?php _e('Delete Step'); ?>
                </a>

                <a
                  v-show="delete_step_id === step.id"
                  v-on:click.prevent="remove_step(step.id)"
                  title="<?php _e('Delete'); ?>"
                  href="#"
                  class="wu-text-red-700 wu-uppercase wu-text-2xs wu-font-bold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-p-4 md:wu-p-0 wu-inline-block"
                >
                  <?php _e('Confirm?', 'wp-ultimo'); ?>
                </a>

              </li>

              <li class="wu-m-0 md:wu-ml-4 wu-text-center">

                <a title="<?php _e('Edit Section', 'wp-ultimo'); ?>"
                  :href="'<?php echo wu_get_form_url('add_new_form_step', array(
                    'checkout_form' => $checkout_form,
                    'step'          => '',
                  )); ?>=' + step.id"
                  type="button" 
                  class="wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800 wubox wu-p-4 md:wu-p-0 wu-inline-block"
                >
                    <?php _e('Edit Section', 'wp-ultimo'); ?>
                </a>

              </li>

              <li class="wu-m-0 md:wu-ml-4 wu-text-center">

                <a title="<?php _e('Add new Field', 'wp-ultimo'); ?>"
                  :href="'<?php echo wu_get_form_url('add_new_form_field', array(
                    'checkout_form' => $checkout_form,
                    'width'         => 600,
                    'step'          => '',
                  )); ?>=' + step.id"
                  type="button" class="wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800 wubox wu-p-4 md:wu-p-0 wu-inline-block">
                    <span class="dashicons-wu-circle-with-plus wu-align-text-bottom"></span>
                    <?php _e('Add new Field', 'wp-ultimo'); ?>
                </a>

              </li>

            </ul>

          </div>

        </div>

      </div>

    </draggable>
    <!-- /Editor Proper -->

    <!-- Preview Block -->
    <div v-show="preview">

      <div v-show="!loading_preview && !preview_error" class="wu-text-center wu-mt-3">

        <a @click.prevent="get_preview('user')" href="#" class="wu-m-2 wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800">
          <?php _e('See as existing user', 'wp-ultimo'); ?>
        </a>

        <a @click.prevent="get_preview('visitor')" href="#" class="wu-m-2 wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800">
          <?php _e('See as visitor', 'wp-ultimo'); ?>
        </a>

      </div>

      <!-- Preview Loading -->
      <div v-show="loading_preview" class="wu-block wu-p-4 wu-py-8 wu-bg-white wu-text-center wu-my-4 wu-border wu-border-solid wu-rounded wu-border-gray-400">

        <span class="wu-blinking-animation wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">
          <?php _e('Loading Preview...', 'wp-ultimo'); ?>
        </span>

      </div>
      <!-- /Preview Loading -->

      <!-- Error -->
      <div v-show="preview_error" class="wu-block wu-p-4 wu-py-8 wu-bg-white wu-text-center wu-my-4 wu-border wu-border-solid wu-rounded wu-border-gray-400">

        <span class="wu-text-red-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">
          <?php _e('Something wrong happened along the way =(', 'wp-ultimo'); ?>
        </span>

      </div>
      <!-- /Error -->

      <!-- Preview Proper -->
      <!-- <div v-show="!loading_preview && !preview_error" class="wu-block wu-p-8 wu-bg-white wu-my-4 wu-border wu-border-solid wu-rounded wu-border-gray-400" v-html="preview_content"></div> -->
      <div v-show="!loading_preview && !preview_error" id="wu-iframe-content" class="wu-w-full wu-relative">

        <iframe id="wp-ultimo-checkout-preview" v-bind:src="iframe_preview_url" class="wu-w-full wu-h-full wu-m-0 wu-mt-4 wu-mb-2 wu-p-0 wu-overflow-hidden wu-border-radius wu-border wu-border-solid wu-rounded wu-border-gray-400">
          <?php _e('Your browser doesn\'t support iframes', 'wp-ultimo'); ?>
        </iframe>

      </div>
      <!-- /Preview Proper -->

    </div>
    <!-- /Preview Block -->

  </div>
  <!-- /Editor -->

  <!-- Add new Step Section -->
  <div id="wp-ultimo-list-table-add-new-2" class="postbox" v-cloak>

    <div class="wu-bg-white wu-px-4 wu-py-3 wu-flex wu-items-center">

      <div class="wu-w-1/2">

        <span class="wu-text-gray-600 wu-my-1 wu-text-2xs wu-uppercase wu-font-semibold">

          <?php printf(__('%1$s steps and %2$s fields', 'wp-ultimo'), '{{ steps.length }}', '{{ field_count }}'); ?>

        </span>

      </div>

      <div class="wu-w-1/2 wu-text-right">

        <ul class="wu-m-0 wu-overflow-hidden wu-flex wu-justify-end">

          <li class="wu-m-0 wu-ml-4">
            <a
              title="<?php _e('Preview', 'wp-ultimo'); ?>"
              href="#"
              type="button"
              class="wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800"
              @click.prevent="get_preview('user')"
            >
              <span class="dashicons-wu-eye wu-align-middle"></span>
              <span v-show="!preview"><?php _e('Preview', 'wp-ultimo'); ?></span>
              <span v-cloak v-show="preview"><?php _e('Editor', 'wp-ultimo'); ?></span>
            </a>
          </li>

          <li class="wu-m-0 wu-ml-4" v-show="!preview">
            <a
              title="<?php _e('Add new Checkout Step', 'wp-ultimo'); ?>"
              href="<?php echo wu_get_form_url('add_new_form_step', array(
                'checkout_form' => $checkout_form,
              )); ?>"
              type="button"
              class="wubox wu-uppercase wu-text-2xs wu-font-semibold wu-no-underline wu-outline-none hover:wu-shadow-none focus:wu-shadow-none wu-text-gray-600 hover:wu-text-gray-800"
            >
              <span class="dashicons-wu-circle-with-plus wu-align-middle"></span>
              <?php _e('Add new Checkout Step', 'wp-ultimo'); ?>
            </a>
          </li>

        </ul>

      </div>

    </div>

  </div>
  <!-- /Add new Step Section -->

  <textarea class="wu-hidden" v-cloak name="_settings" v-html="JSON.stringify(steps)"></textarea>

</div>

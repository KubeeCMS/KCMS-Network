<?php
/**
 * Settings view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="wrap wu-wrap <?php echo esc_attr($classes); ?>">

  <h1 class="wp-heading-inline">

    <?php echo $page->get_title(); ?>

    <?php
    /**
     * You can filter the get_title_link using wu_page_list_get_title_link, see class-wu-page-list.php
     *
     * @since 1.8.2
     */
    foreach ($page->get_title_links() as $action_link) :

      $action_classes = isset($action_link['classes']) ? $action_link['classes'] : '';

    ?>

      <a title="<?php echo esc_attr($action_link['label']); ?>" href="<?php echo esc_url($action_link['url']); ?>" class="page-title-action <?php echo esc_attr($action_classes); ?>">

        <?php if ($action_link['icon']) : ?>

          <span class="dashicons dashicons-<?php echo esc_attr($action_link['icon']); ?> wu-text-sm wu-align-middle wu-h-4 wu-w-4">
            &nbsp;
          </span>

        <?php endif; ?>

        <?php echo $action_link['label']; ?>

      </a>

    <?php endforeach; ?>

    <?php
    /**
     * Allow plugin developers to add additional buttons to list pages
     *
     * @since 1.8.2
     * @param WU_Page WP Ultimo Page instance
     */
    do_action('wu_page_wizard_after_title', $page);
    ?>

  </h1>

  <?php if (wu_request('updated')) : ?>

    <div id="message" class="updated notice notice-success is-dismissible below-h2">
      <p><?php _e('Settings successfully saved.', 'wp-ultimo') ?></p>
    </div>

  <?php endif; ?>

  <hr class="wp-header-end">

  <form method="post">

    <div id="poststuff" class="sm:wu-flex">

      <div class="wu-w-full md:wu-w-4/12 lg:wu-w-2/12">

        <div class="wu-py-4 wu-relative">

          <input
            data-model='setting'
            data-value-field="setting_id"
            data-label-field="title"
            data-search-field="setting_id"
            data-max-items="1"
            selected type="text"
            placeholder="Search Setting"
            class="wu-w-full"
          >

        </div>

        <!-- Navigator -->
        <ul data-wu-app="settings_menu" data-state="{}">

          <li class="md:wu-hidden wu-p-4 wu-font-bold wu-uppercase wu-text-xs wu-text-gray-700">
            <?php _e('Menu', 'wp-ultimo'); ?>
          </li>

          <?php

          /**
           * We need to set a couple of flags in here to control clickable navigation elements.
           * This flag makes sure only steps the user already went through are clickable.
           */
          $is_pre_current_section = true;

          ?>

          <?php foreach ($sections as $section_name => $section) : ?>

            <?php

            /**
             * Updates the flag after the current section is looped.
             */
            if ($current_section === $section_name) {

              $is_pre_current_section = false;

            } // end if;

            ?>

            <!-- Menu Item -->
            <li class="wu-sticky">

              <!-- Menu Link -->
              <a href="<?php echo esc_url($page->get_section_link($section_name)); ?>" class="wu-block wu-py-2 wu-px-4 wu-no-underline wu-text-sm wu-rounded <?php echo !$clickable_navigation && !$is_pre_current_section ? 'wu-pointer-events-none' : ''; ?> <?php echo $current_section === $section_name ? 'wu-bg-gray-300 wu-text-gray-800' : 'wu-text-gray-600 hover:wu-text-gray-700'; ?>">

                <span class="<?php echo esc_attr($section['icon']); ?> wu-align-text-bottom wu-mr-1"></span>

                <?php echo $section['title']; ?>

              </a>
              <!-- End Menu Link -->

              <?php if (!empty($section['sub-sections'])) : ?>

                <!-- Sub-menu -->
                <ul class="classes" v-show="false" v-cloak>

                  <?php foreach ($section['sub-sections'] as $sub_section_name => $sub_section) : ?>

                    <li class="classes">
                      <a href="<?php echo esc_url($page->get_section_link($section_name)."#".$sub_section_name); ?>" class="wu-block wu-py-2 wu-px-4 wu-no-underline wu-text-gray-500 hover:wu-text-gray-600 wu-text-sm">
                        &rarr; <?php echo $sub_section['title']; ?>
                      </a>
                    </li>

                  <?php endforeach; ?>

                </ul>
                <!-- End Sub-menu -->

              <?php endif; ?>

            </li>
            <!-- End Menu Item -->

          <?php endforeach; ?>

        </ul>
        <!-- End Navigator -->

      </div>

      <div class="wu-w-full md:wu-w-8/12 lg:wu-w-6/12 md:wu-pl-4 lg:wu-px-4 metabox-holder">

        <div class="wu-relative">

          <?php
          /**
           * Print Side Metaboxes
           *
           * Allow plugin developers to add new metaboxes
           *
           * @since 1.8.2
           * @param object Object being edited right now
           */
          do_meta_boxes($screen->id, 'normal', false);
          ?>

        </div>

      </div>

      <div class="wu-hidden md:wu-block lg:wu-w-1/12">&nbsp;</div>

      <div class="wu-w-full wu-hidden lg:wu-block lg:wu-w-3/12 metabox-holder">

        <?php
        /**
         * Print Normal Metaboxes
         *
         * Allow plugin developers to add new metaboxes
         *
         * @since 1.8.2
         * @param object Object being edited right now
         */
        do_meta_boxes('wu_settings_admin_page', 'side', false);
        ?>

      </div>

    </div>

    <?php
    /**
     * Allow plugin developers to add scripts to the bottom of the page
     *
     * @since 1.8.2
     * @param WU_Page WP Ultimo Page instance
     */
    do_action('wu_page_wizard_footer', $page);
    ?>

    <?php wp_nonce_field(sprintf('saving_%s', $current_section), sprintf('saving_%s', $current_section), false); ?>

    <?php wp_nonce_field(sprintf('saving_%s', $current_section), '_wpultimo_nonce'); ?>

  </form>

</div>

<script type="text/javascript">

/** Not a huge fan of having this here, but it's better than having
a file for this alone. */
settings_loader = wu_block_ui('#wp-ultimo-wizard-body');

/**
 * Remove the block ui after the settings loaded.
 *
 * @since 2.0.0
 * @return void
 */
function remove_block_ui() {

  settings_loader.unblock();

} // end remove_block_ui;

</script>

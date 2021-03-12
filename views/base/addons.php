<?php
/**
 * Add-ons list page.
 *
 * @since 2.0.0
 */
?>

<style>
body .theme-browser .theme .theme-name {
  height: auto;
}
</style>

<div id="wp-ultimo-wrap" class="wrap wu-wrap <?php echo esc_attr($classes); ?>">

  <h1 class="wp-heading-inline">

    <?php echo $page->get_title(); ?> <span v-cloak v-if="count > 0" class="title-count theme-count" v-text="count"></span>

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
    do_action('wu_page_addon_after_title', $page);
    ?>

  </h1>

  <?php if (wu_request('updated')) : ?>

    <div id="message" class="updated notice notice-success is-dismissible below-h2">
      <p><?php _e('Settings successfully saved.', 'wp-ultimo') ?></p>
    </div>

  <?php endif; ?>

  <hr class="wp-header-end">

  <form method="post">

    <div id="poststuff" class="md:wu-flex">

      <div class="wu-w-full md:wu-w-4/12 lg:wu-w-2/12">

        <div class="wu-py-4 wu-relative" id="search-addons">

          <input
            type="text"
            placeholder="<?php esc_attr_e('Search Add-ons', 'wp-ultimo'); ?>"
            class="wu-w-full"
            v-model="search"
          >

        </div>

        <!-- Navigator -->
        <ul id="addons-menu">

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
              <a 
                href="<?php echo esc_url($page->get_section_link($section_name)); ?>" 
                class="wu-block wu-py-2 wu-px-4 wu-no-underline wu-text-sm wu-rounded wu-text-gray-600 hover:wu-text-gray-700"
                :class="category === '<?php echo esc_attr($section_name); ?>' ? 'wu-bg-gray-300 wu-text-gray-800' : 'wu-text-gray-600 hover:wu-text-gray-700'"
                @click.prevent="set_category('<?php echo esc_attr($section_name); ?>')"
              >

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

      <div class="wu-w-full md:wu-w-8/12 lg:wu-w-10/12 md:wu-pl-4 metabox-holder">

        <div id="wu-addon" class="wu-relative">
<!-- 
          <div class="wp-filter" v-cloak>

            <ul class="filter-links">

                <li :class="category == 'all' ? '' : 'selector-inactive'">
                    <a 
                      v-cloak 
                      href="#"
                      :class="category == 'all' ? 'current wu-font-medium' : ''" 
                      @click.prevent="category = 'all'"
                    >{{ i18n.all }}</a>
                </li>

                <li 
                  v-for="_category in categories"
                  :class="category == _category ? '' : 'selector-inactive'" 
                >
                    <a 
                      v-cloak 
                      href="#"
                      :class="category == _category.toLowerCase() ? 'current wu-font-medium' : ''" 
                      @click.prevent="category = _category.toLowerCase()"
                    >{{ _category }}</a>
                </li>

            </ul>

          </div> -->

          <div class="theme-browser rendered">

              <div class="themes wp-clearfix">

                  <div v-if="loading"
                    class="">
                    
                      <?php echo wu_render_empty_state(array(
                        'message'      => __("Loading...", 'wp-ultimo'),
                        'sub_message'  => __('We are fetch the list of WP Ultimo add-ons.', 'wp-ultimo'),
                        'link_url'     => false,
                      )); ?>

                  </div>

                  <div 
                    class="theme" 
                    tabindex="0"
                    v-cloak
                    v-for="addon in addons_list"
                    :data-slug="addon.slug"
                  >

                      <div class="theme-screenshot wu-bg-gray-100">

                          <img :src="addon.image_url" :alt="addon.name" />

                      </div>

                      <a 
                        class="more-details wubox wu-no-underline" 
                        :title="addon.name"
                        :href="'<?php echo $more_info_url; ?>'.replace('ADDON_SLUG', addon.slug)"
                      >

                        <?php _e('Add-on Details', 'wp-ultimo'); ?>

                      </a>

                      <div class="theme-author">

                          <?php _e('By WP Ultimo', 'wp-ultimo'); ?>

                      </div>

                      <h2 class="theme-name" :id="addon.slug">
                        {{ addon.name }}

                        <div class="wu-pt-1 wu-block">
                          <span 
                            v-cloak
                            class="wu-text-gray-600 wu-font-normal wu-text-xs"
                            v-if="addon.free"
                          >
                            <?php _e('Free Add-on', 'wp-ultimo'); ?>
                          </span>
                          <span 
                            v-cloak
                            class="wu-text-gray-600 wu-font-normal wu-text-xs"
                            v-else
                          >
                            <?php _e('Premium Add-on', 'wp-ultimo'); ?>
                          </span>

                          <span 
                            v-cloak
                            class="wu-ml-2 wu-text-green-600 wu-font-normal wu-text-xs"
                            v-if="addon.installed"
                          >
                            <span class="dashicons-wu-check"></span>
                            <?php _e('Installed', 'wp-ultimo'); ?>
                          </span>

                        </div>
                      </h2>

                  </div>

              </div>

          </div>

          <div class="theme-overlay"></div>

          <div 
            v-cloak
            v-if="! loading && addons_list.length == 0"
          >
            <?php echo wu_render_empty_state(array(
              'message'      => __("No add-ons found...", 'wp-ultimo'),
              'sub_message'  => __('Check the search terms or navigate between categories to see what add-ons we have available.', 'wp-ultimo'),
              'link_label'   => __('See all add-ons', 'wp-ultimo'),
              'link_url'     => remove_query_arg('tab'),
              'link_classes' => '',
              'link_icon'    => 'dashicons-wu-reply',
            )); ?>
          </div>

        </div>

      </div>

    </div>

    <?php
    /**
     * Allow plugin developers to add scripts to the bottom of the page
     *
     * @since 1.8.2
     * @param WU_Page WP Ultimo Page instance
     */
    do_action('wu_page_addon_footer', $page);
    ?>

  </form>

</div>

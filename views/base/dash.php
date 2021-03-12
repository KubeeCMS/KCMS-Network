<?php
/**
 * Dash view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="wrap wu-styling">

  <h1 class="wp-heading-inline">

    <?php echo $page_title; ?>

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
    do_action('wu_page_dash_after_title', $page);
    ?>

  </h1>

  <hr class="wp-header-end">

  <?php do_action('wu_dash_before_metaboxes', $page); ?>

  <?php if (apply_filters('wu_dashboard_display_widgets', true)) : ?>

    <div id="dashboard-widgets-wrap">

        <div id="dashboard-widgets" class="metabox-holder">

            <?php if ($has_full_position) : ?>

                <div id="postbox-container" class="postbox-container wu-w-full wu--mb-5" style="width: 100% !important;">
                    <?php
                    /**
                     * Print Advanced Metaboxes
                     *
                     * Allow plugin developers to add new metaboxes
                     *
                     * @since 1.8.2
                     * @param object Object being edited right now
                     */
                    do_meta_boxes($screen->id, 'full', null);
                    ?>
                </div>

                <div class="wu-mx-2">

                    <?php do_action('wu_dash_after_full_metaboxes', $page); ?>

                </div>

            <?php endif; ?>

            <div id="postbox-container-1" class="postbox-container">
                <?php
                /**
                 * Print Advanced Metaboxes
                 *
                 * Allow plugin developers to add new metaboxes
                 *
                 * @since 1.8.2
                 * @param object Object being edited right now
                 */
                do_meta_boxes($screen->id, 'normal', null);
                ?>
            </div>

            <div id="postbox-container-2" class="postbox-container">
                <?php
                /**
                 * Print Advanced Metaboxes
                 *
                 * Allow plugin developers to add new metaboxes
                 *
                 * @since 1.8.2
                 * @param object Object being edited right now
                 */
                do_meta_boxes($screen->id, 'side', null);
                ?>
            </div>

            <div id="postbox-container-3" class="postbox-container">
            </div>

            <div id="postbox-container-4" class="postbox-container">
            </div>

        </div>

    <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>

    <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>

    </div>

    <!-- dashboard-widgets-wrap -->
    
    <?php endif; ?>

</div>

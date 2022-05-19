<?php
/**
 * Domain mapping view.
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

  <div class="<?php echo wu_env_picker('', 'wu-widget-inset'); ?>">

    <!-- Title Element -->
    <div class="wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100'); ?>">

      <?php if ($title) : ?>

        <h3 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

          <?php echo $title; ?>

        </h3>

      <?php endif; ?>

      <div class="wu-ml-auto">

        <a title="<?php _e('Add Domain', 'wp-ultimo'); ?>" href="<?php echo $modal['url']; ?>" class="wu-text-sm wu-no-underline wubox button">

          <?php _e('Add Domain', 'wp-ultimo'); ?>

        </a>

      </div>

    </div>
    <!-- Title Element - End -->

    <div class="wu-border-t wu-border-solid wu-border-0 wu-border-gray-200">
    
      <table class="wu-m-0 wu-my-2 wu-p-0 wu-w-full">

        <tbody class="wu-align-baseline">

          <?php if ($domains) : ?>

              <?php foreach ($domains as $key => $domain) : $item = $domain['domain_object']; ?>

                  <tr>

                    <td class="wu-px-1">

                      <?php

                      $label = $item->get_stage_label();

                      if (!$item->is_active()) {

                        $label = sprintf('%s <small>(%s)</small>', $label, __('Inactive', 'wp-ultimo'));

                      } // end if;

                      $class = $item->get_stage_class();

                      $status = "<span class='wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-leading-none wu-font-mono $class'>{$label}</span>";

                      $second_row_actions = array();

                      if (!$item->is_primary_domain()) {

                        $second_row_actions['make_primary'] = array(
                          'wrapper_classes' => 'wubox',
                          'icon'            => 'dashicons-wu-edit1 wu-align-middle wu-mr-1',
                          'label'           => '',
                          'url'             => $domain['primary_link'],
                          'value'           => __('Make Primary', 'wp-ultimo'),
                        );

                      } // end if;

                      $second_row_actions['remove'] = array(
                        'wrapper_classes' => 'wu-text-red-500 wubox',
                        'icon'            => 'dashicons-wu-trash-2 wu-align-middle wu-mr-1',
                        'label'           => '',
                        'value'           => __('Delete', 'wp-ultimo'),
                        'url'             => $domain['delete_link'],
                      );

                      echo wu_responsive_table_row(array(
                        'id'     => false,
                        'title'  => strtolower($item->get_domain()),
                        'url'    => false,
                        'status' => $status,
                      ), array(
                        'primary' => array(
                          'wrapper_classes' => $item->is_primary_domain() ? 'wu-text-blue-600' : '',
                          'icon'  => $item->is_primary_domain() ? 'dashicons-wu-filter_1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-plus-square wu-align-text-bottom wu-mr-1',
                          'label' => '',
                          'value' => $item->is_primary_domain() ? __('Primary', 'wp-ultimo').wu_tooltip(__('All other mapped domains will redirect to the primary domain.', 'wp-ultimo'), 'dashicons-editor-help wu-align-middle wu-ml-1') : __('Alias', 'wp-ultimo'),
                        ),
                        'secure'  => array(
                          'wrapper_classes' => $item->is_secure() ? 'wu-text-green-500' : '',
                          'icon'            => $item->is_secure() ? 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1' : 'dashicons-wu-lock1 wu-align-text-bottom wu-mr-1',
                          'label'           => '',
                          'value'           => $item->is_secure() ? __('Secure (HTTPS)', 'wp-ultimo') : __('Not Secure (HTTP)', 'wp-ultimo'),
                        ),
                      ),
                      $second_row_actions);

                      ?>

                    </td>

                  </tr>

              <?php endforeach; ?>

          <?php else : ?>

            <div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4 wu-m-4 wu-mt-6">
              <span><?php echo __('No domains added.', 'wp-ultimo'); ?></span>
            </div>

          <?php endif; ?>

        </tbody>

    </table>
    
    </div>

  </div>

</div>

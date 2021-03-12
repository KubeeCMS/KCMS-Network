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
    
      <table class="">

      <?php if ($domains) : ?>

      <thead class="wu-uppercase">

        <tr>

          <th class="wu-text-left wu-px-4 wu-text-xs wu-font-semibold wu-text-gray-700" style="width: 30%;">

		        <?php echo __('Name', 'wp-ultimo'); ?>

          </th>

          <th class="wu-text-left wu-px-4 wu-text-xs wu-font-semibold wu-text-gray-700" style="width: 30%;">

		        <?php echo __('Status', 'wp-ultimo'); ?>

          </th>

          <th class="wu-text-left wu-px-4 wu-text-xs wu-font-semibold wu-text-gray-700" style="width: 20%;">

		        <?php echo __('Primary', 'wp-ultimo'); ?>

          </th>

          <th class="wu-text-xs wu-px-4 wu-font-semibold wu-text-gray-700 wu-text-center" style="width: 15%;">

		        <?php echo __('Secure', 'wp-ultimo'); ?>

          </th>

          <th class="wu-text-xs wu-px-4 wu-font-semibold wu-text-gray-700" style="width: 10%;">

            &nbsp;

          </th>

        </tr>

      </thead>

      <?php endif; ?>

      <tbody class="wu-align-baseline">

        <?php if ($domains) : ?>

            <?php foreach ($domains as $key => $domain) : ?>

                <tr>

                <td class="wu-align-middle wu-text-sm wu-px-4 wu-whitespace-no-wrap">

				<?php echo $domain['domain']; ?>

                </td>

                <td class="wu-align-middle wu-text-xs">

                    <span class="wu-py-1 wu-px-2 wu-rounded-sm wu-text-xs wu-font-mono <?php echo $domain['stage_class']; ?>">

				<?php echo $domain['stage']; ?>

                    </span>

                </td>

                <td class="wu-align-middle">

                    <span class="wu-toggle wu-inline-block" style="transform: scale(0.75)">
                    <input class="wu-tgl wu-tgl-ios wu-domain-primary" value="<?php echo $domain['id']; ?>" <?php checked($domain['primary'] == 1); ?>
                        id="wu-tg-primary-<?php echo $domain['domain']; ?>" type="checkbox" name="is_primary" />
                    <label for="wu-tg-primary-<?php echo $domain['domain']; ?>" class="wu-tgl-btn wp-ui-highlight"></label>
                    </span>

                </td>

                <td class="wu-align-middle wu-text-xs wu-whitespace-no-wrap wu-text-center">

                    <span <?php echo wu_tooltip_text($domain['secure_message']); ?> class="wu-text-base <?php echo $domain['secure_class']; ?>"></span>

                </td>

                <td class="wu-align-middle wu-text-xs wu-text-right">

                    <a title="<?php _e('Delete Domain', 'wp-ultimo'); ?>" <?php echo wu_tooltip_text(__('Delete domain', 'wp-ultimo')); ?> class="wubox wu-m-0 wu-p-0" href="<?php echo $domain['delete_link']; ?>">

                    <span
                        class="wu-bg-red-500 wu-text-white wu-rounded-full wu-text-sm dashicons-wu-cross wu-inline-block"></span>

                    </a>

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

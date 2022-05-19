<?php
/**
 * Login Form
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

  <?php if ($logged) : ?>

  <!-- Already Logged Block -->

    <div class="wu-p-4 wu-bg-yellow-200 wu-rounded <?php echo wu_env_picker('wu-mb-4', 'wu-mt-2 wu-shadow-sm'); ?>">

      <?php

        // translators: 1$s is the display name of the user currently logged in.
        printf(__('Not %1$s? <a href="%2$s" class="wu-no-underline">Log in</a> using your account.', 'wp-ultimo'), wp_get_current_user()->display_name, $login_url);

      ?>

    </div>

  <!-- Already Logged Block - End -->

  <?php else : ?>

    <!-- Title Element -->
    <div class="wu-pb-4 wu-flex wu-items-center">

      <?php if ($display_title) : ?>

        <h2 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

          <?php echo $title; ?>

        </h2>

      <?php endif; ?>

      <?php if (wu_get_setting('enable_registration', true)) : ?>

        <div class="wu-ml-auto">

          <a
            title="<?php esc_attr_e('Update Billing Address', 'wp-ultimo'); ?>"
            class="wu-text-sm wu-no-underline button"
            href="<?php echo wu_get_registration_url(); ?>"
          >

            <?php _e('Create an Account', 'wp-ultimo'); ?>

          </a>

        </div>

      <?php endif; ?>

    </div>
    <!-- Title Element - End -->

    <?php $form->render(); ?>

  <?php endif; ?>

</div>

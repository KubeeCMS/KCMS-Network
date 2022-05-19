<?php
/**
 * Rollback view.
 *
 * @since 2.0.0
 */
?>
<div id="wp-ultimo-wrap" class="<?php wu_wrap_use_container(); ?> wrap wu-wrap">

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
	     * Allow plugin developers to add
	     *
	     * @since 1.8.2
	     * @param WU_Page WP Ultimo Page instance
	     */
	    do_action('wu_page_rollback_after_title', $page);
	    ?>

  </h1>

  <form method="post" action="<?php echo network_admin_url('admin.php'); ?>">

    <table class="form-table">
      <tbody>

        <tr>
          <th scope="row"><?php _e('Rollback to', 'wp-ultimo'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php _e('Rollback to', 'wp-ultimo'); ?></span>
              </legend>
              <p>
                <label>
                  <input name="type" type="radio" value="latest-stable" v-model="type">
                  <?php printf(__('Last stable version before the currently installed - Currently Installed: <code> %s </code>', 'wp-ultimo'), WP_Ultimo()->version); ?>
                </label>
                <br>
                <label>
                  <input name="type" type="radio" value="select-version" v-model="type">
                  <?php _e('Select the version manually', 'wp-ultimo'); ?>
                </label>
              </p>
            </fieldset>
          </td>
        </tr>

        <tr v-cloak v-if="type== 'select-version'">
          <th scope="row"><?php _e('Available Versions', 'wp-ultimo'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text">
                <span><?php _e('Available Versions', 'wp-ultimo'); ?></span>
              </legend>
              <p>
                <?php if (is_array($versions)) : ?>
					<?php foreach ($versions as $version) : ?>
                    <label>
                      <input name="version" type="radio" value="<?php echo $version; ?>" v-model="version" <?php disabled($version == WP_Ultimo()->version); ?>>
                      <code><?php echo $version; ?></code>
						<?php echo $version == WP_Ultimo()->version ? __(' - Current Version', 'wp-ultimo') : ''; ?>
                    </label>
                    <br>
                  <?php endforeach; ?>
                <?php else : ?>
                  <label>
                    <?php _e('You need to activate your copy to have access to Rollbacks.', 'wp-ultimo'); ?>
                  </label>
                <?php endif; ?>
              </p>
            </fieldset>
          </td>
        </tr>

        <tr v-cloak v-if="type == 'latest-stable' || (type == 'select-version' && version)">
          <th scope="row"><?php _e('Confirm Rollback', 'wp-ultimo'); ?></th>
          <td>
            <fieldset>
              <legend class="screen-reader-text"><span><?php _e('Confirm Rollback', 'wp-ultimo'); ?></span></legend>
              <?php if (is_array($versions)) : ?>
                <label for="confirm">
                  <input v-model="confirm" name="confirm" type="checkbox" id="confirm" value="0">
                  <span v-if="version">
                    <?php printf(__('I understand the risks and I want to rollback to WP Ultimo version %s', 'wp-ultimo'), '<code>{{version}}</code>'); ?>
                  </span>
                  <span v-if="!version">
                    <?php printf(__('I understand the risks and I want to rollback to the last stable version before <code> %s </code>', 'wp-ultimo'), WP_Ultimo()->version); ?>
                  </span>
                </label>
              <?php else : ?>
                <label>
                  <?php _e('You need to activate your copy to have access to Rollbacks.', 'wp-ultimo'); ?>
                </label>
              <?php endif; ?>
            </fieldset>
          </td>
        </tr>

      </tbody>
    </table>

    <?php wp_nonce_field('wp-ultimo-rollback'); ?>
    
    <input type="hidden" name="action" value="rollback-wp-ultimo">
    
    <input type="hidden" name="n" value="<?php echo esc_attr(base64_encode($n)); ?>">

    <p class="submit">
      <input v-on:click="block" v-bind:disabled="!confirm" type="submit" name="submit" id="submit" class="button button-primary button-large wu-ml-auto"
        value="<?php _e('Rollback', 'wp-ultimo'); ?>">
    </p>

  </form>

</div>

<script>
(function($) {
  $(document).ready(function() {

    rollback = new Vue({
      el: '#wp-ultimo-wrap',
      data: {
        confirm: false,
        type: <?php echo json_encode(wu_request('type', false)); ?>,
        version: <?php echo json_encode(wu_request('version', wu_get_version())); ?>,
      },
      methods: {
        block: function() {
          wu_block_ui('#wpcontent');
        }
      }
    });

  });
})(jQuery);
</script>

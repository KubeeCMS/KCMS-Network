<?php
/**
 * Empty List Table View
 *
 * @since 2.0.0
 */
?>
<div 
  class="wu-flex wu-justify-center wu-items-center wu-text-center wu-bg-contain wu-bg-no-repeat wu--mb-12 wu-pb-12"
  style="background-image: url(<?php echo $display_background_image ? wu_get_asset('empty-state-bg.png', 'img') : ''; ?>); <?php echo $display_background_image ? "height: calc(100vh - 300px); background-position: center -30px;" : ''; ?>"
>

  <div class="wu-block wu-p-4 md:wu-pt-12 wu-self-center">

    <span class="wu-block wu-text-2xl wu-text-gray-600">

      <?php echo $message; ?>

    </span>

    <?php if (!empty($link_url)) : ?>

      <div class="wu-block wu-text-base wu-text-gray-500 wu-py-6">

        <?php echo $sub_message; ?>

      </div>

      <div>

        <a 
          href="<?php echo esc_attr($link_url); ?>" 
          title="<?php echo esc_attr($link_label); ?>"
          class="button button-primary button-hero <?php echo esc_attr($link_classes); ?>"
        >

          <?php if (!empty($link_icon)) : ?>
            
            <span class="<?php echo esc_attr($link_icon); ?> wu-align-middle"></span>

          <?php endif; ?>

          <?php echo $link_label; ?>

        </a>

      </div>

    <?php else : ?>

      <div class="wu-block wu-text-base wu-text-gray-500 wu-py-6">

        <?php echo $sub_message; ?>

      </div>

    <?php endif; ?>
  
  </div>  

</div>

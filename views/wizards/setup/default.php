<?php
/**
 * Default view.
 *
 * @since 2.0.0
 */
?>
<h1>
  <?php echo $title; ?>
</h1>

<?php if ($description) : ?>

  <p class="wu-text-lg wu-text-gray-600 wu-mt-4 wu-mb-0">
    <?php echo $description; ?>
  </p>

<?php endif; ?>

<div class="wu-bg-white wu-p-4 wu--mx-5">

  <?php echo $content; ?>

</div>

<!-- Submit Box -->
<div class="wu-flex wu-justify-between wu-bg-gray-100 wu--m-in wu-mt-4 wu-p-4 wu-overflow-hidden wu-border-t wu-border-solid wu-border-l-0 wu-border-r-0 wu-border-b-0 wu-border-gray-300">

  <?php if ($back) : ?>

    <a href="<?php echo esc_attr($page->get_prev_section_link()); ?>" class="wu-self-center button button-large wu-float-left">
      <?php echo $back_label; ?>
    </a>

  <?php endif; ?>

  <div class="wu-text-right wu-relative wu-w-full">

    <?php if ($skip) : ?>

      <a href="<?php echo esc_attr($page->get_next_section_link()); ?>" class="wu-skip-button button button-large">
        <?php echo $skip_label; ?>
      </a>

    <?php endif; ?>

    <?php if ($next) : ?>

      <button name="next" value="1" class="wu-next-button button button-primary button-large wu-ml-2">
        <?php echo $next_label; ?>
      </button>

    <?php endif; ?>

  </div>

</div>
<!-- End Submit Box -->


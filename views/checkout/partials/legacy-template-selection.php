<?php
/**
 * Template File: Basic Pricing Table.
 *
 * To see what methods are available on the product variable, @see inc/models/class-producs.php.
 *
 * This template can also be overridden using template overrides.
 * See more here: @todo add link to template overrides.
 *
 * @since 2.0.0
 * @param array $products List of product objects.
 * @param string $name ID of the field.
 * @param string $label The field label.
 */
?>

<?php if (empty($sites)) : ?>

<div 
  class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4"
>

	<?php _e('No Site Templates Found.', 'wp-ultimo'); ?>

</div>

<?php else : ?>

<div class="themes-php">

  <div class="wrap wu-template-selection">

    <?php

    /**
     * Allow developers to hide the title.
     */
    if (apply_filters('wu_step_template_display_header', true)) :

		?>

      <h1>

        <?php _e('Pick your Template', 'wp-ultimo'); ?>

        <span class="title-count theme-count">

		<?php echo count($sites); ?>

        </span>

      </h1>

    <?php endif; ?>

    <div class="wp-filter">

      <div class="wp-filter-responsive">

        <h4><?php _e('Template Categories', 'wp-ultimo'); ?></h4>

        <select class="">

          <option value="">
            
	<?php _e('All Templates', 'wp-ultimo'); ?>
          
          </option>

	<?php foreach ($categories as $category) : ?>

            <option value="<?php echo esc_attr($category); ?>">
            
		<?php echo $category; ?>
            
            </option>

          <?php endforeach; ?>

        </select>

      </div>

      <ul class="filter-links wp-filter-template">

        <li class="selector-inactive">

          <a
            href="#" 
            data-category=""
            :class="template_category === '' ? 'current' : ''" 
            v-on:click.prevent="template_category = ''"
          >
            
	<?php _e('All Templates', 'wp-ultimo'); ?>
          
          </a>

        </li>

	<?php foreach ($categories as $category) : ?>

          <li class="selector-inactive">

            <a 
              href="#" 
              data-category="<?php echo esc_attr($category); ?>"
              :class="template_category === '<?php echo esc_attr($category); ?>' ? 'current' : ''" 
              v-on:click.prevent="template_category = '<?php echo esc_attr($category); ?>'"
            >
          
		<?php echo $category; ?>
          
            </a>

          </li>

        <?php endforeach; ?>

      </ul>

    </div>

    <div class="theme-browser rendered">

      <div class="themes wp-clearfix">

	<?php $i = 0; foreach ($sites as $site) : ?>

          <div 
            class="theme" 
            tabindex="<?php echo $i; ?>" 
            aria-describedby="<?php echo $site->get_id(); ?>-action <?php echo $site->get_id(); ?>-name" 
            data-slug="<?php echo $site->get_id(); ?>"
            v-show="!template_category || <?php echo esc_attr(json_encode($site->get_categories())); ?>.join(',').indexOf(template_category) > -1"  
            v-cloak
          >

            <div class="theme-screenshot">

              <img 
                src="<?php echo $site->get_featured_image(); ?>" 
                alt="<?php echo $site->get_title(); ?>"
              >

            </div>

            <a 
		<?php echo $site->get_preview_url_attrs(); ?>
              class="more-details" 
              id="<?php echo $site->get_id(); ?>-action"
            >

		<?php _e('View Template', 'wp-ultimo'); ?>

            </a>

            <h2 class="theme-name" id="<?php echo $site->get_id(); ?>-name">

		<?php echo $site->get_title(); ?>
              
            </h2>

            <div class="theme-actions">

              <button 
                class="button button-primary" 
                type="submit" 
                name="template_id" 
                value="<?php echo $site->get_id(); ?>"
              >

		<?php _e('Select', 'wp-ultimo'); ?>

              </button>

            </div>

          </div>

        <?php
        $i++;
endforeach;
	?>

      </div>

    </div>

    <div class="theme-overlay"></div>

    <p class="no-themes">

	<?php _e('No Templates Found', 'wp-ultimo'); ?>
        
    </p>

  </div>

</div>

<?php endif; ?>

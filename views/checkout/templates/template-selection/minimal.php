<?php
/**
 * Displays the frequency selector for the pricing tables
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/pricing-table/frequency-selector.php.
 *
 * HOWEVER, on occasion WP Ultimo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NextPress
 * @package     WP_Ultimo/Views
 * @version     1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

if (!$should_display) {

  return;

} // end if;

$sites = array_map('wu_get_site', $sites);

$categories = isset($categories) ? $categories : array();

?>
<div id="wu-site-template-container">

  <ul id="wu-site-template-filter">
    <li class="wu-site-template-filter-all">
      <a @click.prevent="" href="#">
        <?php _e('All', 'wp-ultimo'); ?>
      </a>
    </li>

    <?php if (isset($categories) && $categories) : ?>

      <?php foreach ($categories as $category) : ?>

        <li class="wu-site-template-filter-<?php echo esc_attr($category) ;?>">
          <a @click.prevent="" href="#">
            <?php echo $category; ?>
          </a>
        </li>

      <?php endforeach; ?>

    <?php endif; ?>
  </ul>

  <div id="wu-site-template-container-grid">

    <?php foreach ($sites as $site_template) : ?>

      <?php if ($site_template->get_type() !== 'site_template') { continue; } ?>

      <div id="wu-site-template-<?php echo esc_attr($site_template->get_id()); ?>">

        <img class="wu-site-template-image" src="<?php echo esc_attr($site_template->get_featured_image()); ?>" alt="<?php echo $site_template->get_title(); ?>">

        <h3 class="wu-site-template-title">

          <?php echo $site_template->get_title(); ?>

        </h3>

        <p class="wu-site-template-description">

          <?php echo $site_template->get_description(); ?>

        </p>

        <div class="wu-site-template-preview-block">

          <a class="wu-site-template-selector" <?php echo $site_template->get_preview_url_attrs(); ?>>

            <?php _e('View Template Preview', 'wp-ultimo'); ?>

          </a>

        </div>

        <label for="wu-site-template-id-<?php echo esc_attr($site_template->get_id()); ?>">

          <input id="wu-site-template-id-<?php echo esc_attr($site_template->get_id()); ?>" type="radio" name="template_id" v-model="$parent.template_id" value="<?php echo esc_attr($site_template->get_id()); ?>" />

          <a class="wu-site-template-selector" @click.prevent="" href="#">

            <?php _e('Select this Template', 'wp-ultimo'); ?>

          </a>

        </label>

      </div>

    <?php endforeach; ?>

  </div>

</div>

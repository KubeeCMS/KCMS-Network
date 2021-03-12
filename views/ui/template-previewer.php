<?php
/**
 * This is the template used for the Template Previewer.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-template-previewer.php.
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

if (!defined('ABSPATH')) {

	exit; // Exit if accessed directly

} // end if;

?>
<!DOCTYPE html>
<html>
  <head>

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php wp_head(); ?>

  </head>

  <body>

    <div id="switcher">

      <div class="center">

          <div class="logo">

              <a title="<?php echo get_network_option(null, 'site_name'); ?>" href="<?php echo network_home_url(); ?>" target="_blank">

                  <?php if ($use_custom_logo && $custom_logo) : ?>

						        <?php echo wp_get_attachment_image($custom_logo, 'full'); ?>

                  <?php else : ?>

                    <img src="<?php echo $logo_url; ?>" alt="<?php echo get_network_option(null, 'site_name'); ?>">
                    
                  <?php endif; ?>

              </a>

          </div>

          <ul id="theme_list_selector">

              <li id="theme_list">

                  <?php if ($selected_template) : ?>

                      <a id="template_selector" href="#">

						              <?php echo $selected_template->get_title(); ?>

                          <span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>

                      </a>

                  <?php else : ?>

                      <a id="template_selector" href="#">

					                <?php _e('Select template...', 'wp-ultimo'); ?>

                          <span style="float: right; margin-top:  -3px" class="dashicons dashicons-arrow-down-alt2"></span>

                      </a>

                  <?php endif; ?>

                  <ul id="test1a">

                      <?php foreach ($templates as $template) : ?>

                          <li>

                              <a href="<?php echo $tp->get_preview_url($template->get_id()); ?>">

							                  <?php echo $template->get_title(); ?>

                              </a>

                              <img alt="" class="preview" src="<?php echo $template->get_featured_image(); ?>">

                          </li>

                      <?php endforeach; ?>

                  </ul>

              </li>

          </ul>

          <?php if ($display_responsive_controls) : ?>

              <div class="responsive">

                  <a href="#" class="desktop active dashicons-before dashicons-desktop" title="<?php esc_attr_e('View Desktop Version', 'wp-ultimo'); ?>"></a>

                  <a href="#" class="tabletlandscape dashicons-before dashicons-tablet" title="<?php esc_attr_e('View Tablet Landscape (1024x768)', 'wp-ultimo'); ?>"></a>

                  <a href="#" class="tabletportrait dashicons-before dashicons-tablet" title="<?php esc_attr_e('View Tablet Portrait (768x1024)', 'wp-ultimo'); ?>"></a>

                  <a href="#" class="mobilelandscape dashicons-before dashicons-smartphone" title="<?php esc_attr_e('View Mobile Landscape (480x320)', 'wp-ultimo'); ?>"></a>

                  <a href="#" class="mobileportrait dashicons-before dashicons-smartphone" title="<?php esc_attr_e('View Mobile Portrait (320x480)', 'wp-ultimo'); ?>"></a>

              </div>

          <?php endif; ?>

      </div>

      <?php if (!isset($_GET['switching'])) : ?>
          
        <ul class="links">

            <?php if (isset($_GET['cs'])) : ?>

                <li class="select-template">

                    <a id="action-select" href="#"><?php echo $button_text; ?> &rarr;</a>

                </li>

            <?php else : ?>

                <li class="select-template">

                    <a id="action-select-link" href="<?php echo wu_get_registration_url('?template_id='.$selected_template->get_id()); ?>"><?php echo $button_text; ?> &rarr;</a>

                </li>

            <?php endif; ?>

        </ul>

      <?php endif; ?>

      <input type="hidden" id="template-selector" value="<?php echo esc_attr($_GET['template-preview']); ?>" />

    </div>

    <?php if (!isset($_GET['switching'])) : ?>

      <div class="mobile-selector">
        
		<?php if (isset($_GET['cs'])) : ?>
            
              <a id="action-select2" href="#"><?php echo $button_text; ?> &rarr;</a>

          <?php else : ?>

              <a id="action-select-link" href="<?php echo wu_get_registration_url('?template_id='.$selected_template->get_id()); ?>"><?php echo $button_text; ?> &rarr;</a>

          <?php endif; ?>

      </div>

    <?php endif; ?>

    <?php if (!wu_request('customizer')) : ?>

      <iframe id="iframe" src="<?php echo add_query_arg('preview', '1', get_home_url($selected_template->get_id())); ?>" width="100%" height="100%">

    <?php else : ?>

      <div class="wu-styling">

        <div class="wu-w-full wu-text-center wu-relative">

          <div class="wu-text-xl wu-rounded wu-font-bold wu-uppercase wu-inline-block wu-p-8 wu-opacity-50" style="margin-top: 30%; background-color: #000; color: #666;">

		        <?php _e('Site Template Preview will go here!', 'wp-ultimo'); ?>

          </div>

        </div>

      </div>

    <?php endif; ?>

  </body>

</html>

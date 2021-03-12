<?php
/**
 * This is the template used to display the URL preview field on the domain step
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-domain-url-preview.php.
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
}

?>

<p id="wu-your-site-block wu-mt-0">

  <small><?php _e('Your URL will be', 'wp-ultimo'); ?></small><br>

  <?php
  /**
   * Change the base, if sub-domain or subdirectory
   */
  $dynamic_part  = '<strong id="wu-your-site">';
  // This is used on the yoursite.network.com during sign-up
  $dynamic_part .= isset($signup->results['blogname']) ? $signup->results['blogname'] : __('yoursite', 'wp-ultimo');
  $dynamic_part .= '</strong>';
  
  $site_url      = preg_replace('#^https?://#', '', WU_Signup()->get_site_url_for_previewer());
  $site_url      = str_replace('www.', '', $site_url);

  // $template      = is_subdomain_install() ? $dynamic_part . '.' . $site_url : $site_url . "/" . $dynamic_part;
  $template      = is_subdomain_install() ? sprintf('%s.<span id="wu-site-domain">%s</span>', $dynamic_part, $site_url) : sprintf('<span id="wu-site-domain">%s</span>/%s', $site_url, $dynamic_part);

  echo $template;

  ?>

</p>

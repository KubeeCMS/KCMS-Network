<?php
/**
 * The Template for displaying the signup flow for the end user
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/signup-header.php.
 *
 * HOWEVER, on occasion WP Ultimo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      NextPress
 * @package     WP_Ultimo/Views
 * @version     1.4.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

do_action('wu_checkout_scripts');

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

  <head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>
      <?php echo apply_filters('wu_signup_page_title', sprintf(__('%s - Signup', 'wp-ultimo'), get_bloginfo('Name'), get_bloginfo('Name'))); ?>
    </title>

    <?php // Signup do action, like the default ?>
    <?php do_action('signup_header'); ?>
    <?php do_action('login_enqueue_scripts'); ?>
    <?php do_action('wu_signup_enqueue_scripts'); ?>
    <?php do_action('admin_print_scripts'); ?>
    <?php do_action('admin_print_styles'); ?>

    <?php //do_action('admin_head'); ?>

    <?php
    /**
		 * We also need to print the footer admin scripts, to make sure we are enqueing some of the scripts dependencies
		 * our scripts need in order to function properly
		 */
    do_action('admin_print_footer_scripts');
    ?>

    <?php while (have_posts()) : the_post(); 
    
      $content = do_shortcode(get_the_content());
    
    ?>

  </head>

  <body class="login wp-core-ui wu-legacy-signup-body">

    <div class="wu-setup">

    <?php

    /**
     * Fires right after the start body tag is printed
     *
     * @since 1.6.2
     */
    do_action('wu_signup_header');
    ?>

      <div id="login">

        <h1 id="wu-setup-logo">
          <a href="<?php echo get_site_url(get_current_site()->blog_id); ?>">
            <?php printf(__('%s - Signup', 'wp-ultimo'), get_bloginfo('Name')); ?>
          </a>
        </h1>

        <?php

        /**
         * Fires before the site sign-up form.
         */
        do_action('wu_before_signup_form');

        ?>

        <div class="wu-setup-content wu-content-<?php echo wu_request('step', isset($signup->step) ? $signup->step : 'default'); ?>">

          <div name="loginform" id="loginform">

            <?php echo $content; ?>

          </div>

        </div>

        <?php

        /**
         * Fires after the sign-up forms, before signup-footer
         */
        do_action('wu_after_signup_form');

        ?>

        <?php
        /**
         * Nav Links
         */
        wu_get_template('legacy/signup/signup-nav-links', array('signup' => $signup));
        ?>

      </div> <!-- /login -->

      <?php
		/**
		 * Navigation Steps
		 */
		wu_get_template('legacy/signup/signup-steps-navigation', array('signup' => $signup));
		?>

      <?php
		/**
		 * Fires right after the start body tag is printed
         *
		 * @since 1.6.2
		 */
		do_action('wu_signup_footer');
    
		?>

    <?php endwhile; ?>

    <?php 

    global $wp_scripts; 

    $wp_scripts->print_inline_script('wu-checkout', 'after', true);
    
    ?>

    </div>

  </body>

</html>

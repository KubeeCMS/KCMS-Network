<?php
/**
 * This is the template used for the Template Step.
 *
 * This template can be overridden by copying it to yourtheme/wp-ultimo/signup/steps/step-template.php.
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

/** Checks if we are in a shortcode environment */
$is_shortcode = isset($is_shortcode) ? $is_shortcode : false;

/** Sets default number of cols */
$cols = isset($cols) ? $cols : 3;

/** Do we want to display the filter bar? **/
$have_filter = wu_get_setting('allow_template_filter', true);

/** Gets the categories and Template objects */
list($categories, $templates) = WU_Site_Templates::prepare_site_templates();

$suffix = WP_Ultimo()->min;

/** Gets the necessary scripts **/
wp_register_script('wu-template-selector', WP_Ultimo()->get_asset("wu-template-selector$suffix.js", 'js'), array('wp-backbone', 'wp-a11y', 'jquery-blockui'), WP_Ultimo()->version);

/** ...and localize them **/
wp_localize_script( 'wu-template-selector', '_wuTemplateSettings', array(
  'themes'   => $templates,
  'is_shortcode' => $is_shortcode,
  'register_url' => WU_Signup()->get_register_url( network_site_url('wp-signup.php') ),
  'selected_category' => isset($_GET['template-category']) ? $_GET['template-category'] : false,
  'settings' => array(
      'canInstall'    => (! is_multisite() && current_user_can('install_themes')),
      'installURI'    => (! is_multisite() && current_user_can('install_themes')) ? admin_url('theme-install.php') : null,
      'confirmDelete' => __("Are you sure you want to delete this theme?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete."),
      'adminUrl'      => parse_url(admin_url(), PHP_URL_PATH),
      'cols'          => $cols ?: 3,
  ),
  'l10n' => array(
      'addNew'            => __('Add New Template', 'wp-ultimo'),
      'search'            => __('Search available templates', 'wp-ultimo'),
      'searchPlaceholder' => __('Search available templates...', 'wp-ultimo'), // placeholder (no ellipsis)
      'themesFound'       => __('Number of Templates found: %d', 'wp-ultimo'),
      'noThemesFound'     => __('No Templates Found. Try a different search.', 'wp-ultimo'),
  ),
));

wp_enqueue_script('wu-template-selector');

$current_theme_actions = array();

/**
 * Template Selection Bar
 * @since 1.6.0
 */

 $suffix = WP_Ultimo()->min;

if (!isset($_GET['elementor-preview']) && !$is_shortcode) {

  // Enqueue Scripts
  wp_enqueue_script('wu-template-preview', WP_Ultimo()->get_asset("wu-template-preview$suffix.js", 'js'), array('jquery'), WP_Ultimo()->version);

} // end if;

?>

<script type="text/javascript">

function wu_ready(fn) {
  if (document.attachEvent ? document.readyState === "complete" : document.readyState !== "loading"){
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn);
  }
} // end ready;

  wu_ready(function() {

    if (typeof wuCreateCookie !== 'function') {

      return;

    } // end if;

    // create the cookie
    wuCreateCookie('wuTemplate', false);

    // bind the listener
    wuListenCookieChange('wuTemplate', function() {

      var wu_parent = document.getElementById('signupform');

      var wu_template_input = document.createElement("input");

      wu_template_input.type  = 'submit';
      wu_template_input.name  = 'template';
      wu_template_input.value = wuReadCookie('wuTemplate');

      wu_template_input.style.display = 'none';

      wu_parent.appendChild(wu_template_input).click();

    });

  });

</script>

<div class="wu-setup-content wu-content-<?php echo isset($is_shortcode) && $is_shortcode ? 'shortcode-template' : 'template'; ?>">

  <form id="signupform" method="post">

  <div class="themes-php">
    <div class="wrap">

      <?php if (apply_filters('wu_step_template_display_header', true)) : ?>

        <?php echo ($is_shortcode) ? "<h3>" : "<h1>"; ?>

        <?php echo ($is_shortcode) ? esc_html__('Available Templates', 'wp-ultimo') : esc_html__('Pick your Template', 'wp-ultimo'); ?>
            <span class="title-count theme-count"><?php echo count($templates); ?></span>

        <?php echo ($is_shortcode) ? "</h3>" : "</h1>"; ?>

      <?php endif; ?>

      <?php if ((!$is_shortcode && $have_filter) || ($is_shortcode && $show_filters)) : ?>

      <div class="wp-filter">

        <div class="wp-filter-responsive">

          <h4><?php _e('Template Categories', 'wp-ultimo'); ?></h4>

          <select class="">
            <option value=""><?php _e('All Templates', 'wp-ultimo'); ?></option>
            <?php foreach ($categories as $cat) { ?>

                <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>

            <?php } ?>
          </select>
        </div>

        <ul class="filter-links wp-filter-template">

          <li class="selector-inactive">
              <a href="#" class="current" data-category=""><?php _e('All Templates', 'wp-ultimo'); ?></a>
          </li>

          <?php

          foreach ($categories as $cat) { ?>

              <li>
                  <a href="?s=<?php echo $cat; ?>" class="" data-category="<?php echo $cat; ?>"><?php echo $cat; ?></a>
              </li>

          <?php } ?>

        </ul>
      </div>

      <?php endif; ?>

      <div class="theme-browser">
        <div class="themes wp-clearfix">
          <?php
            /*
            * This PHP is synchronized with the tmpl-theme template below!
            */

            foreach ($templates as $theme) :
            $aria_action = esc_attr($theme['id'].'-action');
            $aria_name   = esc_attr($theme['id'].'-name');
            ?>

            <div class="theme col-<?php echo $cols; ?>" tabindex="0" aria-describedby="<?php echo $aria_action.' '.$aria_name; ?>">

              <?php if (! empty( $theme['screenshot'][0])) { ?>
                <div class="theme-screenshot">
                  <img src="<?php echo $theme['screenshot'][0]; ?>" alt="" />
                </div>
              <?php } else { ?>
                <div class="theme-screenshot blank"></div>
              <?php } ?>

              <?php
              /**
               * Decides if we are going to display the URL for the preview with the top-bar
               * or simply redirect the user to the site template
               * @since 1.6.0
               */
              $preview_href = wu_get_setting('allow_template_top_bar') ? sprintf('onclick="window.open(\'%s\')"', WU_Site_Hooks::get_template_preview_url($theme['id']).($is_shortcode ? '&shortcode' : '')) : sprintf('href="%s" target="_blank"', $theme['actions']['visit']);
              ?>

              <!-- href="<?php echo $theme['actions']['visit']; ?>" target="_blank"  -->
              <a <?php echo $preview_href; ?> class="more-details" id="<?php echo $aria_action; ?>"><?php _e( 'View Template', 'wp-ultimo' ); ?></a>

              <h2 class="theme-name" id="<?php echo $aria_name; ?>">

                <?php echo $theme['name']; ?>

                <?php if (isset($theme['user_site']) && $theme['user_site']) : ?>
                  <span class="template-user-site"><?php _e('Your Site', 'wp-ultimo'); ?></span>
                <?php endif; ?>

              </h2>

              <div class="theme-actions">

                <button class="button button-primary" type="submit" name="template" value="<?php echo $theme['id']; ?>"><?php _e('Select', 'wp-ultimo'); ?></button>

              </div>

            </div>

            <?php endforeach; ?>

        </div>
      </div>

      <div class="theme-overlay"></div>

      <p class="no-themes">
        <?php _e( 'No Templates Found' ); ?>
      </p>

  </div>
  <!-- .wrap -->

  </div>

  <?php
  /**
   * Print required fields
   */
  WU_Signup()->form_fields(false, 'template'); ?>

  </form>

</div>

<?php
/*
 * The tmpl-theme template is synchronized with PHP above!
 */
?>
<script id="tmpl-theme" type="text/template">

  <# if ( data.screenshot[0] ) { #>

    <div class="theme-screenshot">
      <img src="{{ data.screenshot[0] }}" alt="" />
    </div>

  <# } else { #>
    <div class="theme-screenshot blank"></div>
  <# } #>

  <?php
  /**
    * Decides if we are going to display the URL for the preview with the top-bar
    * or simply redirect the user to the site template
    * @since 1.6.0
    */
  $preview_href = wu_get_setting('allow_template_top_bar') ? sprintf('onclick="window.open(\'%s\')"', WU_Site_Hooks::get_template_preview_url('{{ data.id }}').($is_shortcode ? '&shortcode' : '')) : sprintf('href="%s" target="_blank"', "{{ data.actions.visit }}");
  ?>
  <!-- href="{{{ data.actions.visit }}}" target="_blank" -->
  <a <?php echo $preview_href; ?> class="more-details" id="{{ data.id }}-action"><?php _e( 'View Template', 'wp-ultimo' ); ?></a>

  <h2 class="theme-name" id="{{ data.id }}-name">

    {{{ data.name }}}

    <# if ( data.user_site ) { #>
        <span class="template-user-site"><?php _e('Your Site', 'wp-ultimo'); ?></span>
    <# } #>

  </h2>

  <div class="theme-actions">

    <button class="button button-primary" type="submit" name="template" value="{{ data.id }}"><?php _e('Select', 'wp-ultimo'); ?></button>

  </div>

</script>

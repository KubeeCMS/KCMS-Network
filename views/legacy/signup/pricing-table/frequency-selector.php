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

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

?>

<?php if (wu_get_setting('enable_price_3', true) || wu_get_setting('enable_price_12', true)) : ?>

<ul class="wu-plans-frequency-selector">

  <?php

  $prices = array(
    1  => __('Monthly', 'wp-ultimo'), 
    3  => __('Quarterly', 'wp-ultimo'), 
    12 => __('Yearly', 'wp-ultimo'), 
  );

  $first = true;

  foreach ($prices as $type => $name) : 

    if (!wu_get_setting('enable_price_'.$type, true)) continue; 

  ?>

  <li>
    <a class="<?php echo $first ? 'active first' : ''; ?>" data-frequency-selector="<?php echo $type; ?>" href="#">
      <?php echo $name; ?>
    </a>
  </li>

  <?php $first = false; endforeach; ?>

</ul>

<?php endif; ?>

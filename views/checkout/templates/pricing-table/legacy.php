<?php
/**
 * Template File: Basic Pricing Table.
 *
 * To see what methods are available on the product variable, @see inc/models/class-products.php.
 *
 * This template can also be override using template overrides.
 * See more here: @todo add link to template overrides.
 *
 * @since 2.0.0
 * @param array $products List of product objects.
 * @param string $name ID of the field.
 * @param string $label The field label.
 */

$products_to_reduce = array_merge(array(false), $products);

$first_recurring_product = array_reduce($products_to_reduce, function($chosen_product, $product) {

	if ($product && $product->is_recurring() && $chosen_product == false) {

		$chosen_product = $product;

	} // end if;

	return $chosen_product;

});

$legacy_mode = array_reduce($products_to_reduce, function($all_have_same_duration, $product) use ($first_recurring_product) {

	if ($product && $product->is_recurring()) {

		$all_have_same_duration = $first_recurring_product->get_recurring_description() == $product->get_recurring_description();

	} // end if;

	return $all_have_same_duration;

});

wp_add_inline_script('wu-checkout', sprintf('

  /**
   * Force different durations.
   */
  window.wu_force_different_durations = %s;

  window.wu_legacy_mode = %s;

', json_encode($force_different_durations), json_encode($legacy_mode)), 'after');

wp_add_inline_script('wu-checkout', sprintf("

  /**
   * Add durations if necessary.
   */
  wp.hooks.addFilter('wu_before_form_init', 'next-press/wp-ultimo', function(data) {

    data.wu_force_different_durations = wu_force_different_durations;
    data.wu_legacy_mode               = wu_legacy_mode;

    if (!data.duration && !wu_force_different_durations) {

      data.duration = %s;

    } // end if;

    if (!data.duration_unit && !wu_force_different_durations) {

      data.duration_unit = %s;

    } // end if;

    return data;

  });

", json_encode($first_recurring_product->get_duration()), json_encode($first_recurring_product->get_duration_unit())), 'after');

?>

<?php if (empty($products)) : ?>

<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4">

	<?php _e('No Products Found.', 'wp-ultimo'); ?>

</div>

<?php else : ?>

  <div class="wu-content-plan">

    <div class="layer plans wu-overflow-hidden wu-flex">

	<?php foreach ($products as $product) : ?>

        <div 
          id="plan-<?php echo esc_attr($product->get_id()); ?>"
          class="lift wu-plan plan-tier wu-flex-1 <?php echo esc_attr($product->top_deal ? 'callout' : ''); ?> wu-flex wu-flex-col wu-justify-between"
          v-show="wu_force_different_durations || (duration && wu_legacy_mode) || (( (!duration) || duration == <?php echo $product->get_duration(); ?> && duration_unit == '<?php echo $product->get_duration_unit(); ?>' ) || <?php echo json_encode($product->get_pricing_type() !== 'paid'); ?>)"
        >

        <div class="wu-relative">

		<?php if ($product->is_featured_plan()) : ?>

            <h6>
            
			<?php

			/**
			 * Featured tag.
			 */
			echo apply_filters('wu_featured_plan_label', __('Featured Plan', 'wp-ultimo'), $product);

			?>
            
            </h6>

          <?php endif; ?>

          <h4 class="wp-ui-primary">

		<?php echo $product->get_name(); ?>

          </h4>

		<?php

		/**
		 * Case Free
		 */
		if ($product->get_pricing_type() === 'free') :

			?>

            <!-- Price -->
            <h5>

              <span class="plan-price">

			<?php _e('Free!', 'wp-ultimo'); ?>

              </span>

            </h5>

			<?php

			/**
			 * Case Free
			 */
          elseif ($product->get_pricing_type() === 'contact_us') :

				?>

            <!-- Price -->
            <h5>

              <span class="plan-price">
              
			  <?php echo apply_filters('wu_plan_contact_us_price_line', __('--', 'wp-ultimo')); ?>

              </span>

            </h5>

          <?php else : ?>

            <!-- Price -->
            <h5>

              <?php

				/**
				 * Price display.
				 */

				$symbol_left = in_array(wu_get_setting('currency_position', '%s%v'), array('%s%v', '%s %v'));

				?>

              <?php if ($symbol_left) : ?>
              
                <sup class="superscript">
                
					<?php echo wu_get_currency_symbol($product->get_currency()); ?>
                  
                </sup>
                
              <?php endif; ?>

              <span class="plan-price" v-if="wu_force_different_durations || (duration == <?php echo $product->get_duration(); ?> && duration_unit == '<?php echo $product->get_duration_unit(); ?>')">
              
			  <?php

                $n = $product->get_amount();

                echo str_replace(wu_get_currency_symbol(), '', wu_format_currency($n));

                ?>
              
              </span>

              <?php

				/**
				 *
				 * Display quarterly and Annually plans, to be hidden.
				 */
				$periods = array(3, 12);

				foreach ($periods as $freq) :

					$price_variation = $product->get_price_variation($freq, 'month');

					if (!$price_variation) {

						continue;

					} // end if;

					?>

                <span class="plan-price" v-cloak v-if="duration == <?php echo $price_variation['duration']; ?> && duration_unit == '<?php echo $price_variation['duration_unit']; ?>'">

					<?php

					$n = $price_variation ? $price_variation['monthly_amount'] : false;

					if ($n) {

						echo str_replace(wu_get_currency_symbol(), '', wu_format_currency($n));

					} else {

						echo '--';

					} // end if;

					?>

                </span>

              <?php endforeach; ?>

              <sub v-if="1 == <?php echo $product->get_duration(); ?> && 'month' == '<?php echo $product->get_duration_unit(); ?>'">
              
              <?php

				/**
				 * Period Unit.
				 */
				$symbol = $product->is_recurring() ? __('/mo', 'wp-ultimo') : '';

				echo (!$symbol_left ? wu_get_currency_symbol() : '').' '.$symbol;

				?>
                
              </sub>

              <sub v-else>

              <?php

              /**
               * Period Unit.
               */
              $symbol = $product->is_recurring() ? $product->get_recurring_description() : '';

              echo (!$symbol_left ? wu_get_currency_symbol() : '').' '.$symbol;

              ?>

              </sub>

            </h5>
            <!-- end Price -->

          <?php endif; ?>

          <p class="early-adopter-price">
            
		<?php echo $product->get_description(); ?>

          </p>

        </div>

          <br>

          <!-- Feature List Begins -->
          <ul>

		<?php

		/**
		 *
		 * Display quarterly and Annually plans, to be hidden.
		 */
		$prices_total = array(
			3  => __('every 3 months', 'wp-ultimo'),
			12 => __('yearly', 'wp-ultimo'),
		);

		foreach ($prices_total as $freq => $string) {

			$price_variation = $product->get_price_variation($freq, 'month');

			if (!$price_variation || $product->get_pricing_type() == 'free' || $product->get_pricing_type() == 'contact_us') {

                echo "<li v-cloak v-show='duration == ".$freq."' class='total-price total-price-$freq'>-</li>";

			} else {

                $text = sprintf(__('%1$s, billed %2$s', 'wp-ultimo'), wu_format_currency($price_variation['amount']), $string);

                $extra_check_for_annual = '';

                if ($freq === 12) {

					        $extra_check_for_annual = ' || (duration == "1" && duration_unit == "year")';

                } // end if;

                echo "<li v-cloak v-show='duration == ".$freq.$extra_check_for_annual."' class='total-price total-price-$freq'>$text</li>";

                } // end if;

              } // end foreach;

            ?>

		        <?php foreach ($product->get_pricing_table_lines() as $line) : ?>

              <li><?php echo $line; ?></li>

            <?php endforeach; ?>

            <li class="wu-cta">

              <button 
                v-if="<?php echo json_encode($product->get_pricing_type() !== 'contact_us'); ?>" 
                v-on:click="add_plan(<?php echo $product->get_id(); ?>)" 
                type="button" 
                name="products[]" 
                value="<?php echo $product->get_id(); ?>" 
                class="button button-primary button-next"
              >
	            	<?php _e('Select Plan', 'wp-ultimo'); ?>
              </button>

              <button 
                v-else 
                v-on:click="open_url('<?php echo esc_url($product->get_contact_us_link()); ?>', '_blank');" type="button" 
                name="products[]" 
                value="<?php echo $product->get_id(); ?>" 
                class="button button-primary button-next"
              >
            		<?php _e('Select Plan', 'wp-ultimo'); ?>
              </button>

            </li>

          </ul>
          <!-- Feature List Ends -->

        </div>

      <?php endforeach; ?>

    </div>
    
  </div>

<?php endif; ?>

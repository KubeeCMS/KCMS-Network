<?php
/**
 * Template File: Basic Pricing Table.
 * 
 * To see what methods are available on the product variable, @see inc/models/class-producs.php.
 * 
 * This template can also be overrid using template overrides. 
 * See more here: @todo add link to template overrides.
 * 
 * @since 2.0.0
 * @param array $products List of product objects.
 * @param string $name ID of the field.
 * @param string $label The field label.
 */
?>

<?php if (empty($products)) : ?>

<div class="wu-text-center wu-bg-gray-100 wu-rounded wu-uppercase wu-font-semibold wu-text-xs wu-text-gray-700 wu-p-4">

  <?php _e('No Products Found.', 'wp-ultimo'); ?>

</div>

<?php else : ?>

  <ul class="wu-plans-frequency-selector">

    <li>

      <?php if (wu_get_setting('enable_price_1', true)) : ?>

        <a v-on:click.prevent="billing_option = 1" :class="billing_option == 1 ? 'active' : ''" href="#">
          <?php _e('Monthly', 'wp-ultimo'); ?>
        </a>

      <?php endif; ?>

    </li>

    <li>

      <?php if (wu_get_setting('enable_price_3', true)) : ?>

        <a v-on:click.prevent="billing_option = 3" :class="billing_option == 3 ? 'active' : ''" href="#">
          <?php _e('Quarterly', 'wp-ultimo'); ?>
        </a>

      <?php endif; ?>

    </li>

    <li>

      <?php if (wu_get_setting('enable_price_3', true)) : ?>

        <a v-on:click.prevent="billing_option = 12" :class="billing_option == 12 ? 'active' : ''" href="#">
          <?php _e('Yearly', 'wp-ultimo'); ?>
        </a>
      
      <?php endif; ?>

    </li>

  </ul>

  <div class="layer plans">

    <?php foreach ($products as $product) : ?>

      <div 
        id="plan-<?php echo esc_attr($product->get_id()); ?>"
        class="lift wu-plan plan-tier wu-w-full sm:wu-w-4/12 <?php echo esc_attr($product->top_deal ? 'callout' : ''); ?>"
      >

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

            <span class="plan-price" v-show="billing_option == 1">
            
              <?php 

              $n = $product->get_amount();

              echo str_replace(wu_get_currency_symbol(), '', wu_format_currency($n));

              ?>
            
            </span>

            <?php 

            /**
             * 
             * Display quarterly and Annually plans, to be hidden.
             * 
             */
            $periods = array(3, 12);

            foreach ($periods as $freq) :

              $price_variation = $product->get_price_variation($freq, 'month');

              ?>

              <span class="plan-price" v-cloak v-show="billing_option == <?php echo $freq; ?>">

                <?php 

                $n = $price_variation ? $price_variation->monthly_amount : false;
                
                if ($n) {

                  echo str_replace(wu_get_currency_symbol(), '', wu_format_currency($n));

                } else {

                  echo '--';

                } // end if;
                
                ?>

              </span>

            <?php endforeach; ?>

            <sub>
            
            <?php 

            /**
             * Period Unit
             */
            echo (!$symbol_left ? wu_get_currency_symbol() : '').' '.__('/mo', 'wp-ultimo'); 

            ?>
              
            </sub>

          </h5>
          <!-- end Price -->

        <?php endif; ?>

        <p class="early-adopter-price">
          
          <?php echo $product->get_description(); ?>

        </p>

        <br>

        <!-- Feature List Begins -->
        <ul>

          <?php 

          /**
           * 
           * Display quarterly and Annually plans, to be hidden.
           * 
           */
          $prices_total = array(
            3  => __('every 3 months', 'wp-ultimo'), 
            12 => __('yearly', 'wp-ultimo'), 
          );

          foreach ($prices_total as $freq => $string) {

            $price_variation = $product->get_price_variation($freq, 'month');

            if (!$price_variation || $product->get_pricing_type() == 'free' || $product->get_pricing_type() == 'contact_us') {
              
              echo "<li v-cloak v-show='billing_option == ".$freq."' class='total-price total-price-$freq'>-</li>";

            } else {

              $text = sprintf(__('%1$s, billed %2$s', 'wp-ultimo'), wu_format_currency($price_variation->amount), $string);
              
              echo "<li v-cloak v-show='billing_option == ".$freq."' class='total-price total-price-$freq'>$text</li>";

            } // end if;
            
          } // end foreach;

          ?>

          <?php foreach ($product->get_pricing_table_lines() as $line) : ?>

            <li><?php echo $line; ?></li>

          <?php endforeach; ?>

          <li class="wu-cta">

            <button 
              type="submit" 
              name="products[]" 
              class="button button-primary button-next" 
              value="<?php echo esc_attr($product->get_id()); ?>"
            >
              <?php _e('Select Plan', 'wp-ultimo'); ?>
            </button>

          </li>

        </ul>
        <!-- Feature List Ends -->

      </div>

    <?php endforeach; ?>

  </div>

<?php endif; ?>

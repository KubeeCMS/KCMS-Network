<?php
/**
 * Order summary view.
 *
 * @since 2.0.0
 */
?>
<div id="wu-order-summary-content" class="wu-relative">

  <div v-show="!order" class="wu-bg-gray-100 wu-p-4 wu-text-center wu-border wu-border-solid wu-border-gray-300">

    <?php _e('Generating Order Summary...', 'wp-ultimo'); ?>

  </div>

  <div v-if="order" v-cloak>

    <table id="wu-order-summary-table" class="wu-w-full wu-mb-0">

      <thead>

        <tr class="">

          <th class="col-description">
            <?php _e('Description', 'wp-ultimo'); ?>
          </th>

          <?php if ($table_columns === 'simple') : ?>

            <th class="col-total-gross">
              <?php _e('Subtotal', 'wp-ultimo'); ?>
            </th>

          <?php else : ?>

            <th class="col-total-net">
              <?php _e('Net Total', 'wp-ultimo'); ?>
            </th>

            <th class="col-total-vat-percentage">
              <?php _e('Discounts', 'wp-ultimo'); ?>
            </th>

            <th class="col-total-tax">
              <?php _e('Tax', 'wp-ultimo'); ?>
            </th>

            <th class="col-total-gross">
              <?php _e('Gross Total', 'wp-ultimo'); ?>
            </th>

          <?php endif; ?>

        </tr>

      </thead>

      <tbody>

        <tr v-if="order.line_items.length === 0">

          <td class="" colspan="<?php echo esc_attr($table_columns === 'simple') ? 2 : 5; ?>" class="col-description">

            <?php _e('No products in shopping cart.', 'wp-ultimo'); ?>

          </td>

        </tr>

        <tr v-for="line_item in order.line_items">

          <td class="wu-py-2 col-description" v-show="line_item.recurring">

            <?php printf(__('Subscription - %s', 'wp-ultimo'), '{{ line_item.title }}'); ?>
            
            <small v-if="line_item.type == 'product'" class="wu-ml-3 wu-text-xs">

              <a href="#" class="wu-no-underline" v-on:click.prevent="remove_product(line_item.product_id, line_item.product_slug)">

                <?php _e('Remove', 'wp-ultimo'); ?>

              </a>

            </small>

          </td>

          <td class="wu-py-2 col-description" v-show="!line_item.recurring">
            
            {{ line_item.title }}
            
            <small v-if="line_item.type == 'product'" class="">

              <a href="#" class="wu-no-underline" v-on:click.prevent="remove_product(line_item.product_id, line_item.product_slug)">
            
                <?php _e('Remove', 'wp-ultimo'); ?>
              
              </a>

            </small>

          </td>

          <?php if ($table_columns === 'simple') : ?>

            <td v-show="line_item.recurring" class="wu-py-2 col-total-net">
              
              {{ wu_format_money(line_item.subtotal) }} / {{ line_item.recurring_description }}

            </td>

            <td v-show="!line_item.recurring" class="wu-py-2 col-total-net">
              
              {{ wu_format_money(line_item.subtotal) }}

            </td>

          <?php else : ?>

            <td v-show="line_item.recurring" class="wu-py-2 col-total-net">
              
              {{ wu_format_money(line_item.subtotal) }} / {{ line_item.recurring_description }}

            </td>

            <td v-show="!line_item.recurring" class="wu-py-2 col-total-net">
              
              {{ wu_format_money(line_item.subtotal) }}

            </td>

            <td class="wu-py-2 col-total-net">
              
              {{ wu_format_money(line_item.discount_total) }}

            </td>

            <td class="wu-py-2 col-total-tax">

              {{ wu_format_money(line_item.tax_total) }}

              <small v-if="line_item.tax_rate" class="wu-block">
              
                {{ line_item.tax_label }} {{ line_item.tax_rate }}%
                
              </small>

            </td>

            <td class="wu-py-2 col-total-gross">
              
              {{ wu_format_money(line_item.total) }}
              
            </td>

          <?php endif; ?>

        </tr>

      </tbody>

      <tfoot class="">

        <?php if ($table_columns === 'simple') : ?>

          <tr>

            <td>

              <?php _e("Discounts", 'wp-ultimo'); ?>

            </td>

            <td>

              {{ wu_format_money(order.totals.total_discounts) }}

            </td>

          </tr>

          <tr>

            <td>

              <?php _e("Taxes", 'wp-ultimo'); ?>

            </td>

            <td>

              {{ wu_format_money(order.totals.total_taxes) }}

            </td>

          </tr>

        <?php endif; ?>

        <tr>

          <td class="" colspan="<?php echo esc_attr($table_columns === 'simple') ? 1 : 4; ?>">

            <strong><?php _e("Today's Grand Total", 'wp-ultimo'); ?></strong>

          </td>

          <td class="" v-show="order.has_trial">

            {{ wu_format_money(0) }}

          </td>

          <td class="" v-show="!order.has_trial">

            {{ wu_format_money(order.totals.total) }}

          </td>

        </tr>

        <tr v-if="order.has_trial">

          <td class="" colspan="<?php echo esc_attr($table_columns === 'simple') ? 1 : 4; ?>">

            <small>
              <?php printf(__('Total in %1$s - end of trial period.', 'wp-ultimo'), '{{ $moment.unix(order.dates.date_trial_end).format(`LL`) }}'); ?>
            </small>

          </td>

          <td class="">

            {{ wu_format_money(order.totals.total) }}

          </td>

        </tr>

      </tfoot>

    </table>

    <ul class="wu-p-0 wu-m-0 wu-mt-2 wu-list-none wu-order-summary-additional-info wu-text-sm">

      <li v-if="!order.has_trial && order.has_recurring">

        <?php printf(__('Next fee of %1$s will be billed in %2$s.', 'wp-ultimo'), '{{ wu_format_money(order.totals.recurring.total) }}', "{{ \$moment.unix(order.dates.date_next_charge).format(`LL`) }}"); ?>

      </li>

      <li class="order-description" v-if="order.totals.total_discounts < 0">
      
        <?php 
        // translators: 1 is the discount name (e.g. Launch Promo). 2 is the coupon code (e.g PROMO10), 3 is the coupon amount and 4 is the discount total.
        printf(__('Discount applied: %1$s - %2$s (%3$s) %4$s', 'wp-ultimo'), '{{ order.discount_code.name }}', '{{ order.discount_code.code }}', '{{ order.discount_code.discount_description }}', '{{ wu_format_money(-order.totals.total_discounts) }}'); ?>
        
        <a class="wu-no-underline wu-ml-2" href="#" v-on:click.prevent="discount_code = ''">
          
          <?php _e('Remove', 'wp-ultimo'); ?>

        </a>

      </li>

    </ul>

  </div>

</div>

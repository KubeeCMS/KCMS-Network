<?php
/**
 * Order summary view.
 *
 * @since 2.0.0
 */
?>
<div id="wu-order-summary-content">

  <p v-show="!order" class="wu-bg-gray-200 wu-p-4 wu-text-center wu-border wu-border-solid wu-border-gray-300">

    <?php _e('Generating Order Summary...', 'wp-ultimo'); ?>

  </p>

  <div v-if="order" v-cloak>

    <table id="wu-order-summary-table">

      <thead>

        <tr class="wu-bg-gray-100 wu-rounded">

          <th class="wu-text-left wu-p-4 col-description">
            <?php _e('Description', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-left wu-p-4 col-total-net">
            <?php _e('Net Total', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-left wu-p-4 col-total-vat-percentage">
            <?php _e('Discounts', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-left wu-p-4 col-total-tax">
            <?php _e('Tax', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-left wu-p-4 col-total-gross">
            <?php _e('Gross Total', 'wp-ultimo'); ?>
          </th>

        </tr>

      </thead>

      <tbody>

        <tr v-if="order.line_items.length === 0">

          <td class="wu-p-4" colspan="5" class="col-description">
            <?php _e('No products on shopping cart.', 'wp-ultimo'); ?>
          </td>

        </tr>

        <tr v-for="line_item in order.line_items">

          <td class="wu-px-4 wu-py-2 col-description" v-show="line_item.recurring">
            Subscription - {{ line_item.title }}
            <small v-if="line_item.type == 'product'" class="wu-ml-3 wu-text-xs">
              <a href="#" class="wu-no-underline" @click.prevent="remove_product(line_item.product_id, line_item.product_slug)">
                Remove
              </a>
            </small>
          </td>

          <td class="wu-px-4 wu-py-2 col-description" v-show="!line_item.recurring">
            {{ line_item.title }}
            <small v-if="line_item.type == 'product'" class="wu-ml-3 wu-text-xs">
              <a href="#" class="wu-no-underline" @click.prevent="remove_product(line_item.product_id, line_item.product_slug)">
                Remove
              </a>
            </small>
          </td>

          <td v-show="line_item.recurring" class="wu-px-4 wu-py-2 col-total-net">
            {{ wu_format_money(line_item.unit_price) }} / {{ line_item.duration_unit }}
          </td>

          <td v-show="!line_item.recurring" class="wu-px-4 wu-py-2 col-total-net">
            {{ wu_format_money(line_item.subtotal) }}
          </td>

          <td class="wu-px-4 wu-py-2 col-total-net">
            {{ wu_format_money(line_item.discount_total) }}
          </td>

          <td class="wu-px-4 wu-py-2 col-total-tax">

            {{ wu_format_money(line_item.tax_total) }}

            <span v-if="line_item.tax_rate" class="wu-block wu-text-xs">
              {{ line_item.tax_label }} {{ line_item.tax_rate }}%
            </span>

          </td>

          <td class="wu-px-4 wu-py-2 col-total-gross">
            {{ wu_format_money(line_item.total) }}
          </td>

        </tr>

      </tbody>

      <tfoot class="wu-bg-gray-100 wu-rounded">

        <tr v-if="order.totals.total_discounts">

          <td colspan="4" class="col-description">
            Subtotal
          </td>

          <td class="col-total-gross">
            {{ wu_format_money(order.totals.subtotal) }}
          </td>

        </tr>

        <tr>

          <td class="wu-p-4" colspan="4">
            <strong>Today's Grand Total</strong>
          </td>

          <td class="wu-p-4" v-show="order.has_trial">
            {{ wu_format_money(0) }}
          </td>

          <td class="wu-p-4" v-show="!order.has_trial">
            {{ wu_format_money(order.totals.total) }}
          </td>

        </tr>

        <tr v-if="order.has_trial">

          <td class="wu-p-4" colspan="4">
            <small>
              Total on {{ $moment.unix(order.dates.date_trial_end).calendar() }} - end of trial period
            </small>
          </td>

          <td class="wu-p-4">
            {{ wu_format_money(order.totals.total) }}
          </td>

        </tr>

      </tfoot>

    </table>

    <div v-if="!order.has_trial && order.has_recurring" class="order-description wu-p-4">

      Next fee of {{ wu_format_money(order.totals.recurring.total) }} will be billed on {{ $moment.unix(order.dates.date_next_charge).calendar() }}.

    </div>

    <div class="wu-p-4" v-if="order.totals.total_discounts < 0">
    
      Discount applied: {{ order.discount_code.name }} - {{ order.discount_code.code }} ({{ order.discount_code.discount_description }}) {{ wu_format_money(-order.totals.total_discounts) }}

      <a class="wu-no-underline wu-ml-2" href="#" @click.prevent="discount_code = ''">Remove</a>

    </div>

</div>

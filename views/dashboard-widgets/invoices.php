<?php
/**
 * Invoices
 *
 * @since 2.0.0
 */
?>
<div class="wu-styling <?php echo esc_attr($className); ?>">

  <div class="<?php echo wu_env_picker('', 'wu-widget-inset'); ?>">

    <?php if ($title) : ?>

      <!-- Title Element -->
      
      <div class="wu-p-4 wu-flex wu-items-center <?php echo wu_env_picker('', 'wu-bg-gray-100 wu-border-solid wu-border-0 wu-border-b wu-border-gray-400'); ?>">


          <h3 class="wu-m-0 <?php echo wu_env_picker('', 'wu-widget-title'); ?>">

            <?php echo $title; ?>

          </h3>


      </div>  
      
      <!-- Title Element - End -->

    <?php endif; ?>

    <table class="striped <?php echo wu_env_picker('', 'wp-list-table widefat wu-border-none'); ?>">

      <thead class="wu-uppercase">

        <tr>

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-table-cell lg:wu-hidden" style="width: 100%;">
            <?php _e('Invoice', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell" style="width: 5%;">
            &nbsp;
          </th>

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell" style="width: 20%;">
            <?php _e('ID', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell" style="width: 40%;">
            <?php _e('Date', 'wp-ultimo'); ?>
          </th>

          <!-- <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell" style="width: 30%;">
            <?php _e('Payment Method', 'wp-ultimo'); ?>
          </th> -->

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell" style="width: 15%;">
            <?php _e('Amount', 'wp-ultimo'); ?>
          </th>

          <th class="wu-text-xs wu-px-4 wu-py-2 wu-font-semibold wu-text-gray-700 wu-hidden lg:wu-table-cell">
            &nbsp;
          </th>

        </tr>

      </thead>

      <tbody class="wu-align-baseline">

        <?php foreach ($membership->get_payments() as $payment) : ?>
          
          <!-- Invoice Item -->
          <tr>

            <td class="wu-align-middle wu-p-4 wu-table-cell lg:wu-hidden">

              <span class="dashicons-wu-check wu-text-green-700"></span> <code>1234567890</code>
              
            </td>

            <td class="wu-align-middle wu-p-4 wu-hidden lg:wu-table-cell wu-text-center">

              <span class="dashicons-wu-check wu-text-green-700"></span>
              
            </td>

            <td class="wu-align-middle wu-p-4 wu-hidden lg:wu-table-cell">
              
              <code><?php echo $payment->get_hash(); ?></code>

            </td>

            <td class="wu-align-middle wu-p-4 wu-hidden lg:wu-table-cell">
              
              <?php echo $payment->get_date_created(); ?>

            </td>

            <!-- <td class="wu-align-middle wu-p-4 wu-hidden lg:wu-table-cell">
              
              <span class="dashicons-wu-paypal wu-align-middle"></span> Not working

            </td> -->

            <td class="wu-align-middle wu-p-4 wu-hidden lg:wu-table-cell">
              
              <?php echo wu_format_currency($payment->get_total(), $payment->get_currency()); ?>

            </td>

            <td class="wu-align-middle wu-p-4 wu-text-right wu-hidden lg:wu-table-cell">
              
              <a target="_blank" class="wu-no-underline" href="<?php echo $payment->get_invoice_url(); ?>" title="<?php esc_attr_e('Download Invoice', 'wp-ultimo'); ?>">
                
                <span class="dashicons-wu-download"></span>

              </a>

            </td>
          
          </tr>
          <!-- Invoice Item - End -->

      <?php endforeach; ?>    

      </tbody>

    </table>

  </div>

</div>

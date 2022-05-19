<?php
/**
 * Template invoice view.
 *
 * @since 2.0.0
 */
?>
<style>
.invoice-box {
    width: 100%;
    margin: auto;
    font-size: 16px;
    line-height: 24px;
    color: #555;
}

.invoice-box table {
    width: 100%;
    line-height: inherit;
    text-align: left;
}

.invoice-box table td {
    padding: 5px;
    vertical-align: top;
}

.invoice-box table tr td:nth-child(2) {
    text-align: right;
}

.invoice-box table tr.top table td {
    padding-bottom: 20px;
}

.invoice-box table tr.top table td.title {
    font-size: 45px;
    line-height: 45px;
    color: #333;
}

.invoice-box table tr.information table td {
    padding-bottom: 40px;
}

.invoice-box table tr.heading td {
    background: #eee;
    border-bottom: 1px solid #ddd;
    font-weight: 500;
}

.invoice-box table {
    border-collapse: 1;
}

.invoice-box table tr.heading th {
    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;
}

.invoice-box table tr.item td {
    vertical-align: middle;
}

.invoice-box table tr.heading th {
    background: #eee;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    padding: 10px;
    text-align: right;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 80%;
}

.invoice-box table tr.details td {
    padding: 10px;
}

.invoice-box table tr.item td{
    border-bottom: 1px solid #eee;
    padding: 10px;
}

.invoice-box table tr.item.last td {
    border-bottom: none;
}

.invoice-box table tr.total td {
    border-top: 2px solid #eee;
    font-weight: bold;
    padding-bottom: 60px;
    padding-top: 10px;
    text-align: right;
}

@media only screen and (max-width: 600px) {
    .invoice-box table tr.top table td {
        width: 100%;
        display: block;
        text-align: center;
    }

    .invoice-box table tr.information table td {
        width: 100%;
        display: block;
        text-align: center;
    }
}

/** RTL **/
.rtl {
    direction: rtl;

}

.rtl table {
    text-align: right;
}

.rtl table tr td:nth-child(2) {
    text-align: left;
}

.primary-color {
  padding: 10px;
  background-color: <?php echo $primary_color; ?>;
}
</style>

<div class="invoice-box">
    <table cellpadding="0" cellspacing="0">
        <tr class="top">
            <td colspan="5">
                <table>
                    <tr>
                        <td class="title">
                            <?php if ($use_custom_logo && $custom_logo) : ?>

						        <?php $image_attributes = wp_get_attachment_image_src($custom_logo, 'full'); ?>

						        <img src="<?php echo $image_attributes[0]; ?>" width="100" height="" />

                            <?php else : ?>

                                <img width="100" src="<?php echo $logo_url; ?>" alt="<?php echo get_network_option(null, 'site_name'); ?>">
                                
                            <?php endif; ?>
                        </td>

                        <td>
                            <strong><?php _e('Invoice #', 'wp-ultimo'); ?></strong><br>
                            <?php echo $payment->get_invoice_number(); ?>
                            <br>
                            <?php printf(__('Created: %s', 'wp-ultimo'), date_i18n(get_option('date_format'), strtotime($payment->get_date_created()))); ?><br>
                            <?php _e('Due on Receipt', 'wp-ultimo'); ?><br>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="5">
                <table>
                    <tr>
                        <td>
                            <strong>
                                <?php

                                /**
                                 * Displays company name.
                                 */
                                echo $company_name;

                                ?>
                            </strong>

                            <br>

                            <?php

                            /**
                             * Displays the company address.
                             */
                            echo nl2br($company_address);

                            ?>
                        </td>

                        <td>
                            <strong><?php _e('Bill to', 'wp-ultimo'); ?></strong>
                            <br>
                            <?php

                            /**
                             * Displays the clients address.
                             */
                            echo nl2br(implode(PHP_EOL, (array) $billing_address));

                            ?>
                           
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">

            <th style="text-align: left;">
                <?php _e('Item', 'wp-ultimo'); ?>
            </th>

            <th style="width: 17%;">
                <?php _e('Price', 'wp-ultimo'); ?>
            </th>

            <th style="width: 17%;">
                <?php _e('Discount', 'wp-ultimo'); ?>
            </th>

            <th style="width: 17%;">
                <?php _e('Tax', 'wp-ultimo'); ?>
            </th>

            <th style="width: 17%;">
                <?php _e('Total', 'wp-ultimo'); ?>
            </th>

        </tr>

        <?php foreach ($line_items as $line_item) : ?>

            <tr class="item">

                <td>
                    <span class="font-weight: medium;"><?php echo $line_item->get_title(); ?></span>
                    <br>
                    <small><?php echo $line_item->get_description(); ?></small>
                </td>

                <td style="text-align: right;">
                    <?php echo wu_format_currency($line_item->get_subtotal(), $payment->get_currency()); ?>
                </td>

                <td style="text-align: right;">
                    <?php echo wu_format_currency($line_item->get_discount_total(), $payment->get_currency()); ?>
                </td>

                <td style="text-align: right;">
                    <?php echo wu_format_currency($line_item->get_tax_total(), $payment->get_currency()); ?>
                    <br>
                    <small><?php echo $line_item->get_tax_label(); ?> (<?php echo $line_item->get_tax_rate(); ?>%)</small>
                </td>

                <td style="text-align: right;">
                    <?php echo wu_format_currency($line_item->get_total(), $payment->get_currency()); ?>
                </td>

            </tr>

        <?php endforeach; ?>

        <tr class="total">
            <td colspan='5'>
                <?php printf(__('Total: %s', 'wp-ultimo'), wu_format_currency($payment->get_total(), $payment->get_currency())); ?>
            </td>
        </tr>

        <?php if (!$payment->is_payable()) : ?>

            <tr class="heading">
                <th colspan="5" style="text-align: left;">
                    <?php _e('Payment Method', 'wp-ultimo'); ?>
                </th>
            </tr>

            <tr class="details">
                <td colspan="5">
                    <?php echo $payment->get_payment_method(); ?>
                </td>
            </tr>

        <?php endif; ?>
    </table>
</div>

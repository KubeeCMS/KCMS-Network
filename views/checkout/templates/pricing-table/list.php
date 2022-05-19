<?php
/**
 * List Template field view.
 *
 * @since 2.0.0
 */

/**
 * Deal with different pricing options
 */
foreach ($products as $index => &$_product) {

  $_product = wu_get_product($_product['id']);

  $product_variation = $_product->get_as_variation($duration, $duration_unit);

  if ($product_variation === false && !$force_different_durations) {

    unset($products[$index]);
    
    $_product = $product_variation;
    
  } // end if;

} // end foreach;

?>
<div class="">

  <div class="wu-grid wu-grid-flow-row wu-gap-4 <?php echo $classes; ?>">

    <?php foreach ($products as $product) : ?>

      <label
        id="wu-product-<?php echo $product->get_id(); ?>"
        class="wu-relative wu-block wu-rounded-lg wu-border wu-border-gray-300 wu-bg-white wu-border-solid wu-shadow-sm wu-px-6 wu-py-4 wu-cursor-pointer hover:wu-border-gray-400 sm:wu-flex sm:wu-justify-between focus-within:wu-ring-1 focus-within:wu-ring-offset-2 focus-within:wu-ring-indigo-500">

        <input v-if="<?php echo json_encode($product->get_pricing_type() !== 'contact_us'); ?>" v-on:click="$parent.add_plan(<?php echo $product->get_id(); ?>)" type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>" class="screen-reader-text wu-hidden">

        <input v-else v-on:click="$parent.open_url('<?php echo esc_url($product->get_contact_us_link()); ?>', '_blank');" type="checkbox" name="products[]" value="<?php echo $product->get_id(); ?>" class="screen-reader-text wu-hidden">

        <div class="wu-flex wu-items-center">
          <div class="wu-text-sm">
            <span id="server-size-0-label" class="wu-font-semibold wu-block wu-text-gray-900">
              <?php echo $product->get_name(); ?>
            </span>
            <div id="server-size-0-description-0" class="wu-text-gray-600">
              <p class="sm:wu-inline">
                <?php echo $product->get_description(); ?>
              </p>
            </div>
          </div>
        </div>
        <div id="server-size-0-description-1" class="wu-mt-2 wu-flex wu-text-md sm:wu-mt-0 sm:wu-block sm:wu-ml-4 sm:wu-text-right">
          <div class="wu-font-semibold wu-text-gray-900"><?php echo $product->get_formatted_amount(); ?></div>
          <div class="wu-ml-1 wu-text-sm wu-text-gray-500 sm:wu-ml-0"><?php echo $product->get_recurring_description(); ?></div>
        </div>
        
        <div
          class="wu-absolute wu--inset-px wu-rounded-lg wu-border-solid wu-border-2 wu-pointer-events-none wu-top-0 wu-bottom-0 wu-right-0 wu-left-0" 
          :class="$parent.has_product(<?php echo $product->get_id(); ?>) || $parent.has_product('<?php echo $product->get_slug(); ?>') ? 'wu-border-blue-500' : 'wu-border-transparent'"
          aria-hidden="true"
        >
        </div>
      </label>

    <?php endforeach; ?>

  </div>
  
</div>

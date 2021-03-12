<?php
/**
 * Select2 field view.
 *
 * @since 2.0.0
 */
?>
<?php

$setting = wu_get_setting($field_slug);

$setting = is_array($setting) ? $setting : array();

$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';

// WU_Scripts()->enqueue_select2();

?>

<tr>
  <th scope="row"><label for="<?php echo $field_slug; ?>"><?php echo $field['title']; ?></label> <?php echo WU_Util::tooltip($field['tooltip']); ?> </th>
  <td>

    <select data-width="350px" multiple="multiple" placeholder="<?php echo $placeholder; ?>"  class="wu-select" name="<?php echo $field_slug; ?>[]" id="<?php echo $field_slug; ?>">

      <?php foreach ($field['options'] as $value => $option) : ?>
      <option <?php selected(in_array($value, $setting)); ?> value="<?php echo $value; ?>"><?php echo $option; ?></option>
      <?php endforeach; ?>

    </select>

    <?php if (!empty($field['desc'])) : ?>
    <p class="description" id="<?php echo $field_slug; ?>-desc">
      <?php echo $field['desc']; ?>
    </p>
    <?php endif; ?>

  </td>
</tr>

<?php
/**
 * Heading collapsible field view.
 *
 * @since 2.0.0
 */
?>
<div data-target="<?php echo 'collapsible-'.$field_slug; ?>" class="wu-settings-heading-collapsible wu-col-sm-12 <?php echo isset($field['active']) && !$field['active'] ? 'wu-settings-heading-collapsible-disabled' : ''; ?>">
  <?php echo $field['title']; ?>
</div>

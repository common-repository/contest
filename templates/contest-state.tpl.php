<?php 

/**
 * @file contest-state.tpl.php
 * The template for the contest state select field.
 *
 * Available variables:
 * - $state (string) The default value.
 * - $states (array) An array of state abbreviations to state name.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<select name="contest_state" id="edit-contest-state" class="form-select required" pattern="^.+$">
  
<?php foreach ($states as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($state, $value); ?>><?php print $label; ?></option>
<?php endforeach; ?>

</select>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

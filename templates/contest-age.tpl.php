<?php

/**
 * @file contest-age.tpl.php
 * The template file to display the minimum age select field.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<select name="contest_settings[min_age]">

<?php foreach ((array('' => '-' . __('Age') . '-') + array_combine(range(1, 100), range(1, 100))) as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($default_value, $value); ?>><?php print $label; ?></option>
<?php endforeach; ?>

</select>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

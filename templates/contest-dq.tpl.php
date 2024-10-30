<?php

/**
 * @file contest-dq.tpl.php
 * The template file to display the disqualification days select field.
 *
 * Available variables:
 * - $default_value (int) The default period.
 * - $periods (array) An array of periods to disqualify a user from winning again.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<select name="contest_settings[dq_days]">

<?php foreach ((array('' => '-' . __('Disqualification Days') . '-') + $periods) as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($default_value, $value); ?>><?php print __($label); ?></option>
<?php endforeach; ?>

</select>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

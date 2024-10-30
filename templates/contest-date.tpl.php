<?php 

/**
 * @file
 * The template for the contest birthdate and start/end date select boxes.
 *
 * Available variables:
 * - $day (int) The default day.
 * - $days (array) An array of days, (1 to 31).
 * - $month (int) The default month.
 * - $months (array) An array of the months.
 * - $year (int) The default year.
 * - $years (array) An array of years.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<!-- The month select box -->

<select name="<?php print $field_name; ?>[month]" id="edit-<?php print $field_name; ?>-month" class="form-select required">
  
<?php foreach ($months as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($month, $value); ?>><?php print __($label); ?></option>
<?php endforeach; ?>

</select>

<!-- The day select box -->

<select name="<?php print $field_name; ?>[day]" id="edit-<?php print $field_name; ?>-day" class="form-select required">
  
<?php foreach ($days as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($day, $value); ?>><?php print $label; ?></option>
<?php endforeach; ?>

</select>

<!-- The year select box -->

<select name="<?php print $field_name; ?>[year]" id="edit-<?php print $field_name; ?>-year" class="form-select required">
  
<?php foreach ($years as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($year, $value); ?>><?php print __($label); ?></option>
<?php endforeach; ?>

</select>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

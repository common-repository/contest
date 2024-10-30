<?php

/**
 * @file contest-block-form.tpl.php
 * The template file to display the contest block's form on the widget page.
 *
 * Available variables:
 * - $this (object)
 * - $title (string)
 * - $max (int)
 * - $max_options (array)
 * - $show (int)
 * - $show_options (array)
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<label for="<?php print $this->get_field_id('title'); ?>"><?php print __('Title:'); ?></label>
<input class="widefat" id="<?php print $this->get_field_id('title'); ?>" name="<?php print $this->get_field_name('title'); ?>" type="text" value="<?php print $title; ?>"><br />
<label for="<?php print $this->get_field_id('max'); ?>"><?php print __('Maximum:'); ?></label>
<select class="widefat" id="<?php print $this->get_field_id('max'); ?>" name="<?php print $this->get_field_name('max'); ?>">

<?php foreach ($max_options as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($max, $value); ?>><?php print $label; ?></option>
<?php endforeach; ?>

</select>
<label for="<?php print $this->get_field_id('show'); ?>"><?php print __('Show:'); ?></label>
<select class="widefat" id="<?php print $this->get_field_id('show'); ?>" name="<?php print $this->get_field_name('show'); ?>">

<?php foreach ($show_options as $value => $label): ?>
  <option value="<?php print $value; ?>"<?php selected($show, $value); ?>><?php print $label; ?></option>
<?php endforeach; ?>

</select>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

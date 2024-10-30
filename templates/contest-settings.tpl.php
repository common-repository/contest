<?php

/**
 * @file contest-settings.tpl.php
 * The template for displaying the contest settings page.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<h2><?php print __('Contest'); ?></h2>
<form action="options.php" method="post">
  <?php settings_fields('pluginPage'); ?>
  <?php do_settings_sections('pluginPage'); ?>
  <?php submit_button(); ?>
</form>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

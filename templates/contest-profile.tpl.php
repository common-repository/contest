<?php

/**
 * @file contest-profile.tpl.php
 * The template for the displaying the contest user fields on the user's profile page.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<section>
  <h3><?php print __('Contest Profile'); ?></h3>
  <table class="contest-form-table form-table">
    <?php require contest_theme(CONTEST_DIR . '/templates/contest-fields.tpl.php'); ?>
  </table>
  <?php require contest_theme(CONTEST_DIR . '/templates/contest-field-optin.tpl.php'); ?>
</section>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

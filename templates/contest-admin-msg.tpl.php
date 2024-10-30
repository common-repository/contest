<?php

/**
 * @file contest-admin-msg.tpl.php
 * The template for the displaying status messages on the admin pages.
 *
 * Available variables:
 * - $msg: (string) An unordered list of messages.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="message" class="updated notice contest-notice notice-success is-dismissible below-h1">
  <ul>
    
  <?php foreach ($msgs as $msg): ?>
    <li><?php print $msg; ?></li>
  <?php endforeach; ?>
  
  </ul>
  <button type="button" class="notice-dismiss">
    <span class="screen-reader-text"><?php print __('Dismiss this notice.'); ?></span>
  </button>
</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

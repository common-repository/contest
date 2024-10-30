<?php

/**
 * @file contest-msg.tpl.php
 * The template for the displaying status messages on the user pages.
 *
 * Available variables:
 * - $msg: (string) An unordered list of messages.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="contest-msgs" class="contest-msgs">
  <h2><?php print __('Contest Status'); ?>:</h2>
  <ul>
    
  <?php foreach ($msgs as $msg): ?>
    <li><?php print $msg; ?></li>
  <?php endforeach; ?>
  
  </ul>
</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

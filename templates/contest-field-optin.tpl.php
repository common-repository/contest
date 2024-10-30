<?php

/**
 * @file contest-field-optin.tpl.php
 * The template for the contest optin field.
 *
 * Available variables:
 * - $usr (object) A ContestUser object with the following properties:
 * - - uid (int)
 * - - name (string)
 * - - mail (string)
 * - - title (string)
 * - - full_name (string)
 * - - business (string)
 * - - address (string)
 * - - city (string)
 * - - state (string)
 * - - zip (string)
 * - - phone (string)
 * - - birthdate (int)
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="contest-optin-wrapper" class="contest-field-wrapper">
  <label for="contest-optin"><?php print __('Opt In'); ?>:</label><input type="checkbox" name="contest_optin" value="1"<?php print !empty($usr->optin)? ' checked': ''; ?> class="regular-text" id="contest-optin" pattern="^1$" />
</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

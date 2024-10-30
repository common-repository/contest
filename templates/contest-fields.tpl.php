<?php

/**
 * @file contest-fields.tpl.php
 * The template for the user fields common to the contest and user pages.
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
<tr>
  <th class="contest-profile-label"><label for="contest-address"><?php print __('Address'); ?>:</label></th>
  <td><input type="text" name="contest_address" value="<?php print !empty($usr->address)? $usr->address: ''; ?>" size="30" maxlength="<?php print CONTEST_ADDRESS_MAX; ?>" class="regular-text" id="contest-address" pattern="^\s*[\s\w\-\.\,#]+\s*$" /></td>
</tr>
<tr>
  <th class="contest-profile-label"><label for="contest-city"><?php print __('City'); ?>:</label></th>
  <td><input type="text" name="contest_city" value="<?php print !empty($usr->city)? $usr->city: ''; ?>" size="30" maxlength="<?php print CONTEST_CITY_MAX; ?>" class="regular-text" id="contest-city" pattern="^\s*[\s\w\-\.]+\s*$" /></td>
</tr>
<tr>
  <th class="contest-profile-label"><label for="contest-state"><?php print __('State'); ?>:</label></th>
  <td><?php print contest_render_state('US', (!empty($usr->state)? $usr->state: '')); ?></td>
</tr>
<tr>
  <th class="contest-profile-label"><label for="contest-zip"><?php print __('Zip'); ?>:</label></th>
  <td><input type="text" name="contest_zip" value="<?php print !empty($usr->zip)? $usr->zip: ''; ?>" size="30" maxlength="<?php print CONTEST_ZIP_MAX; ?>" class="regular-text" id="contest-zip" pattern="^\s*\d+\s*$" /></td>
</tr>
<tr>
  <th class="contest-profile-label"><label for="contest-phone"><?php print __('Phone'); ?>:</label></th>
  <td><input type="text" name="contest_phone" value="<?php print !empty($usr->phone)? $usr->phone: ''; ?>" size="30" maxlength="<?php print CONTEST_PHONE_MAX; ?>" class="regular-text" id="contest-phone" /></td>
</tr>
<tr>
  <th class="contest-profile-label"><label for="contest-birthdate"><?php print __('Birthday'); ?>:</label></th>
  <td><div id="contest-birthdate"><?php contest_render_date_select('birthdate', (!empty($usr->birthdate)? $usr->birthdate: '')); ?></div></td>
</tr>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

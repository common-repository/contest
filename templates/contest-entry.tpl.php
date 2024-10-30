<?php

/**
 * @file contest-entry.tpl.php
 * The template file to display the contest entry form.
 *
 * Available variables:
 * - $contest (object) A contest object with the following properties:
 * - - cid (int)
 * - - start (int)
 * - - end (int)
 * - - entrants (int)
 * - - entries (int)
 * - - period (int)
 * - - places (int)
 * - - publish_winners (int)
 * - - host (object)
 * - - sponsor (object)
 * - - usr (object)
 * - - results (array)
 * - - winners (array)
 * - - tnc (string)
 * - - post (object)
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
<form action="/contest-entry-trans" method="post" id="contest-entry" novalidate="novalidate">
  <?php wp_nonce_field('contest_entry', 'contest_entry_nonce'); ?>
  <input type="hidden" name="contest_entry_cid" value="<?php print $contest->cid; ?>" />

<?php if (!empty($usr->uid)): ?>
  <input type="hidden" name="user_email" value="<?php print $usr->email; ?>" />
<?php endif; ?>

  <fieldset class="contest-entry<?php print !empty($usr->uid)? '': ' active'; ?>" name="contest-entry">
    <legend><a href="#"><?php print __('Entry Form'); ?></a></legend>
    <table border="0" cellpadding="0" cellspacing="0" class="contest-form-table form-table">
      
    <?php if (empty($usr->uid)): ?>
      <tr>
        <th class="contest-profile-label"><label for="user-email"><?php print __('Email'); ?>:</label></th>
        <td><input type="text" name="user_email" value="<?php print $usr->email; ?>" size="60" maxlength="100" class="regular-text" id="user-email" pattern="^\s*[\w\-\.]+@[\w\-\.]+\.\w+\s*$" /></td>
      </tr>
    <?php endif; ?>
    
      <tr>
        <th class="contest-profile-label"><label for="contest-first-name"><?php print __('First Name'); ?>:</label></th>
        <td><input type="text" name="first_name" value="<?php print $usr->name_f; ?>" size="60" maxlength="50" class="regular-text" id="first-name" pattern="^\s*[\s\w\-\.]+\s*$" /></td>
      </tr>
      <tr>
        <th class="contest-profile-label"><label for="contest-last-name"><?php print __('Last Name'); ?>:</label></th>
        <td><input type="text" name="last_name" value="<?php print $usr->name_l; ?>" size="60" maxlength="50" class="regular-text" id="last-name" pattern="^\s*[\s\w\-\.]+\s*$" /></td>
      </tr>
      <?php require contest_theme(CONTEST_DIR . '/templates/contest-fields.tpl.php'); ?>
    </table>
  </fieldset>
  <?php require contest_theme(CONTEST_DIR . '/templates/contest-field-optin.tpl.php'); ?>
  <input type="submit" name="submit" value="<?php print __('Enter Contest'); ?>" class="regular-text" id="contest-submit" />
</form>
<script type="text/javascript">
  jQuery('.contest-entry legend').click(function() {
    jQuery('fieldset.contest-entry').toggleClass('active');
  });
</script>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

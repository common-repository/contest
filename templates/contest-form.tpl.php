<?php

/**
 * @file contest-form.tpl.php
 * The template for the contest creation form.
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
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<table border="0" cellpadding="3" cellspacing="1">
  <tr>
    <td class="contest-label"><?php print __('Contest Sponsor'); ?>:</td>
    <td class="contest-field">
      <input type="text" name="sponsor_name" value="<?php print $contest->sponsor->name; ?>" autocomplete="off" size="60" maxlength="50" id="edit-sponsor" class="form-text" />
      <script type="text/javascript">
        jQuery(function($) {
          $("#edit-sponsor").suggest(ajaxurl + "?action=contest_ajax_name", {delay:100, minchars:2});
        });
      </script>
    </td>
  </tr>
  <tr>
    <td class="contest-label"><?php print __('Winning Places'); ?>:</td>
    <td class="contest-field">
      <select name="places" id="edit-places" class="form-select required">

      <?php foreach ((array('' => '-' . __('Select One') . '-') + array_combine(range(1, 10), range(1, 10))) as $value => $label): ?>
        <option value="<?php print $value; ?>"<?php selected($contest->places, $value); ?>><?php print $label; ?></option>
      <?php endforeach; ?>

      </select>
    </td>
  </tr>
  <tr>
    <td class="contest-label">User Can Enter:</td>
    <td class="contest-field">
      <select name="period" id="edit-period" class="form-select required">

      <?php foreach ((array('' => '-' . __('Select One') . '-') + _contest_get_entry_periods()) as $value => $label): ?>
        <option value="<?php print $value; ?>"<?php selected($contest->period, $value); ?>><?php print __(ucfirst($label)); ?></option>
      <?php endforeach; ?>

      </select>
    </td>
  </tr>
  <tr>
    <td class="contest-label">Start:</td>
    <td class="contest-field"><?php contest_render_date_select('start', $contest->start); ?></td>
  </tr>
  <tr>
    <td class="contest-label">End:</td>
    <td class="contest-field"><?php contest_render_date_select('end', $contest->end); ?></td>
  </tr>
</table>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

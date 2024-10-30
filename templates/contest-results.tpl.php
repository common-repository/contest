<?php

/**
 * @file contest-results.tpl.php
 * Template for a contest's results on the view page.
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
$index = 0;
$states = _contest_get_states('US');
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="contest-results">

<?php if (!empty($contest->results)): ?>
  <table border="0" cellpadding="0" cellspacing="0" class="contest-page-winners">
    <caption><?php print __('Contest Results'); ?></caption>
    <thead>
      <tr>
        <th width="20%"><?php print __('Place'); ?></th>
        <th width="80%"><?php print __('Winner'); ?></th>
      </tr>
    </thead>
    <tbody>

    <?php foreach ($contest->results as $place => $usr): ?>
      <?php $index++; ?>
      <tr class="<?php print ($index % 2)? 'odd': 'even'; ?>">
        <td width="20%"><?php print $place; ?>.</td>
        <td width="80%">

        <?php if (!empty($usr->state)): ?>
          <?php print !empty($states[$usr->state])? "$usr->full_name of {$states[$usr->state]}": "$usr->full_name of $usr->state"; ?>
        <?php else: ?>
          <?php print $usr->full_name; ?>
        <?php endif; ?>

       </td>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>
<?php endif; ?>

</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

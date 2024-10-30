<?php

/**
 * @file contest-admin.tpl.php
 * Template for a contest's admin page.
 *
 * Available variables:
 * - $data (object)
 * - - contest (object)
 * - - - start (int)
 * - - - end (int)
 * - - - places (int)
 * - - - period (int)
 * - - - publish_winners (bool)
 * - - - entrants (int)
 * - - - entries (int)
 * - - contestants (array of objects) uid => contestant object
 * - - - uid (int)
 * - - - name (string)
 * - - - mail (string)
 * - - - qty (int)
 * - - - winner (bool)
 * - - host (object)
 * - - - uid (int)
 * - - - name (string)
 * - - - mail (string)
 * - - - title (string)
 * - - - full_name (string)
 * - - - business (string)
 * - - - address (string)
 * - - - city (string)
 * - - - state (string)
 * - - - zip (string)
 * - - - phone (string)
 * - - - birthdate (int)
 * - - sponsor (object)
 * - - - uid (int)
 * - - - name (string)
 * - - - mail (string)
 * - - - url (string)
 * - - - full_name (string)
 * - - - business (string)
 * - - - address (string)
 * - - - city (string)
 * - - - state (string)
 * - - - zip (string)
 * - - - phone (string)
 * - - - birthdate (int)
 * - - winners (array) uid => place
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="contest-admin">
  <div class="contest-admin-edit">
    <span class="edit-link"><a href="<?php print get_permalink($contest->cid); ?>"><?php print __('View'); ?></a></span>
    <span class="edit-link"><a href="<?php print admin_url(); ?>post.php?action=edit&post=<?php print $contest->cid; ?>"><?php print __('Edit'); ?></a></span>
  </div>

<!-- Some host details. --->

  <fieldset class="contest-admin-host"><?php get_author_posts_url($data->host->uid); ?>
    <legend><?php print __('Host'); ?></legend>
    <a href="<?php print get_author_posts_url($data->host->uid); ?>"><?php print $data->host->full_name; ?></a><br />
    <a href="mailto:<?php print $data->host->email; ?>"><?php print $data->host->email; ?></a><br />
    <?php print __('Phone') . ": {$data->host->phone}"; ?><br />
    <?php print __('Address') . ": {$data->host->address}, {$data->host->city} {$data->host->state} {$data->host->zip}"; ?>
  </fieldset>

<!-- Some sponsor details. --->

  <fieldset class="contest-admin-sponsor">
    <legend><?php print __('Sponsor'); ?></legend>
    <a href="<?php print get_author_posts_url($data->sponsor->uid); ?>"><?php print $data->sponsor->full_name; ?></a><br />
    <a href="mailto:<?php print $data->sponsor->email; ?>"><?php print $data->sponsor->email; ?></a><br />
    <a href="<?php print esc_url($data->sponsor->url); ?>"><?php print preg_replace('/^https?:\/\//', '', esc_url($data->sponsor->url)); ?></a><br />
  </fieldset>

<!-- Some contest details. --->

  <div class="contest-admin-detail">
    <?php print __('Start Date') . ': ' . date('Y-m-d', $data->contest->start); ?><br />
    <?php print __('End Date') . ': ' . date('Y-m-d', $data->contest->end); ?><br />
    <?php print __('Total Entries') . ": {$data->contest->entries}"; ?><br />
    <?php print __('Total Users') . ": {$data->contest->entrants}"; ?><br />
    <?php print __('Places Allowed') . ": {$data->contest->places}"; ?>
  </div>

<!-- The administration actions. --->

<?php if ($data->contest->end < CONTEST_TIME): ?>
  <ul class="contest-admin-actions">

  <?php if (count($data->winners) < $data->contest->places): ?>
    <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=pick_winner&contest_admin_cid={$data->contest->cid}", 'pick_winner', 'pick_winner_nonce'); ?>"><?php print __('Pick Random Winner'); ?></a></li>
  <?php else: ?>
    <li class="inactive"><?php print __('Pick Random Winner'); ?></li>
  <?php endif; ?>

  <?php if (count($data->winners) == $data->contest->places && $data->contest->publish_winners): ?>
    <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=unpublish_winners&contest_admin_cid={$data->contest->cid}", 'unpublish_winners', 'unpublish_winners_nonce'); ?>"><?php print __('Unpublish Winners'); ?></a></li>
  <?php elseif (count($data->winners) == $data->contest->places): ?>
   <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=publish_winners&contest_admin_cid={$data->contest->cid}", 'publish_winners', 'publish_winners_nonce'); ?>"><?php print __('Publish Winners'); ?></a></li>
  <?php else: ?>
    <li class="inactive"><?php print __('Publish Winners'); ?></li>
  <?php endif; ?>

  <?php if (count($data->winners) && !$data->contest->publish_winners): ?>
    <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=clear_winners&contest_admin_cid={$data->contest->cid}", 'clear_winners', 'clear_winners_nonce'); ?>"><?php print __('Clear All Winners'); ?></a></li>
  <?php else: ?>
    <li class="inactive"><?php print __('Clear All Winners'); ?></li>
  <?php endif; ?>

    <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=export_entries&contest_admin_cid={$data->contest->cid}", 'export_entries', 'export_entries_nonce'); ?>"><?php print __('Export Entries'); ?></a></li>
    <li><a href="<?php print wp_nonce_url("/contest-admin-trans?op=export_unique&contest_admin_cid={$data->contest->cid}", 'export_unique', 'export_unique_nonce'); ?>"><?php print __('Export Unique Users'); ?></a></li>
  </ul>
<?php else: ?>
  <ul class="contest-admin-actions">
    <li class="inactive"><?php print __('Pick Random Winner'); ?></li>
    <li class="inactive"><?php print __('Publish Winners'); ?></li>
    <li class="inactive"><?php print __('Clear All Winners'); ?></li>
    <li class="inactive"><?php print __('Export Entries'); ?></li>
    <li class="inactive"><?php print __('Export Unique Users'); ?></li>
  </ul>
<?php endif; ?>


<!-- The contest winners. --->

<?php if (!empty($data->winners)): ?>
  <table border="0" cellpadding="10" cellspacing="1" class="contest-admin-winners<?php print $data->contest->publish_winners? ' published-winners': ''; ?>">
    <caption><?php print __('Contest Winners'); ?></caption>
    <thead>
      <tr>
        <th><?php print __('Place'); ?></th>
        <th><?php print __('Name'); ?></th>
        <th><?php print __('Email'); ?></th>
        <th><?php print __('Operation'); ?></th>
      </tr>
    </thead>
    <tbody>

    <?php $index = 0; ?>
    <?php foreach ($data->winners as $uid => $place): ?>
      <?php if (empty($data->contestants[$uid])): ?>
        <?php continue; ?>
      <?php else: ?>
        <?php $usr = $data->contestants[$uid]; ?>
      <?php endif; ?>

      <?php $index++; ?>
      <tr class="<?php print ($index % 2)? 'odd': 'even'; ?>">
        <td><?php print $index; ?>.</td>
        <td><a href="<?php print get_author_posts_url($usr->uid); ?>"><?php print $usr->name; ?></a></td>
        <td><a href="mailto:<?php print $usr->email; ?>"><?php print $usr->email; ?></a></td>
        <td class="center actions"><a href="<?php print wp_nonce_url("/contest-admin-trans?op=clear_winners&contest_admin_cid={$data->contest->cid}&uid={$usr->uid}", 'clear_winners', 'clear_winners_nonce'); ?>"><?php print __('Clear'); ?></a></td>
      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>
<?php endif; ?>


<!-- The contest contestants. --->

  <table border="0" cellpadding="10" cellspacing="1" class="contest-admin-contestants<?php print $data->contest->publish_winners? ' published-winners': ''; ?>">
    <caption><?php print __('Contest Entrants'); ?></caption>
    <thead>
      <tr>
        <th><?php print __('Name'); ?></th>
        <th><?php print __('Email'); ?></th>
        <th><?php print __('Count'); ?></th>
        <th><?php print __('Operation'); ?></th>
      </tr>
    </thead>
    <tbody>

    <?php $index = 0; ?>
    <?php foreach ($data->contestants as $usr): ?>
      <?php $index++; ?>

      <?php if (($index % 50) === 0): ?>

<!-- Print the header every 50 rows. --->

      <tr>
        <th><?php print __('Name'); ?></th>
        <th><?php print __('Email'); ?></th>
        <th><?php print __('Count'); ?></th>
        <th><?php print __('Operation'); ?></th>
      </tr>
      <?php endif; ?>

      <tr class="<?php print ($index % 2)? 'odd': 'even'; ?><?php print !empty($usr->winner)? ' winner': ''; ?>">
        <td><a href="<?php print get_author_posts_url($usr->uid); ?>"><?php print $usr->name; ?></a></td>
        <td><a href="mailto:<?php print $usr->email; ?>"><?php print $usr->email; ?></a></td>
        <td class="center"><?php print $usr->qty; ?></td>

      <?php if ($data->contest->end < CONTEST_TIME): ?>
        <td class="center actions">

        <?php if ($usr->winner): ?>
          <a href="<?php print wp_nonce_url("/contest-admin-trans?op=clear_winners&contest_admin_cid={$data->contest->cid}&uid={$usr->uid}", 'clear_winners', 'clear_winners_nonce'); ?>"><?php print __('Clear'); ?></a>
        <?php else: ?>
          <a href="<?php print wp_nonce_url("/contest-admin-trans?op=pick_winner&contest_admin_cid={$data->contest->cid}&uid={$usr->uid}", 'pick_winner', 'pick_winner_nonce'); ?>"><?php print __('Pick'); ?></a>
        <?php endif; ?>

        </td>
      <?php else: ?>
        <td class="center">&mdash;</td>
      <?php endif; ?>

      </tr>
    <?php endforeach; ?>

    </tbody>
  </table>

<?php if (!empty($debug)): ?>
  <pre>
    <?php print_r($data); ?>
  </pre>
<?php endif; ?>

</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

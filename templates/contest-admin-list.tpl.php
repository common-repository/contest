<?php

/**
 * @file contest-admin.tpl.php
 * Template for a contest's admin page.
 *
 * Available variables:
 * - $data: (object) An object with the data do display the contest list page.
 * - - $contests: (array) An array of objects with the following properties.
 * - - - title (string)
 * - - - cid (int)
 * - - - start (int)
 * - - - end (int)
 * - - - places (int)
 * - - - period (int)
 * - - - publish_winners (bool)
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<h1>Contest List</h1>
<a href="<?php print admin_url(); ?>post-new.php?post_type=contest">Add Contest</a>
<table border="0" cellpadding="10" cellspacing="1" class="contest-admin-list">
  <thead>
    <tr>
      <th><?php print __('Title'); ?></th>
      <th><?php print __('Start'); ?></th>
      <th><?php print __('End'); ?></th>
      <th><?php print __('Results'); ?></th>
      <th colspan="3"><?php print __('Actions'); ?></th>
    </tr>
  </thead>
  <tbody>

  <?php foreach ($data->contests as $i => $obj): ?>
    <tr class="<?php print ($i % 2)? 'odd': 'even'; ?>">
      <th><a href="<?php print get_permalink($obj->cid); ?>"><?php print esc_html($obj->title); ?></a></th>
      <td><?php print date('Y-m-d', $obj->start); ?></td>
      <td><?php print date('Y-m-d', $obj->end); ?></td>
      <td><?php print $obj->publish_winners? 'Published': 'Not Published'; ?></td>
      <td><a href="<?php print get_permalink($obj->cid); ?>"><?php print __('View'); ?></a></td>
      <td><span class="edit-link"><a href="<?php print admin_url(); ?>post.php?action=edit&post=<?php print $obj->cid; ?>"><?php print __('Edit'); ?></a></span></td>
      <td><span class="edit-link"><a href="<?php print admin_url(); ?>admin.php?page=contest-admin&cid=<?php print $obj->cid; ?>"><?php print __('Admin'); ?></a></span></td>
    </tr>
  <?php endforeach; ?>

  </tbody>
</table>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

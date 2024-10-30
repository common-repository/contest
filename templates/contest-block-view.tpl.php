<?php

/**
 * @file contest-block-view.tpl.php
 * The template file to display the contest list block.
 *
 * Available variables:
 * - $block (object) An object with the following properties:
 * - - title (string)
 * - - max (int)
 * - $format (object) An object with the following properties:
 * - - name (string)
 * - - id (string)
 * - - description (string)
 * - - class (string)
 * - - before_widget (string)
 * - - after_widget (string)
 * - - before_title (string)
 * - - after_title (string)
 * - - widget_id (string)
 * - - widget_name (string)
 * - $rows (array) An array of objects with the following properties:
 * - - title (string)
 * - - cid (int)
 * - - start (int)
 * - - end (int)
 * - - published (int)
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<?php print !empty($format->before_widget)? $format->before_widget: ''; ?>

<?php if (!empty($block->title)): ?>
  <?php print (!empty($format->before_title)? $format->before_title: '') . apply_filters('widget_title', $block->title) . (!empty($format->after_title)? $format->after_title: ''); ?>
<?php endif; ?>

  <ul class="contest-block-list">

  <?php for ($i = 0; $i < $block->max && !empty($rows[$i]); $i++): ?>
    <li>
      <a href="<?php print get_permalink($rows[$i]->cid); ?>">
        <?php print !empty($rows[$i]->title)? esc_html($rows[$i]->title): ''; ?><br />

        <?php if (!empty($rows[$i]->published)): ?>
          <?php print __('Results'); ?><span class="raquo">&raquo;</span>
        <?php elseif (isset($rows[$i]->start) && $rows[$i]->start < CONTEST_TIME && isset($rows[$i]->end) && CONTEST_TIME < $rows[$i]->end): ?>
          <?php print __('Enter'); ?><span class="raquo">&raquo;</span>
        <?php else: ?>
          <?php print __('View'); ?><span class="raquo">&raquo;</span>
        <?php endif; ?>

      </a>
    </li>
  <?php endfor; ?>

  </ul>
  <a href="/index.php?post_type=contest"><?php print __('View All'); ?><span class="raquo">&raquo;</span></a>
<?php print !empty($format->after_widget)? $format->after_widget: ''; ?>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

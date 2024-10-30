<?php

/**
 * @file contest-single.tpl.php
 * The template for displaying a single contest.
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
$debug = FALSE;
$contest = _contest_get_page_data();
$usr = $contest->usr;
the_post();
$tmp = '';
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<?php get_header(); ?>
<div id="primary" class="content-area">
  <main id="main" class="site-main" role="main">
    <article id="post-<?php print $contest->cid; ?>" class="contest type-contest status-<?php print $contest->post->post_status; ?> hentry">
      <?php print contest_get_msg(); ?>

    <?php if (!empty($contest->post->post_title)): ?>
      <header class="entry-header">
        <h1 class="entry-title"><?php print esc_html($contest->post->post_title); ?></h1>
      </header>
    <?php endif; ?>

      <?php twentyfifteen_post_thumbnail();  ?>
      <div class="entry-content">
      	<div class="contest-details">
      		<span class="contest-detail-label"><?php print __('Start'); ?>:</span><?php print date('F j, Y', $contest->start); ?><br />
      		<span class="contest-detail-label"><?php print __('End'); ?>:</span><?php print date('F j, Y', $contest->end); ?><br />
      		<span class="contest-detail-label"><?php print __('You can enter'); ?>:</span><?php print (!empty($contest->periods[$contest->period])? ucwords($contest->periods[$contest->period]): __('Regularly')); ?>
        </div>
        <?php the_content(); ?>

      <?php if ($contest->start < CONTEST_TIME &&  CONTEST_TIME < $contest->end): ?>
        <?php require contest_theme(CONTEST_DIR . '/templates/contest-entry.tpl.php'); ?>
      <?php elseif ($contest->publish_winners): ?>
        <?php require contest_theme(CONTEST_DIR . '/templates/contest-results.tpl.php'); ?>
      <?php endif; ?>

        <?php require contest_theme(CONTEST_DIR . '/templates/contest-tnc.tpl.php'); ?>
      </div>

    <?php if (current_user_can('edit_others_posts')): ?>
      <footer class="entry-footer">
        <span class="edit-link"><a href="<?php print admin_url(); ?>post.php?action=edit&post=<?php print $contest->cid; ?>"><?php print __('Edit'); ?></a></span>
        <span class="edit-link"><a href="<?php print admin_url(); ?>admin.php?page=contest-admin&cid=<?php print $contest->cid; ?>"><?php print __('Admin'); ?></a></span>
      </footer>
    <?php endif; ?>

    </article>
  </main>
  <?php print $debug? '<pre>' . print_r($tmp, TRUE) . '</pre>': '' ?>
</div>
<?php get_footer(); ?>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

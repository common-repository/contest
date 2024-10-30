<?php

/**
 * @file contest-archive.tpl.php
 * The template file to display the contest list page.
 */
$query = new WP_Query('post_type=contest');

print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<?php get_header(); ?>
  <section id="primary" class="content-area contest-archive">
    <main id="main" class="site-main" role="main">
      <header class="page-header">
        <h1 class="page-title"><?php print __('Contest List'); ?></h1>
      </header>

    <?php while ($query->have_posts()): ?>
      <?php $query->the_post(); ?>
      <div class="content">
        <h2><a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>

      <?php if (has_post_thumbnail()): ?>
        <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
      <?php endif; ?>

        <?php the_excerpt(); ?>

        <a href="<?php the_permalink() ?>">

        <?php if (contest_is_published(get_the_id())): ?>
          <?php print __('Results'); ?><span class="raquo">&raquo;</span>
        <?php elseif (contest_is_running(get_the_id())): ?>
          <?php print __('Enter'); ?><span class="raquo">&raquo;</span>
        <?php else: ?>
          <?php print __('View'); ?><span class="raquo">&raquo;</span>
        <?php endif; ?>

        </a>
      </div>
    <?php endwhile; ?>

    </main>
  </section>
<?php get_footer(); ?>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

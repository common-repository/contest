<?php

/**
 * @file ContestBlock.php
 * Contains \contest\ContestBlock.
 */

/**
 * Extends WP_Widget to build a contest block.
 */
class ContestBlock extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  public function __construct() {
    $options = array(
      'classname'   => 'contestblock',
      'description' => 'Contest block description.',
    );
    parent::__construct('contest', 'Contests', $options);
  }
  /**
   * Back-end widget form.
   *
   * @param $vars (array) Previously saved values from database.
   */
  public function form($vars) {
    $max = !empty($vars['max'])? $vars['max']: 3;
    $max_options = array(CONTEST_INT_MAX => '-' . __('All') . '-') + array_combine(range(1, 10), range(1, 10));
    $show = !empty($vars['show'])? esc_attr($vars['show']): 'running';
    $show_options = array(
      'all'     => __('All'),
      'running' => __('Running'),
    );
    $title = !empty($vars['title'])? esc_attr($vars['title']): __('Contest List');
    require contest_theme(CONTEST_DIR . '/templates/contest-block-form.tpl.php');
  }
  /**
   * Sanitize widget form values as they are saved.
   *
   * @param $new_instance (array) Values just sent to be saved.
   * @param $old_instance (array) Previously saved values from database.
   *
   * @return Updated (array) safe values to be saved.
   */
  public function update($new_instance, $old_instance) {
    return array(
      'max'   => !empty($new_instance['max'])? strip_tags($new_instance['max']): '',
      'show'  => !empty($new_instance['show'])? strip_tags($new_instance['show']): '',
      'title' => !empty($new_instance['title'])? strip_tags($new_instance['title']): '',
    );
  }
  /**
   * Front-end widget display.
   *
   * @param $format (array) Widget arguments.
   * @param $block (array) Saved values from database.
   */
  public function widget($format, $block) {
    global $wpdb;
    $block = (object) $block;
    $format = (object) $format;

    $stmt = "
      SELECT
        c.cid,
        p.post_title AS 'title',
        c.publish_winners AS 'published',
        c.start,
        c.end
      FROM
        {$wpdb->prefix}contest c
        JOIN {$wpdb->prefix}posts p ON p.ID = c.cid " .
        (($block->show != 'all')? 'WHERE c.end >= %d AND c.start <= %d': '') . "
      ORDER BY
        c.end DESC,
        p.post_title ASC
    ";
    $rows = ($block->show != 'all')? $wpdb->get_results($wpdb->prepare($stmt, CONTEST_TIME, CONTEST_TIME)): $wpdb->get_results($stmt);

    if (!count($rows)) {
      return;
    }
    require contest_theme(CONTEST_DIR . '/templates/contest-block-view.tpl.php');
  }
}

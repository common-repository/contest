<?php

/**
 * @file The contest plugin.
 *
 * @package contest
 * @version 1.1
 *
 * Plugin Name: Contest
 * Plugin URI: http://wordpress.org/plugins/contest/
 * Description: Allows your site to host a sweepstakes, (contestants win prizes via a random drawing).
 * Author: bkelly.
 * Version: 1.1
 * Author URI: http://www.highspeedfun.com/
 */
require 'ContestBlock.php';
require 'ContestUser.php';

const CONTEST_ADDRESS_MAX = 100;
const CONTEST_CITY_MAX = 50;
const CONTEST_EMAIL_MAX = 100;
const CONTEST_NAME_MAX = 25;
const CONTEST_PHONE_MAX = 20;
const CONTEST_STATE_MAX = 50;
const CONTEST_ZIP_MAX = 5;

const CONTEST_STRING_MAX = 255;
const CONTEST_INT_MAX = 2147483647;

const CONTEST_VERSION = '1.1';
const CONTEST_MINIMUM_WP_VERSION = '4.3';

define('CONTEST_DIR', preg_replace('/\/+$/', '', plugin_dir_path(__FILE__)));
define('CONTEST_TIME', (int) $_SERVER['REQUEST_TIME']);
define('CONTEST_URL', plugins_url() . '/contest');

register_activation_hook(__FILE__, '_contest_register_activation');
register_deactivation_hook(__FILE__, '_contest_register_deactivate');
register_uninstall_hook (__FILE__, '_contest_register_uninstall');

add_action('admin_head', 'contest_head', 11);
add_action('admin_init', 'contest_settings_init');
add_action('admin_menu', 'contest_admin_menu');
add_action('admin_notices', 'contest_get_admin_msg');
add_action('delete_post', 'contest_delete');
add_action('edit_user_profile', 'contest_profile_form');
add_action('edit_user_profile_update', 'contest_profile_form_submit');
add_action('init', 'contest_content_type', 0);
add_action('init', 'contest_request_router');
add_action('load-post.php', 'contest_fields_init');
add_action('load-post-new.php', 'contest_fields_init');
add_action('parse_request', 'contest_request_include');
add_action('personal_options_update', 'contest_profile_form_submit');
add_action('show_user_profile', 'contest_profile_form');
add_action('widgets_init', 'contest_block');
add_action('wp_ajax_contest_ajax_name', 'contest_ajax_name');
add_action('wp_enqueue_scripts', 'contest_head', 11);

add_filter('archive_template', 'contest_theme_archive');
add_filter('query_vars', 'contest_request_vars');
add_filter('single_template', 'contest_theme_single');
add_filter('template_include', 'contest_theme', 99);

/**
 * Implementation of action: admin_menu.
 */
function contest_admin_menu() {
  add_menu_page('Contest List', 'Contest List', 'manage_options', 'contest-admin-list', 'contest_admin_list_page');
  add_submenu_page('contest-admin-list', 'Contest Settings', 'Settings', 'manage_options', 'contest-settings', 'contest_settings_page');
  add_submenu_page('', 'Contest Administration', '', 'manage_options', 'contest-admin', 'contest_admin_page');
}
/**
 * The page callback for the admin list page.
 */
function contest_admin_list_page() {
  global $wpdb;

  $stmt = "
    SELECT
      p.post_title as 'title',
      c.*
    FROM
      {$wpdb->prefix}contest c
      JOIN {$wpdb->prefix}posts p ON p.ID = c.cid
    WHERE
      p.post_type = 'contest'
    ORDER BY
      c.cid DESC,
      p.post_title ASC
  ";
  $data = (object) array('contests' => $wpdb->get_results($stmt));

  require contest_theme(CONTEST_DIR . '/templates/contest-admin-list.tpl.php');
}
/**
 * The page callback for a contest's admin page.
 */
function contest_admin_page() {
  global $wpdb;
  $debug = FALSE; // Template debug flag.
  $cid = (!empty($_GET['cid']) && is_numeric($_GET['cid']))? (int) $_GET['cid']: 0;
  $settings = (object) get_site_option('contest_settings');
  $winners = array();

  $contestants = _contest_get_contestants($cid);

  $contest = contest_load($cid);
  $contest->entrants = count($contestants);
  $contest->entries = $wpdb->get_var($wpdb->prepare("SELECT COUNT(uid) FROM {$wpdb->prefix}contest_entry WHERE cid = %d", $cid));

  $data = (object) array(
    'contest'     => $contest,
    'contestants' => $contestants,
    'host'        => $contest->host,
    'sponsor'     => $contest->sponsor,
    'winners'     => _contest_get_winners($cid),
  );
  require contest_theme(CONTEST_DIR . '/templates/contest-admin.tpl.php');
}
/**
 * Ajax callback for the user name auto-complete field.
 */
function contest_ajax_name() {
  global $wpdb;
  $search = preg_replace(array('/\W/', '/_/'), array('', '\_'), $_REQUEST['q']) . '%';

  $rows = $wpdb->get_results($wpdb->prepare("SELECT ID AS 'uid', user_login AS 'name' FROM {$wpdb->prefix}users WHERE user_login LIKE %s", $search));

  foreach ($rows as $row) {
    print "$row->uid:$row->name\n";
  }
  exit();
}
/**
 * Implementation of action: widgets_init
 */
function contest_block() {
  register_widget('ContestBlock');
}
/**
 * Implementation of action: init
 * Register and set other options for the contest content type.
 */
function contest_content_type() {
  $args = array(
    'label'               => __('Contest'),
    'description'         => __('A random game of chance.'),
    'taxonomies'          => array(),
    'hierarchical'        => FALSE,
    'public'              => TRUE,
    'show_ui'             => TRUE,
    'show_in_menu'        => TRUE,
    'show_in_nav_menus'   => TRUE,
    'show_in_admin_bar'   => TRUE,
    'menu_position'       => 5,
    'can_export'          => TRUE,
    'has_archive'         => TRUE,
    'exclude_from_search' => FALSE,
    'publicly_queryable'  => TRUE,
    'capability_type'     => 'page',
    'rewrite'             => 'contest',
    'labels'              => array(
      'name'               => __('Contest'),
      'singular_name'      => __('Contest'),
      'menu_name'          => __('Contests'),
      'parent_item_colon'  => __('Parent Contest'),
      'all_items'          => __('All Contests'),
      'view_item'          => __('View Contest'),
      'add_new_item'       => __('Add New Contest'),
      'add_new'            => __('Add Contest'),
      'edit_item'          => __('Edit Contest'),
      'update_item'        => __('Update Contest'),
      'search_items'       => __('Search Contest'),
      'not_found'          => __('Contest not found'),
      'not_found_in_trash' => __('Contest not found in Trash'),
    ),
    'supports' => array(
      'title',
      'editor',
      'excerpt',
      'thumbnail',
    ),
  );
  register_post_type('contest', $args);
}
/**
 * Implementation of action: delete_post.
 * Delete the contest from the contest table and all the entries from the contest_entry table.
 */
function contest_delete($id = 0) {
  global $wpdb;
  $post = get_post($id);

  if ($post->post_type != 'contest') {
    return;
  }
  $wpdb->delete('contest', array('cid' => $id));
  $wpdb->delete('contest_entry', array('cid' => $id));
}
/**
 * Enter the user into the contest. If user doesn't exist create them.
 */
function contest_entry($cid = 0) {
  global $wpdb;
  $periods = _contest_get_entry_periods();
  $settings = (object) get_site_option('contest_settings');
  $usr = new \contest\ContestUser(get_current_user_id());

  $uid = $usr->savePost($_POST);

  if ($usr->uid != $uid) {
    $usr = new \contest\ContestUser($uid);
  }
  if (!$usr->profileComplete()) {
    contest_set_msg(__('You already have an account with an incomplete profile. The easiest way to enter the contest is to log in and come back and enter the contest.'));
    contest_set_msg(__('Once your profile is complete you won\'t have to do this again.'));
    contest_set_msg(__('If you have problems logging in click the "Request new password" link and a login link will be sent to your email.'));
    return FALSE;
  }
  if (CONTEST_TIME - $usr->birthdate < $settings->min_age * YEAR_IN_SECONDS) {
    contest_set_msg(sprintf(__('You must be at least %d years old to enter the contest.'), $settings->min_age));
    return FALSE;
  }
  $period = $wpdb->get_var($wpdb->prepare("SELECT period FROM {$wpdb->prefix}contest WHERE cid = %d AND start < %d AND %d < end", $cid, CONTEST_TIME, CONTEST_TIME));

  if (!$period) {
    contest_set_msg(__('This contest is closed.'), 'error');
    return FALSE;
  }
  $entered = _contest_get_entered($usr->uid, $cid, $period);

  if ($entered) {
    contest_set_msg(sprintf(__('You can enter the contest %s. We already have an entry for you during this period.'), (!empty($periods[$period])? $periods[$period]: __('regularly'))), 'warning');
    return FALSE;
  }
  if ($usr->uid) {
    $fields = array(
      'cid'     => $cid,
      'uid'     => $usr->uid,
      'created' => CONTEST_TIME,
      'ip'      => _contest_get_user_ip(),
    );
    $wpdb->insert('contest_entry', $fields, array('%d', '%d', '%d', '%s'));

    contest_set_msg(__('You have been entered into the contest.'));
  }
}
/**
 * Implementation of actions: load-post.php, load-post-new.php.
 * Add the meta box actions during setup.
 */
function contest_fields_init() {
  add_action('add_meta_boxes', 'contest_form_alter');
  add_action('save_post', 'contest_form_submit', 10, 2);
}
/**
 * Implementation of action: add_meta_boxes.
 * Place the meta entry box.
 */
function contest_form_alter() {
  add_meta_box('contest_fields', __('Contest Fields'), 'contest_form_fields', 'contest', 'normal', 'default');
}
/**
 * Callback for add_meta_box().
 * Build the meta entry box.
 *
 * @param $object (object) The post object.
 * @param $meta (array) Callback meta data.
 *
 * @return (string) The HTML for the meta box.
 */
function contest_form_fields($post, $meta) {
  global $wpdb;
  wp_nonce_field(basename(__FILE__), $meta['id']);
  $contest = contest_load($post->ID);
  require contest_theme(CONTEST_DIR . '/templates/contest-form.tpl.php');
}
/**
 * Implementation of action: save_post.
 * Create and update contests.
 */
function contest_form_submit() {
  global $wpdb;
  $error = FALSE;

  if (empty($_POST['post_type']) || $_POST['post_type'] != 'contest' || empty($_POST['ID']) || !is_numeric($_POST['ID'])) {
    return;
  }
  $cid = (!empty($_POST['ID']) && is_numeric($_POST['ID']))? (int) $_POST['ID']: NULL;
  $start = (!empty($_POST['start']) && count($_POST['start']) == 3)? mktime(0, 0, 0, (int) $_POST['start']['month'], (int) $_POST['start']['day'], (int) $_POST['start']['year']): '';
  $end = (!empty($_POST['end']) && count($_POST['end']) == 3)? mktime(0, 0, 0, (int) $_POST['end']['month'], (int) $_POST['end']['day'], (int) $_POST['end']['year']): '';
  $places = (!empty($_POST['places']) && is_numeric($_POST['places']))? (int) $_POST['places']: 0;
  $period = (!empty($_POST['period']) && is_numeric($_POST['period']))? (int) $_POST['period']: 0;

  if (!empty($_POST['sponsor_name']) && preg_match('/^(\d+):\w+$/', $_POST['sponsor_name'], $m)) {
    $sponsor = new \contest\ContestUser($m[1]);
  }
  elseif (!empty($_POST['sponsor_name']) && preg_match('/^(\w+)$/', $_POST['sponsor_name'], $m)) {
    $user = get_user_by('login', $m[1]);
    $sponsor = new \contest\ContestUser($user->ID);
  }
  else {
    $sponsor = new \contest\ContestUser(0);
  }
  if (!$sponsor->uid) {
    contest_set_msg(__('Please select a sponsor with a complete profile for the contest.'), 'error');
    $error = TRUE;
  }
  if (!$sponsor->profileComplete()) {
    contest_set_msg(__('The contest sponsor must have a complete profile.'), 'error');
    $error = TRUE;
  }
  if (!$period) {
    contest_set_msg(__('Please select the period between entries.'), 'error');
    $error = TRUE;
  }
  if (!$places) {
    contest_set_msg(__('Please select the number of winning places.'), 'error');
    $error = TRUE;
  }
  if (!$start || !$end) {
    contest_set_msg(__('The contest must have both a start and end dates.'), 'error');
    $error = TRUE;
  }
  if ($end - $start < DAY_IN_SECONDS) {
    contest_set_msg(__('The contest must run for at least one day.'), 'error');
    $error = TRUE;
  }
  if ($error) {
    return;
  }
  $action = $wpdb->query($wpdb->prepare("SELECT 1 FROM {$wpdb->prefix}contest WHERE cid = %d", $cid))? 'update': 'insert';

  if ($action == 'insert') {
    $stmt = "INSERT INTO {$wpdb->prefix}contest (cid, sponsor_id, start, end, places, period) VALUES (%d, %d, %d, %d, %d, %d)";
    $wpdb->query($wpdb->prepare($stmt, $cid, $sponsor->uid, $start, $end, $places, $period));
    contest_set_msg(__('The contest has been created.'));
  }
  else {
    $stmt = "UPDATE {$wpdb->prefix}contest SET sponsor_id = %d, start = %d, end = %d, places = %d, period = %d WHERE cid = %d";
    $wpdb->query($wpdb->prepare($stmt, $sponsor->uid, $start, $end, $places, $period, $cid));
    contest_set_msg(__('The contest has been updated.'));
  }
}
/**
 * Display any contest status messages saved in the session.
 */
function contest_get_admin_msg() {
  $msgs = array();

  for ($i = 0; get_transient("contest_status[$i]"); $i++) {
    $msgs[] = get_transient("contest_status[$i]");
    delete_transient("contest_status[$i]");
  }
  if (!count($msgs)) {
    return '';
  }
  require contest_theme(CONTEST_DIR . '/templates/contest-admin-msg.tpl.php');
}
/**
 * Display any contest status messages saved in the session.
 *
 * @return (string) An HTML list of messages.
 */
function contest_get_msg() {
  $msgs = array();

  for ($i = 0; get_transient("contest_status[$i]"); $i++) {
    $msgs[] = get_transient("contest_status[$i]");
    delete_transient("contest_status[$i]");
  }
  if (!count($msgs)) {
    return '';
  }
  ob_start();
  require contest_theme(CONTEST_DIR . '/templates/contest-msg.tpl.php');
  $buffer = ob_get_contents();
  ob_end_clean();

  return $buffer;
}
/**
 * Implementation of actions: admin_head, wp_enqueue_scripts.
 * Register the plugin style sheet.
 */
function contest_head() {
  global $post;

  wp_register_style('contest-styles', CONTEST_URL . '/css/contest.css');
  wp_enqueue_style('contest-styles');

  if (!empty($post->post_type) && $post->post_type == 'contest') {
    wp_register_script('contest-scripts', CONTEST_URL . '/js/contest.js');
    wp_enqueue_script('contest-scripts');
  }
  elseif (is_admin()) {
    wp_enqueue_script('jquery');
    wp_enqueue_script('suggest');
  }
}
/**
 * Get the publishing status of the contest.
 *
 * @param $cid (int) The contest ID.
 *
 * @return (bool) True if the contest winners are published.
 */
function contest_is_published($cid = 0) {
  global $wpdb;
  return $wpdb->query($wpdb->prepare("SELECT 1 FROM {$wpdb->prefix}contest WHERE cid = %d AND publish_winners > 0", $cid))? TRUE: FALSE;
}
/**
 * Get the open/close status of the contest.
 *
 * @param $cid (int) The contest ID.
 *
 * @return (bool) True if the contest is open for entries.
 */
function contest_is_running($cid = 0) {
  global $wpdb;
  return $wpdb->query($wpdb->prepare("SELECT 1 FROM {$wpdb->prefix}contest WHERE cid = %d AND start < %d AND end > %d", $cid, CONTEST_TIME, CONTEST_TIME))? TRUE: FALSE;
}
/**
 * Load the contest.
 *
 * @param $cid (int) The contest ID.
 * @param $meta (array) Callback meta data.
 *
 * @return (string) The HTML for the meta box.
 */
function contest_load($cid = 0, $context = 'page') {
  global $wpdb;
  $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}contest WHERE cid = %d", $cid));
  $settings = (object) get_site_option('contest_settings');

  $contest = (object) array(
    'cid'             => !empty($row->cid)? $row->cid: '',
    'start'           => !empty($row->start)? $row->start: '',
    'end'             => !empty($row->end)? $row->end: '',
    'entrants'        => '',
    'entries'         => '',
    'period'          => !empty($row->period)? $row->period: '',
    'periods'         => _contest_get_entry_periods(),
    'places'          => !empty($row->places)? $row->places: '',
    'publish_winners' => !empty($row->publish_winners)? TRUE: FALSE,
    'host'            => !empty($settings->host_uid)? new \contest\ContestUser(intval($settings->host_uid)): new \contest\ContestUser(0),
    'sponsor'         => !empty($row->sponsor_id)? new \contest\ContestUser($row->sponsor_id): new \contest\ContestUser(0),
    'usr'             => new \contest\ContestUser(get_current_user_id()),
    'results'         => array(),
    'winners'         => array(),
    'tnc'             => !empty($settings->tnc)? $settings->tnc: '',
    'post'            => new stdClass(),
  );
  return $contest;
}
/**
 * Implementation of actions: edit_user_profile, show_user_profile.
 * Add extra fields to the user profile form.
 *
 * @param $usr (object) A user object.
 */
function contest_profile_form($user) {
  $usr = new \contest\ContestUser($user->ID);
  require contest_theme(CONTEST_DIR . '/templates/contest-profile.tpl.php');
}
/**
 * Implementation of actions: edit_user_profile_update, personal_options_update.
 * Handle the profile submission.
 *
 * @param $uid (int) The user ID.
 */
function contest_profile_form_submit($uid = 0) {
  $usr = new \contest\ContestUser($uid);
  $day = !empty($_POST['birthdate']['day'])? (int) $_POST['birthdate']['day']: NULL;
  $month = !empty($_POST['birthdate']['month'])? (int) $_POST['birthdate']['month']: NULL;
  $year = !empty($_POST['birthdate']['year'])? (int) $_POST['birthdate']['year']: NULL;

  if (!current_user_can('edit_user', $uid)) {
    return FALSE;
  }
  $birthdate = ($day && $month && $year)? mktime(12, 0, 0, $month, $day, $year): '';

  $post = $_POST + array('contest_optin' => 0, 'contest_birthdate' => $birthdate);

  $uid = $usr->savePost($post);
}
/**
 * Implementation of action: parse_request.
 * Include the required resources to process the request.
 *
 * @param $wp (object) The request object.
 */
function contest_request_include(&$wp) {
  if (array_key_exists('contest_admin_cid', $wp->query_vars)) {
    require CONTEST_DIR . '/contest_admin_trans.php';
    exit();
  }
  if (array_key_exists('contest_entry_cid', $wp->query_vars)) {
    require CONTEST_DIR . '/contest_entry_trans.php';
    exit();
  }
}
/**
 * Implementation of filter: query_vars.
 * Register the query variables for the contest.
 *
 * @param $vars (array) The array of the current query variables.
 *
 * @return $vars (array) The array of query variables with the contest entries added.
 */
function contest_request_vars($vars) {
  $vars[] = 'contest_entry_cid';
  $vars[] = 'contest_admin_cid';
  return $vars;
}
/**
 * Implementation of action: init.
 * Route contest requests appropriately.
 */
function contest_request_router() {
  add_rewrite_rule('contest-admin-trans$', CONTEST_DIR . '/contest_admin_trans.php', 'top');
  add_rewrite_rule('contest-entry-trans$', CONTEST_DIR . '/contest_entry_trans.php', 'top');
}
/**
 * Build three date select boxes.
 *
 * @param $field_name (string) The field name prefix, ("start", "end", "birthdate").
 * @param $date (int) The current start/end date.
 *
 * @return (string) The HTML for three select boxes.
 */
function contest_render_date_select($field_name, $date) {
  $day = $date? date('j', $date): '';
  $days = array('' => '-' . _('Day') . '-') + array_combine(range(1, 31), range(1, 31));
  $month = $date? date('n', $date): '';
  $months = array('' => '-' . _('Month') . '-') + _contest_get_months();
  $year = $date? date('Y', $date): '';
  $year_max = ($field_name == 'birthdate')? (int) date('Y'): ((int) date('Y')) + 2;
  $year_min = ($field_name == 'birthdate')? ((int) date('Y')) - 100: ((int) date('Y')) - 1;

  $years = array('' => '-' . _('Year') . '-') + array_combine(range($year_min, $year_max), range($year_min, $year_max));

  require contest_theme(CONTEST_DIR . '/templates/contest-date.tpl.php');
}
/**
 * The dq_days render callback.
 */
function contest_render_dq_select() {
  $options = get_option('contest_settings');
  $periods = _contest_get_dq_periods();

  $default_value = !empty($options['dq_days'])? $options['dq_days']: '';

  require contest_theme(CONTEST_DIR . '/templates/contest-dq.tpl.php');
}
/**
 * The export_dir render callback.
 */
function contest_render_export_txt() {
  $options = get_option('contest_settings');
  print '<input type="text" name="contest_settings[export_dir]" value="' . (!empty($options['export_dir'])? esc_html($options['export_dir']): '') . '" size="24" maxlength="50" />' . "\n";
}
/**
 * The host_uid render callback.
 */
function contest_render_host_txt() {
  $options = get_option('contest_settings');

  print '
    <input type="text" name="contest_settings[host_uid]" value="' . (!empty($options['host_uid'])? esc_html($options['host_uid']): '') . '" autocomplete="off" size="24" maxlength="50" id="edit-sponsor" class="form-text" />
    <script type="text/javascript">
      jQuery(function($) {
        $("#edit-sponsor").suggest(ajaxurl + "?action=contest_ajax_name", {delay:100, minchars:2});
      });
    </script>
  ';
}
/**
 * The min_age render callback.
 */
function contest_render_min_age_select() {
  $options = get_option('contest_settings');
  $default_value = !empty($options['min_age'])? $options['min_age']: '';
  require contest_theme(CONTEST_DIR . '/templates/contest-age.tpl.php');
}
/**
 * Build a country select box.
 *
 * @param $country (string) The 2 character country code.
 * @param $default (string) The current selection.
 *
 * @return (string) An HTML state select box.
 */
function contest_render_state($country = 'US', $state = '') {
  $states = _contest_get_states($country);

  if (!empty($states)) {
    $states = array_merge(array('' =>  '-' . __('State') . '-'), $states);

    ob_start();
    require contest_theme(CONTEST_DIR . '/templates/contest-state.tpl.php');
    $buffer = ob_get_contents();
    ob_end_clean();

    return $buffer;
  }
  return '<input type="text" name="contest_state" value="' . esc_html($state) . '\" size="30" maxlength="' . CONTEST_STATE_MAX . "\" class=\"regular-text\" id=\"contest-state\" />\n";
}
/**
 * The tnc render callback.
 *
 * @return (string) The fully rendered HTML for the terms and conditions.
 */
function contest_render_tnc() {
  global $post;
  $contest = !empty($post->ID)? contest_load($post->ID): contest_load(0);

  $tokens = array(
    '!host_link'     => "<a href=\"http://{$_SERVER['HTTP_HOST']}\">{$_SERVER['HTTP_HOST']}</a>",
    '!server_link'   => "<a href=\"http://{$_SERVER['SERVER_NAME']}\">{$_SERVER['SERVER_NAME']}</a>",
    '!sponsor'       => !empty($contest->sponsor->url)? '<a href="' . esc_url($contest->sponsor->url) . '">' . preg_replace('/^https?:\/\//', '', esc_url($contest->sponsor->url)) . '</a>': '',
    '@country'       => @geoip_country_code_by_name($_SERVER['SERVER_NAME']),
    '@date_end'      => date('F j Y', $contest->end),
    '@date_notify'   => date('F j Y', ($contest->end + (DAY_IN_SECONDS * 30))),
    '@date_start'    => date('F j Y', $contest->start),
    '@host_address'  => $contest->host->address,
    '@host_business' => $contest->host->title,
    '@host_city'     => $contest->host->city,
    '@host_name'     => $contest->host->full_name,
    '@host_state'    => $contest->host->state,
    '@host_zip'      => $contest->host->zip,
    '@host_title'    => $contest->host->title,
    '@places'        => $contest->places,
    '@timezone'      => get_option('timezone_string', date('e')) . ', ' . date('T'),
  );
  return contest_t(wp_kses($contest->tnc, _contest_get_allowable_tags()), $tokens);
}
/**
 * The tnc render callback.
 */
function contest_render_tnc_txtarea() {
  $options = get_option('contest_settings');
	require contest_theme(CONTEST_DIR . '/templates/contest-field-tnc.tpl.php');
}
/**
 * Save contest status messages to the session for later display.
 *
 * @param $msg (string) The message string.
 * @param $status (string) The type of message, (notice, warning, error).
 */
function contest_set_msg($msg = '', $status = 'notice') {
  static $i = 0;

  if (!$i) {
    @session_start();
  }
  switch ($status) {
    case 'debug':
      $msg = (is_array($msg) || is_object($msg))? '<pre>' . preg_replace(array('/</', '/>/'), array('&lt;', '&gt;'), print_r($msg, TRUE)) . '</pre>': '<pre>Type: ' . gettype($msg) . "\n\n" . preg_replace(array('/</', '/>/'), array('&lt;', '&gt;'), $msg) . '</pre>';
      break;

    case 'error':
      $msg = '<div class="contest-notice-error">' . __('Error') . ": $msg</div>";
      break;

    case 'notice':
      $msg = "<div class=\"contest-notice-notice\">$msg</div>";
      break;

    case 'warning':
      $msg = "<div class=\"contest-notice-warning\">$msg</div>";
      break;
  }
  set_transient("contest_status[$i]", $msg, HOUR_IN_SECONDS);

  $i++;
}
/**
 * Callback for the description on the settings page.
 */
function contest_settings_description_callback() {
  print __('Configuration settings common to all contests.');
}
/**
 * Implementation of action: admin_init.
 */
function contest_settings_init() {
  register_setting('pluginPage', 'contest_settings');

  add_settings_section('contest_plugin_section', __('Contest Settings'), 'contest_settings_description_callback', 'pluginPage');

  add_settings_field('dq_days', __('Days Between Wins'), 'contest_render_dq_select', 'pluginPage', 'contest_plugin_section');
  add_settings_field('export', __('Export Directory'), 'contest_render_export_txt', 'pluginPage', 'contest_plugin_section');
  add_settings_field('host', __('Contest Host'), 'contest_render_host_txt', 'pluginPage', 'contest_plugin_section');
  add_settings_field('min_age', __('Minumum Age'), 'contest_render_min_age_select', 'pluginPage', 'contest_plugin_section');
  add_settings_field('tnc', __('Terms and Conditions'), 'contest_render_tnc_txtarea', 'pluginPage', 'contest_plugin_section');
}
/**
 * The page callback for the settings page.
 */
function contest_settings_page() {
  require contest_theme(CONTEST_DIR . '/templates/contest-settings.tpl.php');
}
/**
 * Fill-in a string with tokens with safe values.\
 *
 * @param $string (string) The string to be filled in.
 * @param $args (array) An array of tokens to use.
 *
 * @return (string) The filled in string.
 */
function contest_t($string = '', $tokens = array()) {
  foreach ($tokens as $key => $value) {
    if ($key{0} == '@') {
      $tokens[$key] = esc_html($value);
    }
  }
  return strtr($string, $tokens);
}
/**
 * Implementation of action: template_include.
 * Allow themes to override the default templates.
 *
 * @param $orig_tpl (string) The path to the original template.
 *
 * @return (string) The path to the overridden template if it exists. Otherwise the original template path.
 */
function contest_theme($orig_tpl) {
  global $post;

  if (!empty($post->post_type) && $post->post_type != 'contest') {
    return $orig_tpl;
  }
  $new_tpl = locate_template(array(preg_replace('/^.*\//', '', $orig_tpl)));

  return $new_tpl? $new_tpl: $orig_tpl;
}
/**
 * Implementation of filter: single_template.
 * Callback to display a contest.
 */
function contest_theme_archive() {
  return CONTEST_DIR . '/templates/contest-archive.tpl.php';
}
/**
 * Implementation of filter: single_template.
 * Callback to display a contest.
 */
function contest_theme_single() {
  return CONTEST_DIR . '/templates/contest-single.tpl.php';
}
/**
 * Write the CSV to file, write headers and read the file so it can be downloaded.
 *
 * @param $path (string) The path to the file's directory.
 * @param $file (string) The file name.
 * @param $csv (string) The CSV we'll be downloading.
 */
function _contest_download($path = '', $file = '', $csv = '') {
  if (!file_exists($path)) {
    wp_mkdir_p($path);
  }
  $fh = fopen("$path/$file", 'w+');

  if (!$fh || !$csv) {
    return FALSE;
  }
  fwrite($fh, $csv);
  fclose($fh);

  header('Content-Type: application/csv');
  header("Content-Disposition: attachment; filename=$file");
  header('Pragma: no-cache');
  readfile("$path/$file");
}
/**
 * Format all US phone numbers as XXX-XXX-XXXX.
 *
 * @param $country (string) The country.
 * @param $phone (string) The phone number.
 *
 * @return (string) A US phone number formatted as XXX-XXX-XXXX or the trimed phone number.
 */
function _contest_filter_phone($country, $phone) {
  $phone = trim($phone);

  if ($country != 'USA' || preg_match('/[a-z]+/i', $phone)) {
    return $phone;
  }
  return preg_match('/^1?(\d{3})(\d{3})(\d{4})$/', preg_replace('/\D+/', '', $phone), $m)? "{$m[1]}-{$m[2]}-{$m[3]}": $phone;
}
/**
 * An array of safe tags to be used in wp_kses().
 *
 * @return (array) An array of tags formatted to be used in wp_kses().
 */
function _contest_get_allowable_tags() {
  return array(
    'a'       => array(
      'href'  => array(),
      'title' => array(),
      'style' => array(),
    ),
    'address' => array('style' => array()),
    'b'       => array('style' => array()),
    'br'      => array('style' => array()),
    'cite'    => array('style' => array()),
    'code'    => array('style' => array()),
    'em'      => array('style' => array()),
    'h1'      => array('style' => array()),
    'h2'      => array('style' => array()),
    'h3'      => array('style' => array()),
    'h4'      => array('style' => array()),
    'h5'      => array('style' => array()),
    'h6'      => array('style' => array()),
    'hr'      => array('style' => array()),
    'i'       => array('style' => array()),
    'li'      => array('style' => array()),
    'ol'      => array('style' => array()),
    'p'       => array('style' => array()),
    'span'    => array('style' => array()),
    'strike'  => array('style' => array()),
    'strong'  => array('style' => array()),
    'sub'     => array('style' => array()),
    'sup'     => array('style' => array()),
    'u'       => array('style' => array()),
    'ul'      => array('style' => array()),
  );
}
/**
 * The page callback for the admin list page.
 */
function _contest_get_contestants($cid = 0) {
  global $wpdb;
  $data = array();

  $stmt = "
		SELECT
		  COUNT(e.uid) AS 'qty',
		  IFNULL(u.display_name, u.user_login) AS 'name',
		  e.uid,
		  u.user_email AS 'email',
		  IFNULL((SELECT winner FROM contest_entry WHERE winner > 0 AND cid = e.cid AND uid = e.uid), 0) AS 'winner',
		  IFNULL((SELECT winner FROM contest_entry WHERE winner > 0 AND cid = e.cid AND uid = e.uid), 100) AS 'sort'
		FROM
		  users u
		  JOIN contest_entry e ON e.uid = u.ID
		WHERE
		  e.cid = %d
		GROUP BY
		  e.uid
		ORDER BY
		  sort ASC,
		  COUNT(e.uid) DESC,
		  u.user_email ASC
  ";
  $rows = $wpdb->get_results($wpdb->prepare($stmt, $cid));

  foreach ($rows as $row) {
    $data[$row->uid] = $row;
  }
  return $data;
}
/**
 * Build an array of disqualification period options.
 *
 * @return (array) An array of disqualification periods.
 */
function _contest_get_dq_periods() {
  return array(
    WEEK_IN_SECONDS       => __('One week between wins.'),
    (30 * DAY_IN_SECONDS) => __('One month between wins.'),
    (90 * DAY_IN_SECONDS) => __('Three months between wins.'),
    YEAR_IN_SECONDS       => __('One year between wins.'),
    CONTEST_INT_MAX       => __('Can only win once.'),
  );
}
/**
 * Return true if entered in the contest during this period, (configuarble).
 *
 * @param $nid (int) The node ID.
 * @param $uid (int) The user's ID.
 * @param $period (int) The seconds allowed between entries.
 *
 * @return (bool) True if the user has entered the contest already durring this period.
 */
function _contest_get_entered($uid, $cid, $period) {
  global $wpdb;
  $periods = _contest_get_entry_periods();
  $fmt = array(
    DAY_IN_SECONDS        => 'Y-m-d',
    WEEK_IN_SECONDS       => 'Y-W',
    (DAY_IN_SECONDS * 30) => 'Y-m',
    YEAR_IN_SECONDS       => 'Y',
  );
  if ($periods[$period] == 'once') {
    return $wpdb->query($wpdb->prepare("SELECT 1 FROM {$wpdb->prefix}contest_entry WHERE uid = %d AND cid = %d", $uid, $cid))? TRUE: FALSE;
  }
  if (empty($fmt[$period])) {
    return TRUE;
  }
  $today = date($fmt[$period], CONTEST_TIME);
  $entered = date($fmt[$period], $wpdb->get_var($wpdb->prepare("SELECT created FROM {$wpdb->prefix}contest_entry WHERE uid = %d AND cid = %d ORDER BY created DESC", $uid, $cid)));

  return ($entered == $today)? TRUE: FALSE;
}
/**
 * Build an array of entry period options.
 *
 * @return (array) An array of entry periods.
 */
function _contest_get_entry_periods() {
  return array(
    DAY_IN_SECONDS        => 'daily',
    WEEK_IN_SECONDS       => 'weekly',
    (30 * DAY_IN_SECONDS) => 'monthly',
    YEAR_IN_SECONDS       => 'yearly',
    CONTEST_INT_MAX       => 'once',
  );
}
/**
 * Build and return an array of months.
 *
 * @return (array) An array of months.
 */
function _contest_get_months() {
  return array(
    1  => __('January'),
    2  => __('February'),
    3  => __('March'),
    4  => __('April'),
    5  => __('May'),
    6  => __('June'),
    7  => __('July'),
    8  => __('August'),
    9  => __('September'),
    10 => __('October'),
    11 => __('November'),
    12 => __('December'),
  );
}
/**
 * Build the data to display the contest entry form.
 *
 * @return (object) The contest data.
 */
function _contest_get_page_data() {
  global $post;
  $contest = contest_load($post->ID);
  $contest->post = $post;
  $contest->results = $contest->publish_winners? _contest_get_results($contest->cid): array();

  return $contest;
}
/**
 * Build an array of contest winners indexed by place ascending.
 *
 * @param $cid (int) The contest ID.
 *
 * @return $results (array) An array of winners ordered by place ascending.
 */
function _contest_get_results($cid = 0) {
  $results = array();
  $winners = _contest_get_winners($cid);

  foreach ($winners as $uid => $place) {
    $results[$place] = new \contest\ContestUser($uid);
  }
  return $results;
}
/**
 * Gets the states for the selected country.
 *
 * @param $country (string) The ISO country code.
 *
 * @return (array) An ISO code to country name hash.
 */
function _contest_get_states($country = 'US') {
  switch ($country) {
    case 'CA':
      return array(
        'AB' => 'Alberta',
        'BC' => 'British Columbia',
        'MB' => 'Manitoba',
        'NB' => 'New Brunswick',
        'NL' => 'Newfoundland and Labrador',
        'NS' => 'Nova Scotia',
        'ON' => 'Ontario',
        'PE' => 'Prince Edward Island',
        'QC' => 'Quebec',
        'SK' => 'Saskatchewan',
        'NT' => 'Northwest Territories',
        'NU' => 'Nunavut',
        'YT' => 'Yukon',
      );
    case 'US':
      return array(
        'AL' => 'Alabama',
        'AK' => 'Alaska',
        'AZ' => 'Arizona',
        'AR' => 'Arkansas',
        'CA' => 'California',
        'CO' => 'Colorado',
        'CT' => 'Connecticut',
        'DE' => 'Delaware',
        'DC' => 'District Of Columbia',
        'FL' => 'Florida',
        'GA' => 'Georgia',
        'HI' => 'Hawaii',
        'ID' => 'Idaho',
        'IL' => 'Illinois',
        'IN' => 'Indiana',
        'IA' => 'Iowa',
        'KS' => 'Kansas',
        'KY' => 'Kentucky',
        'LA' => 'Louisiana',
        'ME' => 'Maine',
        'MD' => 'Maryland',
        'MA' => 'Massachusetts',
        'MI' => 'Michigan',
        'MN' => 'Minnesota',
        'MS' => 'Mississippi',
        'MO' => 'Missouri',
        'MT' => 'Montana',
        'NE' => 'Nebraska',
        'NV' => 'Nevada',
        'NH' => 'New Hampshire',
        'NJ' => 'New Jersey',
        'NM' => 'New Mexico',
        'NY' => 'New York',
        'NC' => 'North Carolina',
        'ND' => 'North Dakota',
        'OH' => 'Ohio',
        'OK' => 'Oklahoma',
        'OR' => 'Oregon',
        'PA' => 'Pennsylvania',
        'RI' => 'Rhode Island',
        'SC' => 'South Carolina',
        'SD' => 'South Dakota',
        'TN' => 'Tennessee',
        'TX' => 'Texas',
        'UT' => 'Utah',
        'VT' => 'Vermont',
        'VI' => 'Virgin Islands',
        'VA' => 'Virginia',
        'WA' => 'Washington',
        'WV' => 'West Virginia',
        'WI' => 'Wisconsin',
        'WY' => 'Wyoming',
      );
  }
  return array();
}
/**
 * Get the user's IP.
 *
 * @return (string) The user's IP.
 */
function _contest_get_user_ip() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    return apply_filters('wpb_get_ip', $_SERVER['HTTP_CLIENT_IP']);
  }
  if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    return apply_filters('wpb_get_ip', $_SERVER['HTTP_X_FORWARDED_FOR']);
  }
  return apply_filters('wpb_get_ip', $_SERVER['REMOTE_ADDR']);
}
/**
 * Build and return a uid to place array.
 *
 * @param $cid (int) The contest ID.
 *
 * @return $winners (array) A uid to winning place array.
 */
function _contest_get_winners($cid = 0) {
  global $wpdb;
  $rows = $wpdb->get_results($wpdb->prepare("SELECT uid, winner FROM {$wpdb->prefix}contest_entry WHERE cid = %d AND winner ORDER BY winner ASC", $cid));
  $winners = array();

  foreach ($rows as $row) {
    $winners[$row->uid] = $row->winner;
  }
  return $winners;
}
/**
 * Implentation of register_activation_hook().
 * Create the contest content type, create the contest and contest_entry tables, set all the options to defaults, create user fields.
 */
function _contest_register_activation() {
  require CONTEST_DIR . '/contest_install.php';
  contest_activation();
}
/**
 * Implentation of register_deactivation_hook().
 */
function _contest_register_deactivate() {
  require CONTEST_DIR . '/contest_install.php';
  contest_deactivate();
}
/**
 * Implentation of register_uninstall_hook().
 * Delete the contest content type, drop the contest and contest_entry tables, delete all the contest options and delete user fields.
 */
function _contest_register_uninstall() {
  require CONTEST_DIR . '/contest_install.php';
  contest_uninstall();
}
/**
 * Transaction callback to clear all contest winners.
 *
 * @param $cid (int) The contest ID.
 * @param $uid (int) The user ID.
 *
 * @return (bool) True if successful, otherwise false.
 */
function _contest_trans_clear_winners($cid = 0, $uid = 0) {
  global $wpdb;
  $published = $wpdb->get_var($wpdb->prepare("SELECT publish_winners FROM {$wpdb->prefix}contest WHERE cid = %d", $cid));

  if ($published) {
    contest_set_msg(__('You must unpublish contest winners before clearing a winner.'), 'error');
    return FALSE;
  }
  if ($uid) {
    $wpdb->update("{$wpdb->prefix}contest_entry", array('winner' => 0), array('cid' => $cid, 'uid' => $uid));
  }
  else {
    $wpdb->update("{$wpdb->prefix}contest_entry", array('winner' => 0), array('cid' => $cid));
  }
  return TRUE;
}
/**
 * Transaction callback to export all contest entries.
 *
 * @param $cid (int) The contest ID.
 */
function _contest_trans_export_entries($cid = 0) {
  global $wpdb;
  $csv = '"email","name","address","city","state","zip","phone"' . "\n";
  $file_name = "contest_entries_{$cid}_" . CONTEST_TIME . '.csv';
  $settings = (object) get_site_option('contest_settings');
  $upload_dir = wp_upload_dir();
  $fields = array(
    'email',
    'full_name',
    'address',
    'city',
    'state',
    'zip',
    'phone',
  );
  $rows = $wpdb->get_results($wpdb->prepare("SELECT uid FROM {$wpdb->prefix}contest_entry WHERE cid = %d", $cid));

  foreach ($rows as $row) {
    $usr = new \contest\ContestUser($row->uid);
    if ($usr->uid) {
      $csv .= $usr->toCsv($fields);
    }
  }
  _contest_download("{$upload_dir['basedir']}/$settings->export_dir", $file_name, $csv);

  exit();
}
/**
 * Transaction callback to export unique contest entries.
 *
 * @param $cid (int) The contest ID.
 */
function _contest_trans_export_unique($cid = 0) {
  global $wpdb;
  $csv = '"email","name","address","city","state","zip","phone"' . "\n";
  $file_name = "contest_entries_{$cid}_" . CONTEST_TIME . '.csv';
  $settings = (object) get_site_option('contest_settings');
  $upload_dir = wp_upload_dir();
  $fields = array(
    'email',
    'full_name',
    'address',
    'city',
    'state',
    'zip',
    'phone',
  );
  $rows = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(uid) FROM {$wpdb->prefix}contest_entry WHERE cid = %d", $cid));

  foreach ($rows as $row) {
    $usr = new \contest\ContestUser($row->uid);
    if ($usr->uid) {
      $csv .= $usr->toCsv($fields);
    }
  }
  _contest_download("{$upload_dir['basedir']}/$settings->export_dir", $file_name, $csv);

  exit();
}
/**
 * Transaction callback to pick a winner. If no user ID is supplied the winner will be selected at random.
 *
 * @param $cid (int) The contest ID.
 * @param $uid (int) The user ID.
 *
 * @return (bool) True if successful, otherwise false.
 */
function _contest_trans_pick_winner($cid = 0, $uid = 0) {
  global $wpdb;

  if ($uid) {
    $stmt = "
      SELECT
        e.uid,
        e.created
      FROM
        {$wpdb->prefix}contest_entry e
        JOIN {$wpdb->prefix}users u ON u.ID = e.uid
      WHERE
        e.cid = %d
        AND e.uid = %d
      ORDER BY
        RAND()
    ";
    $row = $wpdb->get_row($wpdb->prepare($stmt, $cid, $uid));
  }
  else {
    $stmt = "
      SELECT
        e.uid,
        e.created
      FROM
        {$wpdb->prefix}contest_entry e
        JOIN {$wpdb->prefix}users u ON u.ID = e.uid
      WHERE
        e.cid = %d
      ORDER BY
        RAND()
    ";
    $row = $wpdb->get_row($wpdb->prepare($stmt, $cid));
  }
  if ($row->uid) {
    $place = 1 + $wpdb->get_var($wpdb->prepare("SELECT winner FROM {$wpdb->prefix}contest_entry WHERE cid = %d AND winner ORDER BY winner DESC", $cid));

    $conditions = array(
      'cid'     => $cid,
      'uid'     => $row->uid,
      'created' => $row->created
    );
    $wpdb->update("{$wpdb->prefix}contest_entry", array('winner' => $place), $conditions);
    return TRUE;
  }
  return FALSE;
}
/**
 * Transaction callback to publish contest winners.
 *
 * @param $cid (int) The contest ID.
 *
 * @return (bool) True if successful, otherwise false.
 */
function _contest_trans_publish_winners($cid = 0) {
  global $wpdb;
  $places = $wpdb->get_var($wpdb->prepare("SELECT places FROM {$wpdb->prefix}contest WHERE cid = %d", $cid));
  $winners = $wpdb->get_var($wpdb->prepare("SELECT COUNT(uid) FROM {$wpdb->prefix}contest_entry WHERE cid = %d AND winner", $cid));

  if ($winners != $places) {
    contest_set_msg(__('You can\'t publish winners before they\'ve all been selected.'), 'error');
    return FALSE;
  }
  $wpdb->update("{$wpdb->prefix}contest", array('publish_winners' => 1), array('cid' => $cid));

  return TRUE;
}
/**
 * Transaction callback to unpublish contest winners.
 *
 * @param $cid (int) The contest ID.
 *
 * @return (bool) True if successful, otherwise false.
 */
function _contest_trans_unpublish_winners($cid = 0) {
  global $wpdb;
  $wpdb->update("{$wpdb->prefix}contest", array('publish_winners' => 0), array('cid' => $cid));
  return TRUE;
}

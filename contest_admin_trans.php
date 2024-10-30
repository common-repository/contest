<?php

/**
 * @file
 * Handle the contest administration transactions.
 */
// Process the inputs.

  $cid = (!empty($_GET['contest_admin_cid']) && is_numeric($_GET['contest_admin_cid']))? (int) preg_replace('/\D+/', '', $_GET['contest_admin_cid']): 0;
  $op = !empty($_GET['op'])? preg_replace('/\W+/', '', strtolower($_GET['op'])): '';
  $uid = (!empty($_GET['uid']) && is_numeric($_GET['uid']))? (int) preg_replace('/\D+/', '', $_GET['uid']): 0;

  $nonce = !empty($_GET["{$op}_nonce"])? $_GET["{$op}_nonce"]: '';
  $redirect_url = admin_url() . "admin.php?page=contest-admin&cid=$cid";

// Check the nonce.

  if (!wp_verify_nonce($nonce, $op)) {
    wp_redirect($redirect_url);
    exit();
  }
// Process the request by operation.

  switch ($op) {
    case 'clear_winners':
      _contest_trans_clear_winners($cid, $uid);
      break;

    case 'export_entries':
      _contest_trans_export_entries($cid);
      break;

    case 'export_unique':
      _contest_trans_export_unique($cid);
      break;

    case 'pick_winner':
      _contest_trans_pick_winner($cid, $uid);
      break;

    case 'publish_winners':
      _contest_trans_publish_winners($cid);
      break;

    case 'unpublish_winners':
      _contest_trans_unpublish_winners($cid);
      break;
  }
// Redirect back to admin page.

  wp_redirect($redirect_url);

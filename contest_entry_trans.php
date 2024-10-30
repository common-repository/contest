<?php

/**
 * @file
 * Handle the contest entry transactions.
 */
  $cid = (!empty($_POST['contest_entry_cid']) && is_numeric($_POST['contest_entry_cid']))? (int) preg_replace('/\D+/', '', $_POST['contest_entry_cid']): 0;
  $nonce = !empty($_POST['contest_entry_nonce'])? $_POST['contest_entry_nonce']: '';

  if (wp_verify_nonce($nonce, 'contest_entry') && $cid) {
    contest_entry($cid);
  }
  wp_redirect(get_permalink($cid));

exit();

<?php

/**
 * @file The contest plugin install, deactivate and uninstall file.
 */

/**
 * Deactivate the contest plugin.
 */
function contest_deactivate() {
  define('WP_UNINSTALL_PLUGIN', TRUE);
  contest_uninstall();
}
/**
 * Install the contest plugin.
 * Create the contest content type, create the contest and contest_entry tables, set all the options to defaults, create user fields.
 */
function contest_activation() {
  global $wpdb;
  $usr = get_user_by('id', get_current_user_id());

// Create the contest table.

  $sql = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}contest (
      cid int(11) NOT NULL default 0,
      sponsor_id int(11) DEFAULT NULL,
      start int(11) NOT NULL DEFAULT 0,
      end int(11) NOT NULL DEFAULT 0,
      places tinyint(4) NOT NULL DEFAULT 1,
      period int(11) NOT NULL DEFAULT 86400,
      publish_winners tinyint(4) NOT NULL DEFAULT 0,
      INDEX start (start),
      INDEX end (end),
      PRIMARY KEY (cid)
    )
  ";
  $res = $wpdb->query($sql);

  if ($res === FALSE) {
    contest_feedback('There was a problem creating the contest table.', 'error');
    contest_deactivate();
    return;
  }
// Create the contest_entry table.

  $sql = "
    CREATE TABLE IF NOT EXISTS {$wpdb->prefix}contest_entry (
      cid int(11) NOT NULL default 0,
      uid int(11) NOT NULL default 0,
      created int(11) NOT NULL default 0,
      ip varchar(30) DEFAULT NULL,
      winner tinyint(4) NOT NULL default 0,
      INDEX cid (cid),
      INDEX uid (uid),
      INDEX created (created),
      PRIMARY KEY (uid, cid, created)
    )
  ";
  $res = $wpdb->query($sql);

  if ($res === FALSE) {
    contest_feedback('There was a problem creating the contest_entry table.', 'error');
    contest_deactivate();
    return;
  }
// Set our options.

  $settings = array(
    'dq_days'    => 90 * DAY_IN_SECONDS,        // The number of days between a persons eligibility to win another contest.
    'export_dir' => 'contest_export',           // The contest results export directory.
    'host_uid'   => "$usr->ID:$usr->user_login",// The contest host's user id. We'll set it to the admin's to start.
    'min_age'    => 18,                         // The minimum age to enter the contest.
    'tnc'        => _contest_install_tnc(),     // Load an example terms and conditions.
  );
  add_option('contest_settings', $settings, '', 'no');
}
/**
 * Uninstall the contest plugin.
 * Delete the contest content type, drop the contest and contest_entry tables, delete all the contest options and delete user fields.
 */
function contest_uninstall() {
  global $wpdb;
  if (!defined('WP_UNINSTALL_PLUGIN')) {
    return;
  }
  delete_option('contest_settings');
  delete_site_option('contest_settings');

  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}contest");
  $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}contest_entry");
}
/**
 * An example terms and conditions.
 *
 * @return (string) An HTML orderd list example terms and conditions.
 */
function _contest_install_tnc() {
  return "
    <ol>
      <li>
        <strong>No purchase necessary to enter or win. A purchase or payment of any kind will not increase your chances of winning.</strong>
      </li>
      <li>
        <strong>How to Enter Via the Internet:</strong> Beginning @date_start through midnight @date_end (@timezone), visit !host_link and follow the on-screen instructions to be entered into the sweepstakes. You must already have access to the Internet, as it is not required that you obtain Internet access solely to participate in this Sweepstakes. The computer used to enter the Sweepstakes must accept cookies, or any successor or similar technology (now know or hereafter devised or discovered), which may be used by !host_link for the purposes of entry tracking. All entries must be made manually. Limit one entry per email address, per household per day. Entries submitted through the use of scripts, programs or other electro-mechanical processes (other than manual entry) are prohibited. No responsibility is assumed for any computer, telephone, cable, network, satellite, electronic or Internet hardware or software malfunctions, failures, connections, availability or garbled or jumbled transmissions, or service provider/Internet/Web site/use net accessibility or availability, traffic congestions, or unauthorized human intervention.
      </li>
      <li>
        <strong>How to Enter Via Mail:</strong> Look for an Official Entry Form in @host_business. To enter the sweepstakes, check the appropriate box on your Official Entry Form and hand print your name, address, zip code plus (optional) email address. Affix first-class postage and mail.
      </li>
      <li>
        <strong>Eligibility:</strong> This sweepstakes (&ldquo;Contest&rdquo;) is hosted by @host_title, Inc. D.B.A. @host_name and prizes provided by  !sponsor (&ldquo;Sponsor&rdquo;) to @country residents only (excluding @country territories), who are at least 18 years old. Employees and directors of host or sponsor and all of its subsidiary and related companies and the immediate family (spouse, mother, father, sister, brother, daughter or son, regardless of where they live) or members of their same households (whether related or not) of such employees and directors are not eligible. By entering this sweepstakes, you agree to these Official Rules and the decisions of host, which shall be final and binding in all respects. No purchase is necessary to win, and purchasing any product will not improve your chances to win. Winner must accept all terms and conditions of prize to qualify. Sweepstakes open to general public.
      </li>
      <li>
        <strong>Promotional Period:</strong> The &ldquo;Promotional Period&rdquo; for this Sweepstakes begins on @date_start and ends on @date_end at midnight (@timezone).
      </li>
      <li>
        <strong>Drawing:</strong> Winner will be selected by random drawing from all eligible entries received during the Promotional Period. All non-winning entries will become the property of host and sponsor and may be used as seen fit for marketing publicity or sale.
      </li>
      <li>
        <strong>Prize/Odds:</strong> No warranties or representations of any kind are made about the prizes. host reserves the right to exchange any prize for any reason with another prize. No assignment or transfer of a prize is permitted prior to delivery of the prize to the winner. Winner is responsible for all federal, state and local taxes and shipping and handling charges. The prize(s), of which @places will be awarded, (described above).
      </li>
      <li>
        <strong>General Conditions:</strong> Void where prohibited. Winner agrees to release and hold harmless host, its affiliates, subsidiaries, advertising and promotion agencies and their respective directors, officers, employees, representatives and agents from any and all liability for any injuries, loss or damage of any kind to person, including death, and property, arising in whole or in part, directly or indirectly, from acceptance, possession, use or misuse of a prize, participation in any sweepstakes related activity, or participation in this sweepstakes. To accomplish this, winner must execute and return an Affidavit of Eligibility, Publicity Release and Release from Liability within 10 days of notification. Failure to timely return this affidavit, or if prize notification or prize is returned as non-deliverable, may result in disqualification with an alternate winner selected. Where permitted by law, winner (and in the instance when interim drawing has taken place, potential winner who has been selected in the interim drawing) agree to grant to host, and its licensees, affiliates and assigns, the right to print, publish, broadcast and use, worldwide in any media now known or hereafter developed, including but not limited to the world wide web, at any time or times, the winner&rsquo;s name, portrait, picture, voice, likeness and biographical information as news or information and for advertising and promotional purposes without additional consideration; and further without such additional compensation, appear for, or provide biographical information for use in any presentation or other activity which may include filming/audio/video/electronic or other recordings and/or interviews, as may be determined from time to time by host. Failure to make such appearances or grant such rights may result in disqualification with an alternate winner or potential winner selected; and while not obligated to do so, host may in its sole discretion, bear such reasonable costs and expenses which host, in its sole discretion, deems appropriate for winners or potential winners to appear for a presentation or other activity. host reserved the right, at its sole discretions, to disqualify any individual it finds, in its sole discretion, to be tampering with the entry process or the operation of the Sweepstakes or Web site; to be in violation of the Terms of Service of the Web site, to be acting in violation of these Sweepstakes Rules; or to be acting in a non-sportsmanlike or disruptive manner, or with intent to annoy, abuse, threaten or harass any other person. Any use of robotic, automatic, macros, programmed or like entry method will void all such entries by such method. In the event of a dispute as to entries submitted by multiple users having the same email account, the authorized subscriber of the account used to enter the Sweepstakes at the actual time of entry will be deemed to be the participant and must comply with these rules. Authorized account subscriber is deemed to be the natural person who is assigned an email address by an Internet access provider, online service provider or other organization, which is responsible for assigning email addresses or the domain associated with the submitted email address. host will prosecute any fraudulent activities to the full extent of the law.
      </li>
      <li>
        <strong>Limitations of Liability:</strong> host is not responsible for any incorrect or inaccurate information, whether caused by Web site users, or tampering or hacking, or by any of the equipment or programming associated with or utilized in the Sweepstakes and assumes no responsibility for any error, omission, interruption, deletion, defect, delay in operation or transmission, communications line failures, theft or destruction or unauthorized access to the Web site. host is not responsible for injury or damage to participants&rsquo; or to any other person&rsquo;s computer related to or resulting from participating in this Sweepstakes or downloading material from or use of the Web site. If, for any reason, the Sweepstakes is not capable of running as planned by reason of infection by computer virus, worms, bugs, tampering, unauthorized interventions, fraud, technical failures, or any other causes which, in the sole opinion of host, could corrupt or affect the administrations, security, fairness, integrity or proper conduct of this Sweepstakes, host reserved the right at its sole discretion to cancel, terminate, modify or suspend the Internet portion of this sweepstakes for any drawing(s) and select the winner from Internet entries received for that drawing prior to the action taken. IN NO VENT WILL HOST, ITS LICENSEES, AND OR AFFILIATES, SUBSIDIARIES AND RELATED COMPANIES, THEIR ADVERTISING, LEGAL OR PROMOTION AGENCIES OR THEIR RESPECTIVE OFFICERS, DIRECTORS, EMPLOYEES, REPRESENTATIVE AND AGENTS, BE RESPONSIBLE OR LIABLE FOR ANY DAMAGES OR LOSSES OF ANY KIND, INCLUDING DIRECT, INDIRECT, INCIDENTAL, CONSEQUENTIAL OR PUNITIVE DAMAGES ARISING OUT OF YOUR ACCESS TO AND USE OF INTERNET SITE !host_link OR THE DOWNLOADING FROM AND/OR PRINTING MATERIAL DOWNLOADED FROM SAID SITE. WITHOUT LIMITING THE FOREGOING, EVERYTHING ON THIS SITE IS PROVIDED &ldquo;AS IS&rdquo; WITHOUT WARRANTY OF ANY KIND. EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR NON-INFRINGEMENT. SOME JURISDICTIONS MAY NOT ALLOW THE LIMITATIONS OR EXCLUSION OF LIABILITY FOR INCIDENTAL OR CONSEQUENTIAL DAMAGES OR EXCLUSION OF IMPLIED WARRANTIES SO SOME OF THE ABOVE LIMITATIONS OR EXCLUSIONS MAY NOT APLY TO YOU. CHECKK YOUR LOCAL LAWS FOR ANY RESTRICTIONS OR LIMITATION REGARDING THESE LIMITATIONS OR EXCLUSIONS.
      </li>
      <li>
        <strong>Disputes:</strong> As a condition of participating in this Sweepstakes, participant agrees that any and all disputes which cannot be resolved between the parties, and causes of action arising out of or connected with this Sweepstakes, shall be resolved individually, without resort to any form of class action, exclusively, before a court located in @country, @host_state having competent jurisdictions, which Court shall apply the laws of the State of @host_state without regard for the doctrines of Conflict of Law. Further, in any such dispute, under no circumstances will participant be permitted to obtain awards for, and hereby waives all rights to claim punitive, incidental or consequential damages, or any other damages, including attorney&rsquo;s fees, other than participant&rsquo;s actual out-of-pocket expense (i.e. costs associated with entering this Promotion), and participant further waives all rights to have damages multiplied or increased.
      </li>
      <li>
        <strong>Winner Notification:</strong> Winner will be notified via phone, e-mail or postal mail, at host&rsquo;s discretion, on or about @date_notify. For the winner&rsquo;s name, send a stamped, self-addressed envelope to: @host_name Attn: Sweepstakes Winners List Please, @host_address, @host_city, @host_state @host_zip
      </li>
      <li>
        <strong>COPPA Policy:</strong> In accordance with the Children&rsquo;s Online Protection Policy, we cannot accept registration from anyone under the age of eighteen. COPPA provides protection for children while online and prohibits Web sites from accepting any identifiable data or information from anyone thirteen and under.
      </li>
    </ol>
  ";
}

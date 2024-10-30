<?php

/**
 * @file contest-field-tnc.tpl.php
 * The template for the contest tnc field.
 *
 * Available variables:
 * - $options: (array)
 * - - tnc: (string) The contest's terms and conditions.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<div id="contest-tnc-wrapper" class="contest-field-wrapper">
  <textarea cols="70" rows="10" name="contest_settings[tnc]">
  	<?php print!empty($options['tnc'])? wp_kses($options['tnc'], _contest_get_allowable_tags()): ''; ?>
  </textarea>
  <div id="contest-admin-tnc-description">
    <strong><?php print __('These are the tokens used by the Terms and Conditions'); ?>:</strong>
    <ul>
	  	<li>!host_link - <?php print __('A link to this site.'); ?></li>
	  	<li>!server_link - <?php print __('An alternat link to this site, (Apache configuration varies).'); ?></li>
	  	<li>@county - <?php print __("The host's country."); ?></li>
	  	<li>@date_end - <?php print __('The date the contest ends.'); ?></li>
	  	<li>@date_notify - <?php print __('The date the winners will be notified by.'); ?></li>
	  	<li>@date_start - <?php print __('The date the contest starts.'); ?></li>
	  	<li>@host_address - <?php print __("The host's address."); ?></li>
	  	<li>@host_business - <?php print __("The host's business."); ?></li>
	  	<li>@host_city - <?php print __("The hosts's city."); ?></li>
	  	<li>@host_name - <?php print __("The host's name."); ?></li>
	  	<li>@host_state - <?php print __("The host's state."); ?></li>
	  	<li>@host_zip - <?php print __("The host's postal code, (zip)."); ?></li>
	  	<li>@host_title - <?php print __("The host's alternate name."); ?></li>
	  	<li>@places - <?php print __('The number of places this contest is awarding.'); ?></li>
	  	<li>@timezone - <?php print __('The timezone that will be used for the contest.'); ?></li>
    </ul>
  </div>
</div>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

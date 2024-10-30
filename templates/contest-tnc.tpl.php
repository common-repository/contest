<?php

/**
 * @file contest-tnc.tpl.php
 * Template for the contest's terms and conditions.
 */
print WP_DEBUG? "\n<!-- Start: " . __FILE__ . " -->\n": '';
?>
<fieldset class="contest-tnc">
  <legend><a href="#"><?php print __('Terms and Conditions'); ?></a></legend>
  <?php print contest_render_tnc(); ?>
</fieldset>
<script type="text/javascript">
  jQuery('.contest-tnc legend').click(function() {
    jQuery('fieldset.contest-tnc').toggleClass('active');
  });
</script>
<?php print WP_DEBUG? "\n<!-- End: " . __FILE__ . " -->\n": ''; ?>

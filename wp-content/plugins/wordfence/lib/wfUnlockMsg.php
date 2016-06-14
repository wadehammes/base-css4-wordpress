If you are a site administrator and have been accidentally locked out, please enter your email in the box below and click "Send". If the email address you enter belongs to a known site administrator or someone set to receive Wordfence alerts, we will send you an email to help you regain access. <a href="https://docs.wordfence.com/en/Help!_I_locked_myself_out_and_can't_get_back_in._What_can_I_do%3F" target="_blank">Please read this FAQ entry if this does not work.</a>
<br /><br />
<form method="POST" action="<?php echo wfUtils::getSiteBaseURL(); ?>?_wfsf=unlockEmail">
<?php require_once(ABSPATH .'wp-includes/pluggable.php'); ?>
<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('wf-form'); ?>" />
<input type="text" size="50" name="email" value="" maxlength="255" />&nbsp;<input type="submit" name="s" value="Send me an unlock email" />
</form>
<br /><br />

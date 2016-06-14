<div class="wordfenceModeElem" id="wordfenceMode_twoFactor"></div>
<div class="wrap" id="paidWrap">
	<?php require('menuHeader.php'); ?>
	<?php $pageTitle = "Cellphone Sign-in"; $helpLink="http://docs.wordfence.com/en/Cellphone_sign-in"; $helpLabel="Learn more about Cellphone Sign-in"; include('pageTitle.php'); ?>
<?php if(! wfConfig::get('isPaid')){ ?>
	<div class="wf-premium-callout" style="margin: 20px 0 20px 20px; width: 700px;">
		<h3>Cellphone Sign-in is only available to Premium Members</h3>
		<p>Our Cellphone Sign-in uses a technique called "Two Factor Authentication" which is used by banks, government
			agencies and military world-wide as one of the most secure forms of remote system authentication. It's now
			available from Wordfence for your WordPress website. We recommend you enable Cellphone Sign-in for all
			Administrator level accounts.</p>

		<p>Upgrade to Premium today for less than $5 per month:</p>
		<ul>
			<li>Receive real-time Firewall and Scan engine rule updates for protection as threats emerge</li>
			<li>Other advanced features like IP reputation monitoring, an advanced comment spam filter, advanced
				scanning options and country blocking give you the best protection available
			</li>
			<li>Access to Premium Support</li>
			<li>Discounts of up to 75% available for multiyear and multi-license purchases</li>
		</ul>

		<p class="center"><a class="button button-primary"
		                     href="https://www.wordfence.com/gnl1twoFac1/wordfence-signup/">Get Premium</a></p>
	</div>

<?php } ?>

	<div class="wordfenceWrap" style="margin: 20px 20px 20px 30px;">
		<p style="width: 500px;">
		</p>
		<p>
			<table border="0">
			<tr><td>Enter a username to enable Cellphone Sign-in:</td><td><input type="text" id="wfUsername" value="" size="20" /></td></tr>
			<tr><td>Enter a phone number where the code will be sent:</td><td><input type="text" id="wfPhone" value="" size="20" />Format: +1-123-555-5034</td></tr>
			<tr><td colspan="2"><input type="button" value="Enable Cellphone Sign-in" onclick="WFAD.addTwoFactor(jQuery('#wfUsername').val(), jQuery('#wfPhone').val());" /></td></tr>
			</table>
		</p>
		<div style="height: 20px;">
			<div id="wfTwoFacMsg" style="color: #F00;">
			&nbsp;
			</div>
		</div>
		<div id="wfTwoFacUsers">

		</div>
	</div>
</div>

<script type="text/x-jquery-template" id="wfTwoFacUserTmpl">
<div>
	<table border="0"><tr>
		<td style="width: 100px;">${username}</td>
		<td style="width: 150px;">${phone}</td>
		<td>
			{{if status == 'activated'}}
				<span style="color: #0A0;">Cellphone Sign-in Enabled</span>
			{{else}}
				Enter activation code:<input type="text" id="wfActivate" size="4" /><input type="button" value="Activate" onclick="WFAD.twoFacActivate('${userID}', jQuery('#wfActivate').val());" />
			{{/if}}
		</td>
		<td>&nbsp;&nbsp;&nbsp;<a href="#" onclick="WFAD.delTwoFac('${userID}'); return false;">[Delete]</a></td>
	</tr>
	</table>
</div>
</script>
<script type="text/x-jquery-template" id="wfWelcomeTwoFactor">
<div>
<h3>Secure Sign-in using your Cellphone</h3>
<strong><p>Want to permanently block all brute-force hacks?</p></strong>
<p>
	The premium version of Wordfence includes Cellphone Sign-in, also called Two Factor Authentication in the security industry.
	When you enable Cellphone Sign-in on a member's account, they need to complete a 
	two step process to sign in. First they enter their username and password 
	as usual to sign-into your WordPress website. Then they're told
	that a code was sent to their phone. Once they get the code, they sign
	into your site again and this time they add a space and the code to the end of their password.
</p>
<p>
	This technique is called Two Factor Authentication because it relies on two factors: 
	Something you know (your password) and something you have (your phone).
	It is used by banks and military world-wide as a way to dramatically increase
	security.
</p>
<p>
<?php
if(wfConfig::get('isPaid')){
?>
	You have upgraded to the premium version of Wordfence and have full access
	to this feature along with our other premium features.
<?php
} else {
?>
	If you would like access to this premium feature, please 
	<a href="https://www.wordfence.com/gnl1twoFac2/wordfence-signup/" target="_blank">upgrade to our premium version</a>.
<?php
}
?>
</p>
</div>
</script>

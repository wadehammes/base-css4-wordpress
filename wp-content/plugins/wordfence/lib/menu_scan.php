<?php
$sigUpdateTime = wfConfig::get('signatureUpdateTime');
?>
<div class="wordfenceModeElem" id="wordfenceMode_scan"></div>
<div class="wrap wordfence">

	<?php
	$nonce = filter_input(INPUT_GET, 'nonce', FILTER_SANITIZE_STRING);
	if (!empty($promptForCredentials) && !empty($wpFilesystemActionCallback) && is_callable($wpFilesystemActionCallback)):
		if (wp_verify_nonce($nonce, 'wp-ajax')) {
			$relaxedOwnership = true;
			$homePath = get_home_path();

			if (!wordfence::requestFilesystemCredentials($filesystemCredentialsAdminURL, $homePath, $relaxedOwnership, true)) {
				echo '</div>';
				return;
			}

			call_user_func_array($wpFilesystemActionCallback,
				!empty($wpFilesystemActionCallbackArgs) && is_array($wpFilesystemActionCallbackArgs) ? $wpFilesystemActionCallbackArgs : array());
		} else {
			printf("Security token has expired. Click <a href='%s'>here</a> to return to the scan page.", esc_url(network_admin_url('admin.php?page=Wordfence')));
		}

		?>

	<?php else: ?>

	<?php require('menuHeader.php'); ?>
	<?php $pageTitle = "Wordfence Scan"; $helpLink="http://docs.wordfence.com/en/Wordfence_scanning"; $helpLabel="Learn more about scanning"; include('pageTitle.php'); ?>
	<div class="wordfenceWrap">
		<div class="wordfenceScanButton">
			<table border="0" cellpadding="0" cellspacing="0" style="width: 800px;">
			<tr>
				<td style="width: 250px; padding-top: 10px;">
					<button type="button" id="wfStartScanButton1" class="wfStartScanButton button-primary" onclick="wordfenceAdmin.startScan();">Start a Wordfence Scan</button><br />
					&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="WFAD.killScan(); return false;" style="font-size: 10px; color: #AAA;">Click to kill the current scan.</a>
				</td>
				<td>
					<div style="border: 1px solid #CCC; padding: 4px;">
						<a href="http://docs.wordfence.com/en/Wordfence_scanning" target="_blank" class="wfhelp"></a><a href="http://docs.wordfence.com/en/Wordfence_scanning" target="_blank">Read our scanning documentation</a>. You can also <a href="#" onclick="WFAD.startTourAgain(); return false;">start the tour again</a>, <a href="http://www.wordfence.com/gnl1listSubscr/subscribe-to-the-wordfence-email-list/" target="_blank">subscribe to get WordPress Security Alerts and Product News</a> or <a target="_blank" href="http://support.wordfence.com/">visit our support website help.</a> Love Wordfence? You can help by doing two simple things: <a href="http://wordpress.org/extend/plugins/wordfence/" target="_blank">Go to WordPress.org now and give this plugin a 5&#9733; rating</a>. Blog about Wordfence and link to the <a href="http://wordpress.org/extend/plugins/wordfence/" target="_blank">plugin page</a> or <a href="http://www.wordfence.com/gnl1scanTopHome/" target="_blank">www.wordfence.com</a>. Spreading the word helps us keep the best features free.
					</div>
				</td>
			</tr>
			</table>
		</div>
		<div>
			<div class="consoleHead">
				<span class="consoleHeadText">Scan Summary</span>
			</div>
			<?php 
				$events = wordfence::getLog()->getStatusEvents(0);
			?>
			<div class="bevelDiv1 consoleOuter"><div class="bevelDiv2"><div class="bevelDiv3 consoleInner" id="consoleSummary">
			<?php if(sizeof($events) < 1){ ?>
				<div style="width: 500px;">
					Welcome to Wordfence!<br /><br />
					To get started, simply click the "Scan" button at the top of this page to start your first scan.
				</div>
			<?php } ?>
			</div></div></div>
			<?php if (wfConfig::get('isPaid')) { ?>
				<?php if (wfConfig::get('scansEnabled_fileContents')): ?>
				<div style="width: 800px; ">
					<p class="wf-success">You are running the Premium version of the Threat Defense Feed which is
						updated in real-time as new threats emerge.</p>
				</div>
			<?php else: ?>
				<div class="wfSecure">Premium scanning enabled</div>
			<?php endif ?>
			<?php } else { ?>
				<?php if (wfConfig::get('scansEnabled_fileContents')): ?>
					<p>You are running the Wordfence Community Scan signatures.
<!--						<em id="wf-scan-sigs-last-update"></em>-->
					</p>
				<?php endif ?>

				<div class="wf-premium-callout" style="margin: 20px 0 20px 2px;width: 765px;">
					<h3>The Wordfence Scan alerts you if you've been hacked</h3>

					<p>As new threats emerge, the Threat Defense Feed is updated to detect these new hacks. The Premium
						version of the Threat Defense Feed is updated in real-time protecting you immediately. As a free
						user <strong>you are receiving the community version</strong> of the feed which is updated 30 days later. Upgrade
						now for less than $5 a month!</p>
					<p class="center"><a class="button button-primary"
					                     href="https://www.wordfence.com/gnl1scanUpgrade/wordfence-signup/">
							Get Premium</a></p>
				</div>

			<?php } ?>

			<?php if ($sigUpdateTime ): ?>
				<script>
					WFAD.updateSignaturesTimestamp(<?php echo (int) $sigUpdateTime ?>);
				</script>
			<?php endif ?>

			<div class="consoleHead" style="margin-top: 20px;">
				<span class="consoleHeadText">Scan Detailed Activity</span>
				<a href="#" class="wfALogMailLink" onclick="WFAD.emailActivityLog(); return false;">Email activity log</a>
			</div>
			<div class="bevelDiv1 consoleOuter"><div class="bevelDiv2"><div class="bevelDiv3 consoleInner" id="consoleActivity">
				<?php 
					if(sizeof($events) > 0){
						$debugOn = wfConfig::get('debugOn', false);
						$newestItem = 0;
						$sumEvents = array();
						$timeOffset = 3600 * get_option('gmt_offset');
						foreach($events as $e){
							if(strpos($e['msg'], 'SUM_') !== 0){
								if( $debugOn || $e['level'] < 4){
									$typeClass = '';
									if($debugOn){
										$typeClass = ' wf' . $e['type'];
									}
									echo '<div class="wfActivityLine' . $typeClass . '">[' . date('M d H:i:s', $e['ctime'] + $timeOffset) . ']&nbsp;' . $e['msg'] . '</div>';
								}
							}
							$newestItem = $e['ctime'];
						}

						echo '<script type="text/javascript">WFAD.lastALogCtime = ' . $newestItem . '; WFAD.processActArray(' . json_encode(wordfence::getLog()->getSummaryEvents()) . ');</script>';
					} else { ?>
						A live stream of what Wordfence is busy with right now will appear in this box.

					<?php
					}
				?>
			</div></div></div>
			<div style="position: relative; width: 803px;">
				&nbsp;
				<a href="#" target="_blank" class="wfALogViewLink" id="wfALogViewLink">View activity log</a>
			</div>
			<div style="margin: 0 0 20px 5px; width: 795px;">
				<strong>Docs:</strong> Our <a href="http://support.wordfence.com/" target="_blank">Support Site</a> can answer many common (and some less common) questions. It also includes our priority support ticketing system for Premium Wordfence users. 
				<?php $unknownFilesLink = wfUtils::siteURLRelative() . '?_wfsf=unknownFiles&nonce=' . wp_create_nonce('wp-ajax'); ?>
			</div>

			<div class="wf-premium-callout" style="margin: 20px 0 20px 2px;width: 765px;">
				<h3>Have you been hacked?</h3>
				<p>If your site has been compromised by attackers it is vitally important to restore it to working
					order as quickly as possible. But cleaning up a hacked website can be difficult if you've never
					done it before, sometimes it takes professional intervention. Let our team of seasoned Security
					Analysts resolve it for you quickly and professionally.</p>
				<p class="center"><a class="button button-primary"
				                     href="https://www.wordfence.com/gnl1scanGetHelp/wordfence-site-cleanings/">
						Get Help</a></p>
			</div>


		</div>
		<div style="margin-top: 20px;">
			<div id="wfTabs">
				<a href="#" id="wfNewIssuesTab" class="wfTab2 wfTabSwitch selected" onclick="wordfenceAdmin.switchIssuesTab(this, 'new'); return false;">New Issues</a>
				<a href="#" class="wfTab2 wfTabSwitch"          onclick="wordfenceAdmin.switchIssuesTab(this, 'ignored'); return false;">Ignored Issues</a>
			</div>
			<div class="wfTabsContainer">
				<div id="wfIssues_new" class="wfIssuesContainer">
					<h2>New Issues</h2>
					<?php if (wfConfig::get('scansEnabled_highSense')): ?>
					<div class="wf-notice">
						<em>HIGH SENSITIVITY scanning is enabled, it may produce false positives</em>
					</div>
					<?php endif ?>
					<p>
						The list below shows new problems or warnings that Wordfence found with your site.
						If you have fixed all the issues below, you can <a href="#" onclick="WFAD.updateAllIssues('deleteNew'); return false;">click here to mark all new issues as fixed</a>.
						You can also <a href="#" onclick="WFAD.updateAllIssues('ignoreAllNew'); return false;">ignore all new issues</a> which will exclude all issues listed below from future scans.
					</p>
					<p>
						<a href="#" onclick="jQuery('#wfBulkOps').toggle(); return false;">Bulk operation&raquo;&raquo;</a>
						<div id="wfBulkOps" style="display: none;">
							<input type="button" name="but2" value="Select All Repairable files" onclick="jQuery('input.wfrepairCheckbox').prop('checked', true); return false;" />
							&nbsp;<input type="button" name="but1" value="Bulk Repair Selected Files" onclick="WFAD.bulkOperation('repair'); return false;" />
							<br />
							<br />
							<input type="button" name="but2" value="Select All Deletable files" onclick="jQuery('input.wfdelCheckbox').prop('checked', true); return false;" />
							&nbsp;<input type="button" name="but1" value="Bulk Delete Selected Files" onclick="WFAD.bulkOperation('del'); return false;" />
						</div>

					</p>
					 <div id="wfIssues_dataTable_new">
					 </div>
				</div>
				<div id="wfIssues_ignored" class="wfIssuesContainer">
					<h2>Ignored Issues</h2>
					<p>
						The list below shows issues that you know about and have chosen to ignore.
						You can <a href="#" onclick="WFAD.updateAllIssues('deleteIgnored'); return false;">click here to clear all ignored issues</a>
						which will cause all issues below to be re-scanned by Wordfence in the next scan.
					</p>
					 <div id="wfIssues_dataTable_ignored"></div>
				</div>
			</div>
		</div>
	</div>
	<?php endif ?>

</div>
<script type="text/x-jquery-template" id="issueTmpl_configReadable">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr>
			<th>URL:</th>
			<td><a href="${data.url}" target="_blank">${data.url}</a></td>
		<tr>
			<th>Severity:</th>
			<td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td>
		</tr>
		<tr>
			<th>Status</th>
			<td>
				{{if status == 'new' }}New{{/if}}
				{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
			</td>
		</tr>
	</table>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong>
		{{if data.fileExists}}
		<a target="_blank" href="${WFAD.makeViewFileLink(data.file)}">View the file</a>
		{{/if}}
		<a href="#" onclick="WFAD.hideFile('${id}', 'delete'); return false;">Hide this file in <em>.htaccess</em></a>
		{{if data.canDelete}}
		<a href="#" onclick="WFAD.deleteFile('${id}'); return false;">Delete this file (can't be undone).</a>
		<p>
			<label><input type="checkbox" class="wfdelCheckbox" value="${id}" />&nbsp;Select for bulk delete</label>
		</p>
		{{/if}}
	</div>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
	{{/if}}
	{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_wpscan_fullPathDiscl">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>URL:</th><td><a href="${data.url}" target="_blank">${data.url}</a></td>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		{{if (status == 'new')}}
			<strong>Resolve:</strong>
			<?php if (!wfUtils::isNginx()): ?>
				<a href="#" onclick="WFAD.fixFPD('${id}'); return false;">Fix this issue</a>
			<?php endif ?>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
		{{/if}}
		{{if status == 'ignoreC' || status == 'ignoreP'}}
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
		{{/if}}
	</div>
	{{if (status == 'new')}}
	<div class="wfIssueOptions">
		<strong style="width: auto;">Manual Fix:</strong>
		&nbsp;
		Set <code>display_errors</code> to <code>Off</code> in your php.ini file.
	</div>
	{{/if}}

</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_wpscan_directoryList">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>URL:</th><td><a href="${data.url}" target="_blank">${data.url}</a></td>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>

	<div class="wfIssueOptions">
		{{if (status == 'new')}}
		<strong>Resolve:</strong>
		<?php if (!wfUtils::isNginx()): ?>
			<a href="#" onclick="WFAD.disableDirectoryListing('${id}'); return false;">Fix this issue</a>
		<?php endif ?>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
		{{/if}}
		{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
		{{/if}}
	</div>
	<?php if (!wfUtils::isNginx()): ?>
	{{if (status == 'new')}}
	<div class="wfIssueOptions">
		<strong style="width: auto;">Manual Fix:</strong>
		&nbsp;
		Add <code>Options -Indexes</code> to your .htaccess file.
	</div>
	{{/if}}
	<?php endif ?>

</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_wfThemeUpgrade">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Theme Name:</th><td>${data.name}</td></tr>
		<tr><th>Current Theme Version:</th><td>${data.version}</td></tr>
		<tr><th>New Theme Version:</th><td>${data.newVersion}</td></tr>
		<tr><th>Theme URL:</th><td><a href="${data.URL}" target="_blank">${data.URL}</a></td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
		<a href="<?php echo get_admin_url() . 'update-core.php'; ?>">Click here to update now</a>.
	</p>
	<div class="wfIssueOptions">
		{{if (status == 'new')}}
			<strong>Resolve:</strong>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
		{{/if}}
		{{if status == 'ignoreC' || status == 'ignoreP'}}
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
		{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_wfPluginUpgrade">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Plugin Name:</th><td>${data.Name}</td></tr>
		{{if data.PluginURI}}<tr><th>Plugin Website:</th><td><a href="${data.PluginURI}" target="_blank">${data.PluginURI}</a></td></tr>{{/if}}
		<tr><th>Current Plugin Version:</th><td>${data.Version}</td></tr>
		<tr><th>New Plugin Version:</th><td>${data.newVersion}</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
		<a href="<?php echo get_admin_url() . 'update-core.php'; ?>">Click here to update now</a>.
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
	{{/if}}
	{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_wfUpgrade">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Current WordPress Version:</th><td>${data.currentVersion}</td></tr>
		<tr><th>New WordPress Version:</th><td>${data.newVersion}</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
		<a href="<?php echo get_admin_url() . 'update-core.php'; ?>">Click here to update now</a>.
	</p>
	<div class="wfIssueOptions">
	{{if (status == 'new')}}
		<strong>Resolve:</strong>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this issue</a>
	{{/if}}
	{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_dnsChange">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Old DNS records:</th><td>${data.oldDNS}</td></tr>
		<tr><th>New DNS records:</th><td>${data.newDNS}</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if (status == 'new')}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I know about this change</a>
	{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_badOption">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		{{if data.isMultisite}}
		<tr><th>Multisite Blog ID:</th><td>${data.blog_id}</td></tr>
		<tr><th>Multisite Blog Domain:</th><td>${data.domain}</td></tr>
		<tr><th>Multisite Blog Path:</th><td>${data.path}</td></tr>
		{{/if}}
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignoring all alerts related to this option{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if (status == 'new')}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore issues related to this option</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring issues related to this option</a>
	{{/if}}
	</div>
</div>
</div>
</script>


<script type="text/x-jquery-template" id="issueTmpl_diskSpace">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Space remaining:</th><td>${data.spaceLeft}</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignoring all disk space alerts{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if (status == 'new')}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore disk space alerts</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring disk space alerts</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_easyPassword">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Issue first detected:</th><td>${timeAgo} ago.</td></tr>
		<tr><th>Login name:</th><td>${data.user_login}</td></tr>
		<tr><th>User email:</th><td>${data.user_email}</td></tr>
		<tr><th>Full name:</th><td>${data.first_name} ${data.last_name}</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}Ignored until user changes password{{/if}}
			{{if status == 'ignoreP' }}Ignoring this user's weak passwords{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong>
		<a target="_blank" href="${data.editUserLink}">Edit this user</a>
	</div>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this weak password</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore all this user's weak passwords</a>
	{{/if}}
	{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_commentBadURL">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Author</th><td>${data.author}</td></tr>
		<tr><th>Bad URL:</th><td><strong class="wfWarn">${data.badURL}</strong></td></tr>
		<tr><th>Posted on:</th><td>${data.commentDate}</td></tr>
		{{if data.isMultisite}}
		<tr><th>Multisite Blog ID:</th><td>${data.blog_id}</td></tr>
		<tr><th>Multisite Blog Domain:</th><td>${data.domain}</td></tr>
		<tr><th>Multisite Blog Path:</th><td>${data.path}</td></tr>
		{{/if}}
		<tr><th>Severity:</th><td>Critical</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' || status == 'ignoreC' }}Ignored{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="WfIssueOptions">
		<strong>Tools:</strong>
		<a target="_blank" href="${data.editCommentLink}">Edit this ${data.type}</a>
	</div>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this ${data.type}</a>
	{{/if}}
	{{if status == 'ignoreC' || status == 'ignoreP'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this ${data.type}</a>
	{{/if}}
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_postBadTitle">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Title:</th><td><strong class="wfWarn">${data.postTitle}</strong></td></tr>
		<tr><th>Posted on:</th><td>${data.postDate}</td></tr>
		{{if data.isMultisite}}
		<tr><th>Multisite Blog ID:</th><td>${data.blog_id}</td></tr>
		<tr><th>Multisite Blog Domain:</th><td>${data.domain}</td></tr>
		<tr><th>Multisite Blog Path:</th><td>${data.path}</td></tr>
		{{/if}}
		<tr><th>Severity:</th><td>Critical</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This bad title will be ignored in this ${data.type}.{{/if}}
			{{if status == 'ignoreP' }}This post won't be scanned for bad titles.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong> 
		<a target="_blank" href="${data.editPostLink}">Edit this ${data.type}</a>
	</div>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this title in this ${data.type}</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore all dangerous titles in this ${data.type}</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_postBadURL">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		{{if data.isMultisite}}
		<tr><th>Title:</th><td><a href="${data.permalink}" target="_blank">${data.postTitle}</a></td></tr>
		{{else}}
		<tr><th>Title:</th><td><a href="${data.permalink}" target="_blank">${data.postTitle}</a></td></tr>
		{{/if}}
		<tr><th>Bad URL:</th><td><strong class="wfWarn">${data.badURL}</strong></td></tr>
		<tr><th>Posted on:</th><td>${data.postDate}</td></tr>
		{{if data.isMultisite}}
		<tr><th>Multisite Blog ID:</th><td>${data.blog_id}</td></tr>
		<tr><th>Multisite Blog Domain:</th><td>${data.domain}</td></tr>
		<tr><th>Multisite Blog Path:</th><td>${data.path}</td></tr>
		{{/if}}
		<tr><th>Severity:</th><td>Critical</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This bad URL will be ignored in this ${data.type}.{{/if}}
			{{if status == 'ignoreP' }}This post won't be scanned for bad URL's.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong> 
		<a target="_blank" href="${data.editPostLink}">Edit this ${data.type}</a>
	</div>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this bad URL in this ${data.type}</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore all bad URL's in this ${data.type}</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>



<script type="text/x-jquery-template" id="issueTmpl_file">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Filename:</th><td>${data.file}</td></tr>
		{{if ((typeof data.badURL !== 'undefined') && data.badURL)}}
		<tr><th>Bad URL:</th><td><strong class="wfWarn">${data.badURL}</strong></td></tr>
		{{/if}}
		<tr><th>File type:</th><td>{{if data.cType}}${WFAD.ucfirst(data.cType)}{{else}}Not a core, theme or plugin file.{{/if}}</td></tr>
		<tr><th>Issue first detected:</th><td>${timeAgo} ago.</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' }}Permanently ignoring this file{{/if}}
			{{if status == 'ignoreC' }}Ignoring this file until it changes{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong> 
		{{if data.fileExists}}
		<a target="_blank" href="${WFAD.makeViewFileLink(data.file)}">View the file.</a>
		{{/if}}
		{{if data.canFix}}
		<a href="#" onclick="WFAD.restoreFile('${id}'); return false;">Restore the original version of this file.</a>
		{{/if}}
		{{if data.canDelete}}
		<a href="#" onclick="WFAD.deleteFile('${id}'); return false;">Delete this file (can't be undone).</a>
		{{/if}}
		{{if data.canDiff}}
		<a href="${WFAD.makeDiffLink(data)}" target="_blank">See how the file has changed.</a>
		{{/if}}
		{{if data.canFix}}
		<br />&nbsp;<input type="checkbox" class="wfrepairCheckbox" value="${id}" />&nbsp;Select for bulk repair
		{{/if}}
		{{if data.canDelete}}
		<br />&nbsp;<input type="checkbox" class="wfdelCheckbox" value="${id}" />&nbsp;Select for bulk delete
		{{/if}}
	</div>
	<div class="wfIssueOptions">
		{{if status == 'new'}}
			<strong>Resolve:</strong>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
			{{if data.fileExists}}
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore until the file changes.</a>
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Always ignore this file.</a>
			{{else}}
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore missing file</a>
			{{/if}}
				
		{{/if}}
		{{if status == 'ignoreC' || status == 'ignoreP'}}
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue.</a>
		{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_database">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Option Name:</th><td>${data.option_name}</td></tr>
		{{if ((typeof data.badURL !== 'undefined') && data.badURL)}}
		<tr><th>Bad URL:</th><td><strong class="wfWarn">${data.badURL}</strong></td></tr>
		{{/if}}
		<tr><th>Issue first detected:</th><td>${timeAgo} ago.</td></tr>
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreP' }}Permanently ignoring this option{{/if}}
			{{if status == 'ignoreC' }}Ignoring this option until it changes{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
		<strong>Tools:</strong>
		{{if data.optionExists}}
		<a target="_blank" href="${WFAD.makeViewOptionLink(data.option_name, data.site_id)}">View this option.</a>
		{{/if}}
		{{if data.canDelete}}
		<a href="#" onclick="WFAD.deleteDatabaseOption('${id}'); return false;">Delete this option from the database (can't be undone).</a>
		<br />&nbsp;<input type="checkbox" class="wfdelCheckbox" value="${id}" />&nbsp;Select for bulk delete
		{{/if}}
	</div>
	<div class="wfIssueOptions">
		{{if status == 'new'}}
			<strong>Resolve:</strong>
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
			{{if data.optionExists}}
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore until the option changes.</a>
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Always ignore this option.</a>
			{{else}}
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore missing option.</a>
			{{/if}}

		{{/if}}
		{{if status == 'ignoreC' || status == 'ignoreP'}}
			<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue.</a>
		{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_pubBadURLs">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}These bad URLs will be ignored until they change.{{/if}}
			{{if status == 'ignoreP' }}These bad URLs will be permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore these URLs until they change.</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore these URLs permanently</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>


<script type="text/x-jquery-template" id="issueTmpl_pubDomainRedir">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This redirect will be ignored until it changes.{{/if}}
			{{if status == 'ignoreP' }}This redirect is permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreC'); return false;">Ignore this redirect until it changes</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore any redirect like this permanently</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_heartbleed">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This redirect will be ignored until it changes.{{/if}}
			{{if status == 'ignoreP' }}This redirect is permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore this problem</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_checkSpamIP">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This redirect will be ignored until it changes.{{/if}}
			{{if status == 'ignoreP' }}This redirect is permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore this problem</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>
<script type="text/x-jquery-template" id="issueTmpl_checkGSB">
	<div>
		<div class="wfIssue">
			<h2>${shortMsg}</h2>
			<p>
			<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
				{{if ((typeof data.badURL !== 'undefined') && data.badURL)}}
				<tr><th>Bad URL:</th><td><strong class="wfWarn">${data.badURL}</strong></td></tr>
				{{/if}}
				<tr><th>Issue first detected:</th><td>${timeAgo} ago.</td></tr>
				<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
				<tr><th>Status</th><td>
						{{if status == 'new' }}New{{/if}}
						{{if status == 'ignoreC' }}This issue will be ignored until it changes.{{/if}}
						{{if status == 'ignoreP' }}This issue is permanently ignored.{{/if}}
					</td></tr>
			</table>
			</p>
			<p>
				{{html longMsg}}
			</p>
			<div class="wfIssueOptions">
				{{if status == 'new'}}
				<strong>Resolve:</strong>
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore this problem</a>
				{{/if}}
				{{if status == 'ignoreP' || status == 'ignoreC'}}
				<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
				{{/if}}
			</div>
		</div>
	</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_spamvertizeCheck">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This redirect will be ignored until it changes.{{/if}}
			{{if status == 'ignoreP' }}This redirect is permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong> 
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore this problem</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>

<script type="text/x-jquery-template" id="issueTmpl_suspiciousAdminUsers">
<div>
<div class="wfIssue">
	<h2>${shortMsg}</h2>
	<p>
		<table border="0" class="wfIssue" cellspacing="0" cellpadding="0">
		<tr><th>Severity:</th><td>{{if severity == '1'}}Critical{{else}}Warning{{/if}}</td></tr>
		<tr><th>Status</th><td>
			{{if status == 'new' }}New{{/if}}
			{{if status == 'ignoreC' }}This issue will be ignored until it changes.{{/if}}
			{{if status == 'ignoreP' }}This issue is permanently ignored.{{/if}}
		</td></tr>
		</table>
	</p>
	<p>
		{{html longMsg}}
	</p>
	<div class="wfIssueOptions">
	{{if status == 'new'}}
		<strong>Resolve:</strong>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">I have fixed this issue</a>
		<a href="#" onclick="WFAD.deleteAdminUser('${id}'); return false;">Delete this user</a>
		<a href="#" onclick="WFAD.revokeAdminUser('${id}'); return false;">Revoke all capabilities from this user</a>
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'ignoreP'); return false;">Ignore this problem</a>
	{{/if}}
	{{if status == 'ignoreP' || status == 'ignoreC'}}
		<a href="#" onclick="WFAD.updateIssueStatus('${id}', 'delete'); return false;">Stop ignoring this issue</a>
	{{/if}}
	</div>
</div>
</div>
</script>




<script type="text/x-jquery-template" id="wfNoScanYetTmpl">
<div>
	<table class="wfSummaryParent" cellpadding="0" cellspacing="0">
	<tr><th class="wfHead">Your first scan is starting now</th></tr>
	<tr><td>
		<table class="wfSC1"  cellpadding="0" cellspacing="0">
		<tr><td>
			Your first Wordfence scan should be automatically starting now
			and you will see the scan details in the "Activity Log" above in a few seconds.
		</td></tr>
		<tr><td>
			<div class="wordfenceScanButton"><button type="button" id="wfStartScanButton2" class="wfStartScanButton button-primary">Start a Wordfence Scan</button></div>
		</td></tr>
		</table>
	</td>
	</tr></table>
</div>
</script>


<script type="text/x-jquery-template" id="wfWelcomeContent1">
<div>
<h3>Welcome to Wordfence</h3>
<p>
	Wordfence is a robust and complete security system and performance enhancer for WordPress. It protects your WordPress site
	from security threats and keeps you off Google's SEO black-list by providing a firewall, brute force protection, continuous scanning and many other security enhancements. 
</p>
<p>
	Wordfence also detects if there are any security problems on 
	your site or if there has been an intrusion and will alert you via email. 
	Wordfence can also help repair hacked sites, even if you don't have a backup of your site.
</p>
</div>
</script>
<script type="text/x-jquery-template" id="wfWelcomeContent2">
<div>
<h3>How Wordfence is different</h3>
<p><strong>Powered by our Cloud Servers</strong></p>
<p>
	Wordfence is not just a standalone plugin for WordPress. It is part of Feedjit Inc. and is powered by our cloud scanning servers based at our
	data center in Seattle, Washington in the USA. On these servers we keep an updated mirror of every version of WordPress ever released
	and every version of every plugin and theme ever released into the WordPress repository. That allows us to
	do an integrity check on your core files, plugins and themes. It also means that when we detect they have changed, we can show you the
	changes and we can give you the option to repair any corrupt files. Even if you don't have a backup of that file.
</p>
<p><strong>Keeping you off Google's SEO Black-List</strong></p>
<p>
	We also maintain a real-time copy of the Google Safe Browsing list (the GSB) and use it to scan all your files, posts, pages and comments for dangerous URL's.
	If you accidentally link to a URL on the GSB, your site is often black-listed by Google and removed from search results. 
	The GSB is constantly changing, so constant scanning of all your content is needed to keep you safe and off Google's SEO black-list.
</p>
<p><strong>Scans for back-doors, malware, viruses and other threats</strong></p>
<p>
	Wordfence also maintains an updated threat and malware signature database which we use to scan your site for intrusions, malware, backdoors and more.
</p>
</div>
</script>
<script type="text/x-jquery-template" id="wfWelcomeContent3">
<div>
<h3>How to use Wordfence</h3>
<strong><p>Start with a Scan</p></strong>
<p>
	Using Wordfence is simple. Start by doing a scan. 
	Once the scan is complete, a list of issues will appear at the bottom of this page. Work through each issue one at a time. If you know an 
	issue is not a security problem, simply choose to ignore it. When you click "ignore" it will be moved to the list of ignored issues.
</p>
<strong><p>Use the tools we provide</p></strong>
<p>
	You'll notice that with each issue we provide tools to help you repair problems you may find. For example, if a core file has been modified
	you can view how it has been changed, view the whole file or repair the file. If we find a back-door a hacker has left behind, we give
	you the option to delete the file. Using these tools is an essential part of the diagnostic and cleaning process if you have been hacked.
</p>
<p>
	Repair each security problem that you find. You may have to fix a weak password that we detected, upgrade a theme or plugin, delete a comment that
	contains an unsafe URL and so on. Once you're done, start another scan and your site should come back with no security issues.
</p>
<strong><p>Regular scheduled scans keep your site safe</p></strong>
<p>
	Once you've done your initial scan and cleanup, Wordfence will automatically scan your site once a day.
	If you would like to scan your site more frequently or control when Wordfence does a scan, upgrade to the 
	paid version of Wordfence which includes other features like country blocking.
</p>
</div>
</script>

<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$scanner = wfScanner::shared();
$scanOptions = $scanner->scanOptions();

$backPage = new wfPage(wfPage::PAGE_SCAN);
if (isset($_GET['source']) && wfPage::isValidPage($_GET['source'])) {
	$backPage = new wfPage($_GET['source']);
}
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Scanner Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
			
			//Hash-based option block linking
			if (window.location.hash) {
				var hashes = window.location.hash.split('#');
				var hash = hashes[hashes.length - 1];
				var block = $('.wf-block[data-persistence-key="' + hash + '"]');
				if (block) {
					if (!block.hasClass('wf-active')) {
						block.find('.wf-block-content').slideDown({
							always: function() {
								block.addClass('wf-active');
								$('html, body').animate({
									scrollTop: block.offset().top - 100
								}, 1000);
							}
						});

						WFAD.ajax('wordfence_saveDisclosureState', {name: block.data('persistenceKey'), state: true}, function() {});
					}
					else {
						$('html, body').animate({
							scrollTop: block.offset().top - 100
						}, 1000);
					}
					history.replaceState('', document.title, window.location.pathname + window.location.search);
				}
			}
		});
	})(jQuery);
</script>
<div class="wf-options-controls">
	<div class="wf-row">
		<div class="wf-col-xs-12">
			<?php
			echo wfView::create('options/block-controls', array(
				'backLink' => $backPage->url(),
				'backLabelHTML' => sprintf(__('<span class="wf-hidden-xs">Back to </span>%s', 'wordfence'), $backPage->label()),
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_SCANNER,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default Scan settings? This will undo any custom changes you have made to the options on this page.', 'wordfence'),
			))->render();
			?>
		</div>
	</div>
</div>
<div class="wf-options-controls-spacer"></div>
<?php
if (wfOnboardingController::shouldShowAttempt3()) {
	echo wfView::create('onboarding/banner')->render();
}
?>
<div class="wrap wordfence">
	<div class="wf-container-fluid">
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wp-header-end"></div>
			</div>
		</div>
		<div class="wf-row">
			<div class="<?php echo wfStyle::contentClasses(); ?>">
				<div id="wf-scan-options" class="wf-fixed-tab-content">
					<?php
					echo wfView::create('common/section-title', array(
						'title' => __('Scan Options and Scheduling', 'wordfence'),
						'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN),
						'helpLabelHTML' => __('Learn more<span class="wf-hidden-xs"> about Scanning</span>', 'wordfence'),
						'showIcon' => true,
					))->render();
					?>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-active">
								<div class="wf-block-content">
									<ul class="wf-block-list wf-block-list-horizontal wf-block-list-nowrap wf-scanner-coverage">
										<li>
											<?php
											if (function_exists('network_admin_url') && is_multisite()) { $optionsURL = network_admin_url('admin.php?page=WordfenceScan&subpage=scan_options'); }
											else { $optionsURL = admin_url('admin.php?page=WordfenceScan&subpage=scan_options'); }
											echo wfView::create('common/status-detail', array(
												'id' => 'wf-scanner-type',
												'percentage' => $scanner->scanTypeStatus(),
												'activeColor' => (!$scanner->isEnabled() ? '#ececec' : null /* automatic */),
												'title' => __('Scan Type: ', 'wordfence') . wfScanner::displayScanType($scanner->scanType()),
												'subtitle' => wfScanner::displayScanTypeDetail($scanner->scanType()),
												'link' => $optionsURL,
												'linkLabel' => null,
												'statusTitle' => __('Scan Status', 'wordfence'),
												'statusList' => $scanner->scanTypeStatusList(),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_STATUS_OVERALL),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('common/status-detail', array(
												'id' => 'wf-scanner-malware-type',
												'percentage' => $scanner->signatureMode() == wfScanner::SIGNATURE_MODE_PREMIUM ? 1.0 : 0.7,
												'activeColor' => (!$scanner->isEnabled() ? '#ececec' : null /* automatic */),
												'title' => __('Malware Signatures: ', 'wordfence') . ($scanner->signatureMode() == wfScanner::SIGNATURE_MODE_PREMIUM ? __('Premium', 'wordfence') : __('Community', 'wordfence')),
												'subtitle' => ($scanner->signatureMode() == wfScanner::SIGNATURE_MODE_PREMIUM ? __('Signatures updated in real-time', 'wordfence') : __('Signature updates delayed by 30 days', 'wordfence')),
												'link' => 'https://www.wordfence.com/gnl1scanUpgrade/wordfence-signup/',
												'linkLabel' => null,
												'statusTitle' => __('Malware Signatures Status', 'wordfence'),
												'statusList' => $scanner->signatureMode() == wfScanner::SIGNATURE_MODE_PREMIUM ? array() : array(array(
													'percentage' => 0.30,
													'title'      => __('Enable Premium Scan Signatures.', 'wordfence'),
												)),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_STATUS_MALWARE),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('common/status-detail', array(
												'id' => 'wf-scanner-reputation',
												'percentage' => $scanner->reputationStatus(),
												'activeColor' => (!$scanner->isEnabled() ? '#ececec' : null /* automatic */),
												'title' => __('Reputation Checks', 'wordfence'),
												'subtitle' => __('Check spam &amp; spamvertising blacklists', 'wordfence'),
												'link' => $optionsURL . '#wf-scanner-options-general',
												'linkLabel' => null,
												'statusTitle' => __('Reputation Check Status', 'wordfence'),
												'statusList' => $scanner->reputationStatusList(),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_STATUS_REPUTATION),
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('wf-scanner-options-schedule') ? ' wf-active' : '') ?>" data-persistence-key="wf-scanner-options-schedule">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Scan Scheduling', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<?php
										echo wfView::create('scanner/scan-scheduling', array(
											'scanner' => $scanner,
										))->render();
										?>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end scan schedule -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-always-active">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Basic Scan Type Options', 'wordfence'); ?></strong>
										</div>
									</div>
								</div>
								<div class="wf-block-content">
									<?php
									echo wfView::create('scanner/scan-type', array(
										'scanner' => $scanner,
									))->render();
									?>
								</div>
							</div>
						</div>
					</div> <!-- end scan type -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('wf-scanner-options-general') ? ' wf-active' : '') ?>" data-persistence-key="wf-scanner-options-general"> 
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('General Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
									<?php
									$options = array(
										array('key' => 'scansEnabled_checkGSB', 'label' => __('Check if this website is on a domain blacklist', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CHECK_SITE_BLACKLISTED), 'premium' => true, 'subtitleHTML' => __('<em>Reputation check</em>', 'wordfence')),
										array('key' => 'spamvertizeCheck', 'label' => __('Check if this website is being "Spamvertised"', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CHECK_SITE_SPAMVERTIZED), 'premium' => true, 'subtitleHTML' => __('<em>Reputation check</em>', 'wordfence')),
										array('key' => 'checkSpamIP', 'label' => __('Check if this website IP is generating spam', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CHECK_IP_SPAMMING), 'premium' => true, 'subtitleHTML' => __('<em>Reputation check</em>', 'wordfence')),
										array('key' => 'scansEnabled_checkHowGetIPs', 'label' => __('Scan for misconfigured How does Wordfence get IPs', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CHECK_MISCONFIGURED_HOW_GET_IPS)),
										array('key' => 'scansEnabled_checkReadableConfig', 'label' => __('Scan for publicly accessible configuration, backup, or log files', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_PUBLIC_CONFIG)),
										array('key' => 'scansEnabled_suspectedFiles', 'label' => __('Scan for publicly accessible quarantined files', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_PUBLIC_QUARANTINED)),
										array('key' => 'scansEnabled_core', 'label' => __('Scan core files against repository versions for changes', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CORE_CHANGES)),
										array('key' => 'scansEnabled_themes', 'label' => __('Scan theme files against repository versions for changes', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_THEME_CHANGES)),
										array('key' => 'scansEnabled_plugins', 'label' => __('Scan plugin files against repository versions for changes', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_PLUGIN_CHANGES)),
										array('key' => 'scansEnabled_coreUnknown', 'label' => __('Scan wp-admin and wp-includes for files not bundled with WordPress', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_UNKNOWN_CORE)),
										array('key' => 'scansEnabled_malware', 'label' => __('Scan for signatures of known malicious files', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_MALWARE_HASHES)),
										array('key' => 'scansEnabled_fileContents', 'label' => __('Scan file contents for backdoors, trojans and suspicious code', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_MALWARE_SIGNATURES)),
										array('key' => 'scansEnabled_fileContentsGSB', 'label' => __('Scan file contents for malicious URLs', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_MALWARE_URLS)),
										array('key' => 'scansEnabled_posts', 'label' => __('Scan posts for known dangerous URLs and suspicious content', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_POST_URLS)),
										array('key' => 'scansEnabled_comments', 'label' => __('Scan comments for known dangerous URLs and suspicious content', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_COMMENT_URLS)),
										array('key' => 'scansEnabled_suspiciousOptions', 'label' => __('Scan WordPress core, plugin, and theme options for known dangerous URLs and suspicious content', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_MALWARE_OPTIONS)),
										array('key' => 'scansEnabled_oldVersions', 'label' => __('Scan for out of date, abandoned, and vulnerable plugins, themes, and WordPress versions', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_UPDATES)),
										array('key' => 'scansEnabled_suspiciousAdminUsers', 'label' => __('Scan for admin users created outside of WordPress', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_UNKNOWN_ADMINS)),
										array('key' => 'scansEnabled_passwds', 'label' => __('Check the strength of passwords', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_PASSWORD_STRENGTH)),
										array('key' => 'scansEnabled_diskSpace', 'label' => __('Monitor disk space', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_DISK_SPACE)),
										array('key' => 'scansEnabled_dns', 'label' => __('Scan for unauthorized DNS changes', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_DNS_CHANGES)),
										array('key' => 'other_scanOutside', 'label' => __('Scan files outside your WordPress installation', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_OUTSIDE_WORDPRESS)),
										array('key' => 'scansEnabled_scanImages', 'label' => __('Scan images, binary, and other files as if they were executable', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_IMAGES_EXECUTABLE)),
										array('key' => 'scansEnabled_highSense', 'label' => __('Enable HIGH SENSITIVITY scanning (may give false positives)', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_HIGH_SENSITIVITY)),
									);
									foreach ($options as $o):
									?>
										<li>
											<?php
											if (isset($o['view'])) {
												echo wfView::create($o['view'], array(
													'optionName' => $o['key'],
													'value' => wfConfig::get($o['key']) ? 1 : 0,
													'title' => $o['label'],
													'helpLink' => $o['helpLink'],
													'premium' => isset($o['premium']) && $o['premium'],
												))->render();
											}
											else {
												echo wfView::create('options/option-toggled', array(
													'optionName' => $o['key'],
													'enabledValue' => 1,
													'disabledValue' => 0,
													'value' => wfConfig::get($o['key']) ? 1 : 0,
													'title' => $o['label'],
													'subtitleHTML' => isset($o['subtitleHTML']) ? $o['subtitleHTML'] : null, 
													'helpLink' => $o['helpLink'],
													'premium' => isset($o['premium']) && $o['premium'],
												))->render();
											}
											?>
										</li>
									<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end general options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('wf-scanner-options-performance') ? ' wf-active' : '') ?>" data-persistence-key="wf-scanner-options-performance"> 
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Performance Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<?php
										$options = array(
											array('key' => 'lowResourceScansEnabled', 'label' => __('Use low resource scanning (reduces server load by lengthening the scan duration)', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_LOW_RESOURCE)),
											array('key' => 'scan_maxIssues', 'label' => __('Limit the number of issues sent in the scan results email', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_LIMIT_ISSUES), 'view' => 'options/option-text', 'parameters' => array('subtitle' => __('0 or empty means unlimited issues will be sent', 'wordfence'))),
											array('key' => 'scan_maxDuration', 'label' => __('Time limit that a scan can run in seconds', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_OVERALL_TIME_LIMIT), 'view' => 'options/option-text', 'parameters' => array('subtitle' => sprintf(__('0 or empty means the default of %s will be used', 'wordfence'), wfUtils::makeDuration(WORDFENCE_DEFAULT_MAX_SCAN_TIME)))),
											array('key' => 'maxMem', 'label' => __('How much memory should Wordfence request when scanning', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_MEMORY_LIMIT), 'view' => 'options/option-text', 'parameters' => array('subtitle' => __('Memory size in megabytes', 'wordfence'))),
											array('key' => 'maxExecutionTime', 'label' => __('Maximum execution time for each scan stage ', 'wordfence'), 'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_STAGE_TIME_LIMIT), 'view' => 'options/option-text', 'parameters' => array('subtitle' => sprintf(__('Blank for default. Must be greater than %d and 10-20 or higher is recommended for most servers', 'wordfence'), intval(WORDFENCE_SCAN_MIN_EXECUTION_TIME) - 1))),
										);
										foreach ($options as $o):
											?>
											<li>
												<?php
												if (isset($o['view']) && $o['view'] == 'options/option-text') {
													if (!isset($o['parameters'])) { $o['parameters'] = array(); }
													echo wfView::create($o['view'], array_merge(array(
														'textOptionName' => $o['key'],
														'textValue' => wfConfig::get($o['key']),
														'title' => $o['label'],
														'helpLink' => $o['helpLink'],
													), $o['parameters']))->render();
												}
												else {
													echo wfView::create('options/option-toggled', array(
														'optionName' => $o['key'],
														'enabledValue' => 1,
														'disabledValue' => 0,
														'value' => wfConfig::get($o['key']) ? 1 : 0,
														'title' => $o['label'],
														'helpLink' => $o['helpLink'],
														'disabled' => isset($o['disabled']) ? $o['disabled'] : false,
													))->render();
												}
												?>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end performance options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('wf-scanner-options-custom') ? ' wf-active' : '') ?>" data-persistence-key="wf-scanner-options-custom">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Advanced Scan Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'scan_exclude',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('scan_exclude')),
												'title' => __('Exclude files from scan that match these wildcard patterns (one per line)', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_EXCLUDE_PATTERNS),
												'noSpacer' => true,
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'scan_include_extra',
												'textValue' => wfConfig::get('scan_include_extra'),
												'title' => __('Additional scan signatures (one per line)', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_SCAN_OPTION_CUSTOM_MALWARE_SIGNATURES),
												'noSpacer' => true,
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end custom scan options -->
				</div> <!-- end wf-scan-options block -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>
<script type="application/javascript">
	(function($) {
		$(function() {
			var updatePendingCircles = function() {
				$('#circle-wf-scanner-type, #circle-wf-scanner-reputation').wfCircularProgress({pendingOverlay: Object.keys(WFAD.pendingChanges).length > 0});
			};
			var coalescingUpdateTimer = false;

			$('.wf-option').on('change', function() {
				clearTimeout(coalescingUpdateTimer);
				coalescingUpdateTimer = setTimeout(updatePendingCircles, 100);
			});
		});
	})(jQuery);
</script>

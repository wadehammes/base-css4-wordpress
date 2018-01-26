<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$waf = wfWAF::getInstance();
$d = new wfDashboard(); unset($d->countriesNetwork);
$firewall = new wfFirewall();
$config = $waf->getStorageEngine();
$wafURL = wfPage::pageURL(wfPage::PAGE_FIREWALL);
$wafConfigURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend');
$wafRemoveURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#removeAutoPrepend');
/** @var array $wafData */

$backPage = new wfPage(wfPage::PAGE_FIREWALL);
if (isset($_GET['source']) && wfPage::isValidPage($_GET['source'])) {
	$backPage = new wfPage($_GET['source']);
}
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Firewall Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
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
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_FIREWALL,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default Firewall settings? This will undo any custom changes you have made to the options on this page. If you have manually disabled any rules or added any custom whitelisted URLs, those changes will not be overwritten.', 'wordfence'),
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
		<?php
		if (function_exists('network_admin_url') && is_multisite()) {
			$firewallURL = network_admin_url('admin.php?page=WordfenceWAF#top#waf');
			$blockingURL = network_admin_url('admin.php?page=WordfenceWAF#top#blocking');
		}
		else {
			$firewallURL = admin_url('admin.php?page=WordfenceWAF#top#waf');
			$blockingURL = admin_url('admin.php?page=WordfenceWAF#top#blocking');
		}
		?>
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wp-header-end"></div>
			</div>
		</div>
		<div class="wf-row">
			<div class="<?php echo wfStyle::contentClasses(); ?>">
				<div id="waf-options" class="wf-fixed-tab-content">
					<?php
					echo wfView::create('common/section-title', array(
						'title' => __('Firewall Options', 'wordfence'),
						'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF),
						'helpLabelHTML' => __('Learn more<span class="wf-hidden-xs"> about the Firewall</span>', 'wordfence'),
						'showIcon' => true,
					))->render();
					?>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-active">
								<div class="wf-block-content">
									<ul class="wf-block-list wf-block-list-horizontal wf-block-list-nowrap wf-waf-coverage">
										<li>
											<?php
											if (function_exists('network_admin_url') && is_multisite()) { $optionsURL = network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options'); }
											else { $optionsURL = admin_url('admin.php?page=WordfenceWAF&subpage=waf_options'); }
											echo wfView::create('common/status-detail', array(
												'id' => 'waf-coverage',
												'percentage' => $firewall->wafStatus(),
												'activeColor' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? '#ececec' : null /* automatic */),
												'title' => __('Web Application Firewall', 'wordfence'),
												'subtitle' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? __('Currently in Learning Mode', 'wordfence') : __('Stops Complex Attacks', 'wordfence')),
												'link' => $optionsURL,
												'linkLabel' => null,
												'statusTitle' => __('Web Application Firewall Status', 'wordfence'),
												'statusList' => $firewall->wafStatusList(),
												'statusExtra' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? wfView::create('waf/status-tooltip-learning-mode')->render() : ''),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_STATUS_OVERALL),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('common/status-detail', array(
												'id' => 'waf-rules',
												'percentage' => $firewall->ruleStatus(),
												'activeColor' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? '#ececec' : null /* automatic */),
												'title' => __('Firewall Rules: ', 'wordfence') . ($firewall->ruleMode() == wfFirewall::RULE_MODE_PREMIUM ? __('Premium', 'wordfence') : __('Community', 'wordfence')),
												'subtitle' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? __('Currently in Learning Mode', 'wordfence') : ($firewall->ruleMode() == wfFirewall::RULE_MODE_PREMIUM ? __('Rules updated in real-time', 'wordfence') : __('Rule updates delayed by 30 days', 'wordfence'))),
												'link' => 'https://www.wordfence.com/gnl1wafUpgrade/wordfence-signup/',
												'linkLabel' => null,
												'linkNewWindow' => true,
												'statusTitle' => __('Firewall Rules Status', 'wordfence'),
												'statusList' => $firewall->wafStatusList('rules'),
												'statusExtra' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? wfView::create('waf/status-tooltip-learning-mode')->render() : ''),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_STATUS_RULES),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('common/status-detail', array(
												'id' => 'waf-blacklist',
												'percentage' => $firewall->blacklistStatus(),
												'title' => __('Real-Time IP Blacklist: ', 'wordfence') . ($firewall->blacklistMode() == wfFirewall::BLACKLIST_MODE_ENABLED ? __('Enabled', 'wordfence') : __('Disabled', 'wordfence')),
												'subtitle' => __('Blocks requests from known malicious IPs', 'wordfence'),
												'link' => (($firewall->ruleMode() == wfFirewall::RULE_MODE_PREMIUM && $firewall->blacklistMode() == wfFirewall::BLACKLIST_MODE_DISABLED) ? network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#waf-options-advanced') : 'https://www.wordfence.com/gnl1wafUpgrade/wordfence-signup/'),
												'linkLabel' => null,
												'linkNewWindow' => !($firewall->ruleMode() == wfFirewall::RULE_MODE_PREMIUM && $firewall->blacklistMode() == wfFirewall::BLACKLIST_MODE_DISABLED),
												'statusTitle' => __('Blacklist Status', 'wordfence'),
												'statusList' => $firewall->wafStatusList('blacklist'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_STATUS_BLACKLIST),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('common/status-detail', array(
												'id' => 'waf-brute',
												'percentage' => $firewall->bruteForceStatus(),
												'title' => __('Brute Force Protection', 'wordfence') . ($firewall->bruteForceStatus() == 0 ? __(': Disabled', 'wordfence') : ''),
												'subtitle' => __('Stops Password Guessing Attacks', 'wordfence'),
												'link' => network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#waf-options-bruteforce'),
												'linkLabel' => null,
												'statusTitle' => __('Brute Force Protection Status', 'wordfence'),
												'statusList' => $firewall->bruteForceStatusList(),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_STATUS_BRUTE_FORCE),
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
							<div class="wf-block wf-always-active">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Basic Firewall Options', 'wordfence'); ?></strong>
										</div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list wf-block-list-horizontal">
										<li class="wf-flex-vertical wf-flex-align-left wf-flex-full-width">
											<h3><?php esc_html_e('Web Application Firewall Status', 'wordfence'); ?></h3>
											<?php if ($firewall->isSubDirectoryInstallation()): ?>
											<p class="wf-no-top"><?php printf(__('You are currently running the Wordfence Web Application Firewall from another WordPress installation. Please <a href="%s">click here</a> to configure the Firewall to run correctly on this site.', 'wordfence'), esc_attr(network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend'))); ?></p>
											<?php else: ?>
											<p class="wf-no-top">
												<?php $wafStatus = $firewall->firewallMode(); ?>
												<span id="wafStatus-enabled-description" class="wafStatus-description"<?php if ($wafStatus != wfFirewall::FIREWALL_MODE_ENABLED) { echo ' style="display: none;"'; } ?>><strong><?php _e('Enabled and Protecting:', 'wordfence'); ?></strong> <?php _e('In this mode, the Wordfence Web Application Firewall is actively blocking requests matching known attack patterns and is actively protecting your site from attackers.', 'wordfence'); ?></span>
												<span id="wafStatus-learning-mode-description" class="wafStatus-description"<?php if ($wafStatus != wfFirewall::FIREWALL_MODE_LEARNING) { echo ' style="display: none;"'; } ?>><strong><?php _e('Learning Mode:', 'wordfence'); ?></strong> <?php printf(__('When you first install the Wordfence Web Application Firewall, it will be in learning mode. This allows Wordfence to learn about your site so that we can understand how to protect it and how to allow normal visitors through the firewall. We recommend you let Wordfence learn for a week before you enable the firewall. <a href="%s" target="_blank" rel="noopener noreferrer">Learn More</a>', 'wordfence'), wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_LEARNING_MODE)); ?></span>
												<span id="wafStatus-disabled-description" class="wafStatus-description"<?php if ($wafStatus != wfFirewall::FIREWALL_MODE_DISABLED) { echo ' style="display: none;"'; } ?>><strong><?php _e('Disabled:', 'wordfence'); ?></strong> <?php _e('In this mode, the Wordfence Web Application Firewall is functionally turned off and does not run any of its rules or analyze the request in any way.', 'wordfence'); ?></span>
											</p>
											<p class="wf-no-top wf-add-bottom">
												<select id="input-wafStatus" data-original-value="<?php echo esc_attr($wafStatus); ?>" name="wafStatus" class="wf-form-control"<?php echo !WFWAF_ENABLED ? ' disabled' : '' ?>>
													<option<?php echo $wafStatus == wfFirewall::FIREWALL_MODE_ENABLED ? ' selected' : '' ?> class="wafStatus-enabled" value="enabled"><?php _e('Enabled and Protecting', 'wordfence'); ?></option>
													<option<?php echo $wafStatus == wfFirewall::FIREWALL_MODE_LEARNING ? ' selected' : '' ?> class="wafStatus-learning-mode" value="learning-mode"><?php _e('Learning Mode', 'wordfence'); ?></option>
													<option<?php echo $wafStatus == wfFirewall::FIREWALL_MODE_DISABLED ? ' selected' : '' ?> class="wafStatus-disabled" value="disabled"><?php _e('Disabled', 'wordfence'); ?></option>
												</select>
												<script type="application/javascript">
													(function($) {
														$(function() {
															$('#input-wafStatus').select2({
																minimumResultsForSearch: -1,
																width: '200px'
															}).on('change', function() {
																var select = $(this);
																var value = select.val();
																var container = $($(this).data('select2').$container);
																container.removeClass('wafStatus-enabled wafStatus-learning-mode wafStatus-disabled')
																	.addClass('wafStatus-' + value);
																
																$('.wafStatus-description').hide();
																$('#wafStatus-' + value + '-description').show();
																if (value == 'learning-mode') {
																	$('#waf-learning-mode-grace-period').show();
																}
																else {
																	$('#waf-learning-mode-grace-period').hide();
																}
																
																var originalValue = select.data('originalValue');
																if (originalValue == value) {
																	delete WFAD.pendingChanges['wafStatus'];
																}
																else {
																	WFAD.pendingChanges['wafStatus'] = value;
																}

																WFAD.updatePendingChanges();
															}).val(<?php echo json_encode($wafStatus) ?>).triggerHandler('change');

															$('#waf-learning-mode-grace-period .wf-datetime').datetimepicker({
																timeFormat: 'hh:mmtt z'
															}).each(function() {
																var el = $(this);
																if (el.attr('data-value')) {
																	el.datetimepicker('setDate', new Date(el.attr('data-value') * 1000));
																}
															}).on('change', function() {
																var value = Math.floor($(this).datetimepicker('getDate').getTime() / 1000);
																var originalValue = $('#input-learningModeGracePeriod').data('originalValue');
																if (originalValue == value) {
																	delete WFAD.pendingChanges['learningModeGracePeriod'];
																}
																else {
																	WFAD.pendingChanges['learningModeGracePeriod'] = $(this).val();
																}
																WFAD.updatePendingChanges();
															});

															$('#waf-learning-mode-grace-period .wf-option-checkbox').on('click', function(e) {
																e.preventDefault();
																e.stopPropagation();

																var originalValue = $(this).data('originalValue');
																var value = originalValue;
																var isActive = $(this).hasClass('wf-checked');
																if (isActive) {
																	$(this).removeClass('wf-checked');
																	$('#waf-learning-mode-grace-period .wf-datetime').attr('disabled', true);
																	value = 0;
																}
																else {
																	$(this).addClass('wf-checked');
																	$('#waf-learning-mode-grace-period .wf-datetime').attr('disabled', false);
																	value = 1;

																	if (!$('#input-learningModeGracePeriod').val()) {
																		var date = new Date();
																		date.setDate(date.getDate() + 7);
																		$('#input-learningModeGracePeriod').datetimepicker('setDate', date);
																	}
																}
																
																if (originalValue == value) {
																	delete WFAD.pendingChanges['learningModeGracePeriodEnabled'];
																}
																else {
																	WFAD.pendingChanges['learningModeGracePeriodEnabled'] = value;
																}

																WFAD.updatePendingChanges();
															});

															$(window).on('wfOptionsReset', function() {
																$('#input-wafStatus').val($('#input-wafStatus').data('originalValue')).trigger('change');
																$('#waf-learning-mode-grace-period .wf-option-checkbox').each(function() {
																	var originalValue = $(this).data('originalValue');
																	$(this).toggleClass('wf-checked', !!originalValue);
																	$('#waf-learning-mode-grace-period .wf-datetime').attr('disabled', !originalValue);
																});
																$('.wf-datetime').each(function() {
																	var el = $(this);
																	if (el.attr('data-value')) {
																		el.datetimepicker('setDate', new Date(el.attr('data-value') * 1000));
																	}
																	else {
																		el.val('');
																	}
																})
															});
														});
													})(jQuery);
												</script>
											</p>
											<div id="waf-learning-mode-grace-period" class="wf-add-bottom" style="display: none;"><div class="waf-learning-mode wf-option-checkbox<?php echo $config->getConfig('learningModeGracePeriodEnabled') ? ' wf-checked' : ''; ?>" data-original-value="<?php echo $config->getConfig('learningModeGracePeriodEnabled') ? 1 : 0; ?>"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div><span> <?php _e('Automatically enable on', 'wordfence'); ?> </span><input type="text" name="learningModeGracePeriod" id="input-learningModeGracePeriod" class="wf-datetime wf-form-control" placeholder="Enabled until..." data-value="<?php echo esc_attr($config->getConfig('learningModeGracePeriod') ? (int) $config->getConfig('learningModeGracePeriod') : '') ?>" data-original-value="<?php echo esc_attr($config->getConfig('learningModeGracePeriod') ? (int) $config->getConfig('learningModeGracePeriod') : '') ?>"<?php echo $config->getConfig('learningModeGracePeriodEnabled') ? '' : ' disabled'; ?>></div>
											<?php endif; ?>
										</li>
										<li class="wf-flex-vertical wf-flex-align-left">
											<h3><?php esc_html_e('Protection Level', 'wordfence'); ?></h3>
											<?php if ($firewall->protectionMode() == wfFirewall::PROTECTION_MODE_EXTENDED && !$firewall->isSubDirectoryInstallation()): ?>
												<p class="wf-no-top"><strong><?php _e('Extended Protection:', 'wordfence'); ?></strong> <?php _e('All PHP requests will be processed by the firewall prior to running.', 'wordfence'); ?></p>
												<p><?php printf(__('If you\'re moving to a new host or a new installation location, you may need to temporarily disable extended protection to avoid any file not found errors. Use this action to remove the configuration changes that enable extended protection mode or you can <a href="%s" target="_blank" rel="noopener noreferrer">remove them manually</a>.', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_FIREWALL_WAF_REMOVE_MANUALLY)); ?></p>
												<p class="wf-no-top"><a class="wf-btn wf-btn-default" href="#" id="wf-waf-uninstall"><?php _e('Remove Extended Protection', 'wordfence'); ?></a></p>
											<?php elseif ($firewall->isSubDirectoryInstallation()): ?>
												<p class="wf-no-top"><strong><?php _e('Existing WAF Installation Detected:', 'wordfence'); ?></strong> <?php _e('You are currently running the Wordfence Web Application Firewall from another WordPress installation. Please configure the firewall to run correctly on this site.', 'wordfence'); ?></p>
												<p><a class="wf-btn wf-btn-primary" href="#" id="wf-waf-install"><?php _e('Optimize the Wordfence Firewall', 'wordfence'); ?></a></p>
											<?php else: ?>
												<p class="wf-no-top"><strong><?php _e('Basic WordPress Protection:', 'wordfence'); ?></strong> <?php _e('The plugin will load as a regular plugin after WordPress has been loaded, and while it can block many malicious requests, some vulnerable plugins or WordPress itself may run vulnerable code before all plugins are loaded.', 'wordfence'); ?></p>
												<p><a class="wf-btn wf-btn-primary" href="#" id="wf-waf-install"><?php _e('Optimize the Wordfence Firewall', 'wordfence'); ?></a></p>
											<?php endif; ?>
											<script type="application/javascript">

												(function($) {
													$(function() {
														var validateContinue = function() {
															var backupsAvailable = $('.wf-waf-backups:visible').data('backups');
															var backupsDownloaded = $('#wf-waf-server-config').data('backups');

															var matchCount = 0;
															backupsAvailable = backupsAvailable.sort();
															backupsDownloaded = backupsDownloaded.sort();
															for (var i = 0; i < backupsAvailable.length; i++) {
																for (var n = 0; n < backupsDownloaded.length; n++) {
																	if (backupsAvailable[i] == backupsDownloaded[n]) {
																		matchCount++;
																	}
																}
															}
															
															$('#wf-waf-install-continue, #wf-waf-uninstall-continue').toggleClass('wf-disabled', matchCount != backupsAvailable.length);
														};
														
														var installUninstallResponseHandler = function(action, res) {
															var modal = $('.wf-modal-title').closest('.wf-modal');
															if (res.needsCredentials) {
																var replacement = $(res.html);
																modal.replaceWith(replacement);
																modal = replacement;

																var form = replacement.find('#request-filesystem-credentials-form').closest('form');
																form.find('input[type="submit"]').attr('type', 'hidden');
																form.on('submit', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.ajax(action, form.serialize(), function(res) {
																		installUninstallResponseHandler(action, res);
																	});
																});
																modal.find('#wf-waf-modal-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();
																	
																	form.trigger('submit');
																});
																$.wfcolorbox.resize();
															}
															else if (res.credentialsFailed || res.installationFailed || res.uninstallationFailed) {
																var replacement = $(res.html);
																modal.replaceWith(replacement);
																modal = replacement;
																modal.find('#wf-waf-modal-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.colorboxClose();
																});
																$.wfcolorbox.resize();
															}
															else if (res.uninstallationWaiting) {
																var replacement = $(res.html);
																modal.replaceWith(replacement);
																modal = replacement;
																modal.find('#wf-waf-modal-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.colorboxClose();
																}).addClass('wf-disabled');
																
																var timeout = res.timeout; //In seconds
																setTimeout(function() {
																	modal.find('#wf-waf-modal-continue').removeClass('wf-disabled');
																	var payload = {serverConfiguration: res.serverConfiguration, iniModified: 1};
																	if (res.credentials) {
																		payload['credentials'] = res.credentials;
																		payload['credentialsSignature'] = res.credentialsSignature;
																	}
																	WFAD.ajax(action, payload, function(res) {
																		installUninstallResponseHandler(action, res);
																	});
																}, (timeout + 10) * 1000);
																$.wfcolorbox.resize();
															}
															else if (res.ok) {
																var replacement = $(res.html);
																modal.replaceWith(replacement);
																modal = replacement;
																modal.find('#wf-waf-modal-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	window.location.reload(true);
																});
																$.wfcolorbox.resize();
															}
															else {
																WFAD.colorboxModal((WFAD.isSmallScreen ? '300px' : '400px'), 'Error During Setup', res.errorMsg);
															}
														};
														
														var installUninstallHandler = function(html) {
															WFAD.colorboxHTML('800px', html, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																$('#wf-waf-server-config').data('backups', []);
																$('.wf-waf-backup-download').on('click', function(e) {
																	var backupIndex = parseInt($(this).data('backupIndex'));
																	var backupsAvailable = $(this).closest('.wf-waf-backups').data('backups');
																	var backupsDownloaded = $('#wf-waf-server-config').data('backups');
																	var found = false;
																	for (var i = 0; i < backupsDownloaded.length; i++) {
																		if (backupsDownloaded[i] == backupsAvailable[backupIndex]) {
																			found = true;
																			break;
																		}
																	}
																	
																	if (!found) {
																		backupsDownloaded.push(backupsAvailable[backupIndex]);
																		$('#wf-waf-server-config').data('backups', backupsDownloaded);
																		validateContinue();
																	}
																});
																
																$('#wf-waf-server-config').select2();

																$('#wf-waf-include-prepend > li').each(function(index, element) {
																	$(element).on('click', function(e) {
																		e.preventDefault();
																		e.stopPropagation();

																		var control = $(this).closest('.wf-switch');
																		var value = $(this).data('optionValue');

																		control.find('li').each(function() {
																			$(this).toggleClass('wf-active', value == $(this).data('optionValue'));
																		});
																	});
																});
																
																var nginxNotice = $('.wf-nginx-waf-config');
																$('#wf-waf-server-config').on('change', function() {
																	var el = $(this);
																	$('.wf-waf-backups').hide();
																	$('.wf-waf-backups-' + el.val().replace(/[^a-z0-9\-]/i, '')).show();

																	if (nginxNotice.length) { //Install only
																		if (el.val() == 'nginx') {
																			nginxNotice.fadeIn(400, function () {
																				$.wfcolorbox.resize();
																			});
																		}
																		else {
																			nginxNotice.fadeOut(400, function () {
																				$.wfcolorbox.resize();
																			});
																		}
																	}
																	else {
																		$.wfcolorbox.resize();
																	}

																	validateContinue();
																}).triggerHandler('change');
																
																$('#wf-waf-install-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();
																	
																	var serverConfiguration = $('#wf-waf-server-config').val();
																	var currentAutoPrepend = $('#wf-waf-include-prepend .wf-active').data('optionValue');
																	
																	WFAD.ajax('wordfence_installAutoPrepend', {serverConfiguration: serverConfiguration, currentAutoPrepend: currentAutoPrepend}, function(res) {
																		installUninstallResponseHandler('wordfence_installAutoPrepend', res);
																	});
																});

																$('#wf-waf-uninstall-continue').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	var serverConfiguration = $('#wf-waf-server-config').val();

																	WFAD.ajax('wordfence_uninstallAutoPrepend', {serverConfiguration: serverConfiguration}, function(res) {
																		installUninstallResponseHandler('wordfence_uninstallAutoPrepend', res);
																	});
																});
															}});
														};

														$('#wf-waf-install').on('click', function(e) {
															e.preventDefault();
															e.stopPropagation();

															var installer = $('#wafTmpl_install').tmpl();
															var installerHTML = $("<div />").append(installer).html();
															installUninstallHandler(installerHTML);
														});

														$('#wf-waf-uninstall').on('click', function(e) {
															e.preventDefault();
															e.stopPropagation();

															var uninstaller = $('#wafTmpl_uninstall').tmpl();
															var uninstallerHTML = $("<div />").append(uninstaller).html();
															installUninstallHandler(uninstallerHTML);
														});

														if (window.location.hash) {
															var hashes = window.location.hash.split('#');
															for (var i = 0; i < hashes.length; i++) {
																if (hashes[i] == 'configureAutoPrepend') {
																	$('#wf-waf-install').trigger('click');
																	history.replaceState('', document.title, window.location.pathname + window.location.search);
																}
																else if (hashes[i] == 'removeAutoPrepend') {
																	$('#wf-waf-uninstall').trigger('click');
																	history.replaceState('', document.title, window.location.pathname + window.location.search);
																}
															}
														}

														$(window).on('hashchange', function () {
															var hashes = window.location.hash.split('#');
															for (var i = 0; i < hashes.length; i++) {
																if (hashes[i] == 'configureAutoPrepend') {
																	$('#wf-waf-install').trigger('click');
																	history.replaceState('', document.title, window.location.pathname + window.location.search);
																}
																else if (hashes[i] == 'removeAutoPrepend') {
																	$('#wf-waf-uninstall').trigger('click');
																	history.replaceState('', document.title, window.location.pathname + window.location.search);
																}
															}
														});
													});
												})(jQuery);
											</script>
										</li>
										<li class="wf-flex-vertical wf-flex-align-left">
											<h3><?php esc_html_e('Real-Time IP Blacklist', 'wordfence'); ?></h3>
											<?php if ($firewall->ruleMode() == wfFirewall::RULE_MODE_COMMUNITY): ?>
												<p class="wf-no-top"><strong><?php _e('Premium Feature:', 'wordfence'); ?></strong> <?php _e('This feature blocks all traffic from IPs with a high volume of recent malicious activity using Wordfence\'s real-time blacklist.', 'wordfence'); ?></p>
												<p><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1blacklistUpgrade/wordfence-signup/#premium-order-form" target="_blank" rel="noopener noreferrer"><?php _e('Upgrade to Premium', 'wordfence'); ?></a>&nbsp;&nbsp;<a class="wf-btn wf-btn-callout-subtle wf-btn-default" href="https://www.wordfence.com/gnl1blacklistLearn/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php _e('Learn More', 'wordfence'); ?></a></p>
											<?php elseif ($firewall->isSubDirectoryInstallation()): ?>
												<p class="wf-no-top"><?php printf(__('You are currently running the Wordfence Web Application Firewall from another WordPress installation. Please <a href="%s">click here</a> to configure the Firewall to run correctly on this site.', 'wordfence'), esc_attr(network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend'))); ?></p>
											<?php else: ?>
												<p class="wf-no-top"><?php _e('This feature blocks all traffic from IPs with a high volume of recent malicious activity using Wordfence\'s real-time blacklist.', 'wordfence'); ?></p>
												<div class="wf-option wf-option-switch wf-padding-add-bottom" data-option-name="disableWAFBlacklistBlocking" data-original-value="<?php echo $config->getConfig('disableWAFBlacklistBlocking') ? '1': '0'; ?>">
													<ul class="wf-switch">
														<?php
														$states = array(
															array('value' => '1', 'label' => __('Disabled', 'wordfence')),
															array('value' => '0', 'label' => __('Enabled', 'wordfence')),
														);
														
														foreach ($states as $s): 
														?>
															<li<?php if ($s['value'] == ($config->getConfig('disableWAFBlacklistBlocking') ? '1': '0')) { echo ' class="wf-active"'; } ?> data-option-value="<?php echo esc_attr($s['value']); ?>"><?php echo esc_html($s['label']); ?></li> 
														<?php endforeach; ?>
													</ul>
												</div>
											<?php endif; ?>
											<script type="application/javascript">

												(function($) {
													$(function() {
														
													});
												})(jQuery);
											</script>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end firewall status -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('waf-options-advanced') ? ' wf-active' : '') ?>" data-persistence-key="waf-options-advanced">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Advanced Firewall Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'disableWAFIPBlocking',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => $config->getConfig('disableWAFIPBlocking') ? 1 : 0,
												'title' => __('Delay IP and Country blocking until after WordPress and plugins have loaded (only process firewall rules early)', 'wordfence'),
												'subtitle' => ($firewall->isSubDirectoryInstallation() ? __('You are currently running the WAF from another WordPress installation. This option can be changed once you configure the firewall to run correctly on this site.', 'wordfence') : ''),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_DELAY_BLOCKING),
												'disabled' => $firewall->isSubDirectoryInstallation(),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'whitelisted',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('whitelisted')),
												'title' => __('Whitelisted IP addresses that bypass all rules', 'wordfence'),
												'subtitleHTML' => __('Whitelisted IPs must be separated by commas or placed on separate lines. You can specify ranges using the following formats: 127.0.0.1/24, 127.0.0.[1-100], or 127.0.0.1 - 127.0.1.100<br/>Wordfence automatically whitelists <a href="http://en.wikipedia.org/wiki/Private_network" target="_blank" rel="noopener noreferrer">private networks</a> because these are not routable on the public Internet.', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_WHITELISTED_IPS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'bannedURLs',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('bannedURLs')),
												'title' => __('Immediately block IPs that access these URLs', 'wordfence'),
												'subtitle' => __('Separate multiple URLs with commas or place them on separate lines. Asterisks are wildcards, but use with care. If you see an attacker repeatedly probing your site for a known vulnerability you can use this to immediately block them. All URLs must start with a "/" without quotes and must be relative. e.g. /badURLone/, /bannedPage.html, /dont-access/this/URL/, /starts/with-*', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_IMMEDIATELY_BLOCK_URLS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'wafAlertWhitelist',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('wafAlertWhitelist')),
												'title' => __('Ignored IP addresses for Wordfence Web Application Firewall alerting', 'wordfence'),
												'subtitle' => __('Ignored IPs must be separated by commas or placed on separate lines. These addresses will be ignored from any alerts about increased attacks and can be used to ignore things like standalone website security scanners.', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_IGNORED_ALERT_IPS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rules', array(
												'firewall' => $firewall,
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end advanced firewall options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('waf-options-bruteforce') ? ' wf-active' : '') ?>" data-persistence-key="waf-options-bruteforce">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Brute Force Protection', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-switch', array(
												'optionName' => 'loginSecurityEnabled',
												'value' => wfConfig::get('loginSecurityEnabled') ? '1': '0',
												'titleHTML' => '<strong>' . __('Enable brute force protection', 'wordfence') . '</strong>',
												'subtitle' => __('This option enables all "Brute Force Protection" options, including two-factor authentication, strong password enforcement, and invalid login throttling. You can modify individual options below.', 'wordfence'),
												'states' => array(
													array('value' => '0', 'label' => __('Off', 'wordfence')),
													array('value' => '1', 'label' => __('On', 'wordfence')),
												),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_ENABLE_LOGIN_SECURITY),
												'noSpacer' => true,
												'alignment' => 'wf-right',
											))->render();
											?>
										</li>
										<li>
											<?php
											$breakpoints = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 20, 30, 40, 50, 100, 200, 500);
											$options = array();
											foreach ($breakpoints as $b) {
												$options[] = array('value' => $b, 'label' => $b);
											}
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'loginSec_maxFailures',
												'selectOptions' => $options,
												'selectValue' => wfConfig::get('loginSec_maxFailures'),
												'title' => __('Lock out after how many login failures', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_LOCK_OUT_FAILURE_COUNT),
											))->render();
											?>
										</li>
										<li>
											<?php
											$breakpoints = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 20, 30, 40, 50, 100, 200, 500);
											$options = array();
											foreach ($breakpoints as $b) {
												$options[] = array('value' => $b, 'label' => $b);
											}
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'loginSec_maxForgotPasswd',
												'selectOptions' => $options,
												'selectValue' => wfConfig::get('loginSec_maxForgotPasswd'),
												'title' => __('Lock out after how many forgot password attempts', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_LOCK_OUT_FORGOT_PASSWORD_COUNT),
											))->render();
											?>
										</li>
										<li>
											<?php
											$breakpoints = array(5, 10, 30, 60, 120, 240, 360, 720, 1440);
											$options = array();
											foreach ($breakpoints as $b) {
												$options[] = array('value' => $b, 'label' => wfUtils::makeDuration($b * 60));
											}
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'loginSec_countFailMins',
												'selectOptions' => $options,
												'selectValue' => wfConfig::getInt('loginSec_countFailMins'),
												'title' => __('Count failures over what time period', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_COUNT_TIME_PERIOD),
											))->render();
											?>
										</li>
										<li>
											<?php
											$breakpoints = array(5, 10, 30, 60, 120, 240, 360, 720, 1440, 2880, 7200, 14400, 28800, 43200, 86400);
											$options = array();
											foreach ($breakpoints as $b) {
												$options[] = array('value' => $b, 'label' => wfUtils::makeDuration($b * 60));
											}
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'loginSec_lockoutMins',
												'selectOptions' => $options,
												'selectValue' => wfConfig::getInt('loginSec_lockoutMins'),
												'title' => __('Amount of time a user is locked out', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_LOCKOUT_DURATION),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'loginSec_lockInvalidUsers',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('loginSec_lockInvalidUsers') ? 1 : 0,
												'title' => __('Immediately lock out invalid usernames', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_IMMEDIATELY_LOCK_OUT_INVALID_USERS),
											))->render();
											?>
										</li>
										<li>
											<?php
											$blacklist = wfConfig::get('loginSec_userBlacklist', '');
											if (empty($blacklist)) {
												$users = array();
											}
											else {
												$users = explode("\n", wfUtils::cleanupOneEntryPerLine($blacklist));
											}
											
											echo wfView::create('options/option-token', array(
												'tokenOptionName' => 'loginSec_userBlacklist',
												'tokenValue' => $users,
												'title' => __('Immediately block the IP of users who try to sign in as these usernames', 'wordfence'),
												'subtitle' => __('Hit enter to add a username', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_IMMEDIATELY_BLOCK_USERS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-label', array(
												'titleHTML' => '<strong>' . __('Additional Options', 'wordfence') . '</strong>',
												'noSpacer' => true,
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled-select', array(
												'toggleOptionName' => 'loginSec_strongPasswds_enabled',
												'enabledToggleValue' => 1,
												'disabledToggleValue' => 0,
												'toggleValue' => !!wfConfig::get('loginSec_strongPasswds_enabled') ? 1 : 0,
												'selectOptionName' => 'loginSec_strongPasswds',
												'selectOptions' => array(array('value' => 'pubs', 'label' => __('Force admins and publishers to use strong passwords (recommended)', 'wordfence')), array('value' => 'all', 'label' => __('Force all members to use strong passwords', 'wordfence'))),
												'selectValue' => wfConfig::get('loginSec_strongPasswds'),
												'title' => __('Enforce strong passwords', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_ENFORCE_STRONG_PASSWORDS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'loginSec_maskLoginErrors',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('loginSec_maskLoginErrors') ? 1 : 0,
												'title' => __('Don\'t let WordPress reveal valid users in login errors', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_MASK_LOGIN_ERRORS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'loginSec_blockAdminReg',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('loginSec_blockAdminReg') ? 1 : 0,
												'title' => __('Prevent users registering \'admin\' username if it doesn\'t exist', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_PREVENT_ADMIN_REGISTRATION),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'loginSec_disableAuthorScan',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('loginSec_disableAuthorScan') ? 1 : 0,
												'title' => __('Prevent discovery of usernames through \'/?author=N\' scans, the oEmbed API, and the WordPress REST API', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_PREVENT_AUTHOR_SCAN),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'other_blockBadPOST',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('other_blockBadPOST') ? 1 : 0,
												'title' => __('Block IPs who send POST requests with blank User-Agent and Referer', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_BLOCK_BAD_POST),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'other_pwStrengthOnUpdate',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('other_pwStrengthOnUpdate') ? 1 : 0,
												'title' => __('Check password strength on profile update', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_CHECK_PASSWORD),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'other_WFNet',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('other_WFNet') ? 1 : 0,
												'title' => __('Participate in the Real-Time WordPress Security Network', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_PARTICIPATE_WFSN),
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end brute force protection -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('waf-options-ratelimiting') ? ' wf-active' : '') ?>" data-persistence-key="waf-options-ratelimiting">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Rate Limiting', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-switch', array(
												'optionName' => 'firewallEnabled',
												'value' => wfConfig::get('firewallEnabled') ? '1': '0',
												'title' => __('Enable Rate Limiting and Advanced Blocking', 'wordfence'),
												'subtitle' => __('NOTE: This checkbox enables ALL blocking/throttling functions including IP, country and advanced blocking, and the "Rate Limiting Rules" below.', 'wordfence'),
												'states' => array(
													array('value' => '0', 'label' => __('Off', 'wordfence')),
													array('value' => '1', 'label' => __('On', 'wordfence')),
												),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_ENABLE_ADVANCED_BLOCKING),
												'noSpacer' => true,
												'alignment' => 'wf-right',
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'blockFakeBots',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('blockFakeBots') ? 1 : 0,
												'title' => __('Immediately block fake Google crawlers', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_IMMEDIATELY_BLOCK_FAKE_GOOGLE),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'neverBlockBG',
												'selectOptions' => array(
													array('value' => 'neverBlockVerified', 'label' => __('Verified Google crawlers have unlimited access to this site', 'wordfence')),
													array('value' => 'neverBlockUA', 'label' => __('Anyone claiming to be Google has unlimited access', 'wordfence')),
													array('value' => 'treatAsOtherCrawlers', 'label' => __('Treat Google like any other Crawler', 'wordfence')),
												),
												'selectValue' => wfConfig::get('neverBlockBG'),
												'title' => __('How should we treat Google\'s crawlers', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_GOOGLE_ACTION),
											))->render();
											?>
										</li>
										<?php
										$rateOptions = array(
											array('value' => 'DISABLED', 'label' => __('Unlimited', 'wordfence')),
											array('value' => 1, 'label' => sprintf(__('%d per minute', 'wordfence'), 1)),
											array('value' => 2, 'label' => sprintf(__('%d per minute', 'wordfence'), 2)),
											array('value' => 3, 'label' => sprintf(__('%d per minute', 'wordfence'), 3)),
											array('value' => 4, 'label' => sprintf(__('%d per minute', 'wordfence'), 4)),
											array('value' => 5, 'label' => sprintf(__('%d per minute', 'wordfence'), 5)),
											array('value' => 10, 'label' => sprintf(__('%d per minute', 'wordfence'), 10)),
											array('value' => 15, 'label' => sprintf(__('%d per minute', 'wordfence'), 15)),
											array('value' => 30, 'label' => sprintf(__('%d per minute', 'wordfence'), 30)),
											array('value' => 60, 'label' => sprintf(__('%d per minute', 'wordfence'), 60)),
											array('value' => 120, 'label' => sprintf(__('%d per minute', 'wordfence'), 120)),
											array('value' => 240, 'label' => sprintf(__('%d per minute', 'wordfence'), 240)),
											array('value' => 480, 'label' => sprintf(__('%d per minute', 'wordfence'), 480)),
											array('value' => 960, 'label' => sprintf(__('%d per minute', 'wordfence'), 960)),
											array('value' => 1920, 'label' => sprintf(__('%d per minute', 'wordfence'), 1920)),
										);
										$actionOptions = array(
											array('value' => 'throttle', 'label' => __('throttle it', 'wordfence')),
											array('value' => 'block', 'label' => __('block it', 'wordfence')),
										);
										?>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'maxGlobalRequests_enabled',
												'toggleValue' => !!wfConfig::get('maxGlobalRequests_enabled') ? 1 : 0,
												'rateOptionName' => 'maxGlobalRequests',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('maxGlobalRequests'),
												'actionOptionName' => 'maxGlobalRequests_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('maxGlobalRequests_action'),
												'title' => __('If anyone\'s requests exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_ANY),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'maxRequestsCrawlers_enabled',
												'toggleValue' => !!wfConfig::get('maxRequestsCrawlers_enabled') ? 1 : 0,
												'rateOptionName' => 'maxRequestsCrawlers',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('maxRequestsCrawlers'),
												'actionOptionName' => 'maxRequestsCrawlers_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('maxRequestsCrawlers_action'),
												'title' => __('If a crawler\'s page views exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_CRAWLER),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'max404Crawlers_enabled',
												'toggleValue' => !!wfConfig::get('max404Crawlers_enabled') ? 1 : 0,
												'rateOptionName' => 'max404Crawlers',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('max404Crawlers'),
												'actionOptionName' => 'max404Crawlers_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('max404Crawlers_action'),
												'title' => __('If a crawler\'s pages not found (404s) exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_CRAWLER_404),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'maxRequestsHumans_enabled',
												'toggleValue' => !!wfConfig::get('maxRequestsHumans_enabled') ? 1 : 0,
												'rateOptionName' => 'maxRequestsHumans',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('maxRequestsHumans'),
												'actionOptionName' => 'maxRequestsHumans_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('maxRequestsHumans_action'),
												'title' => __('If a human\'s page views exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_HUMAN),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'max404Humans_enabled',
												'toggleValue' => !!wfConfig::get('max404Humans_enabled') ? 1 : 0,
												'rateOptionName' => 'max404Humans',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('max404Humans'),
												'actionOptionName' => 'max404Humans_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('max404Humans_action'),
												'title' => __('If a human\'s pages not found (404s) exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_HUMAN_404),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('waf/option-rate-limit', array(
												'toggleOptionName' => 'maxScanHits_enabled',
												'toggleValue' => !!wfConfig::get('maxScanHits_enabled') ? 1 : 0,
												'rateOptionName' => 'maxScanHits',
												'rateOptions' => $rateOptions,
												'rateValue' => wfConfig::get('maxScanHits'),
												'actionOptionName' => 'maxScanHits_action',
												'actionOptions' => $actionOptions,
												'actionValue' => wfConfig::get('maxScanHits_action'),
												'title' => __('If 404s for known vulnerable URLs exceed', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_RATE_LIMIT_ANY_404),
											))->render();
											?>
										</li>
										<li>
											<?php
											$breakpoints = array(60, 300, 1800, 3600, 7200, 21600, 43200, 86400, 172800, 432000, 864000, 2592000);
											$options = array();
											foreach ($breakpoints as $b) {
												$options[] = array('value' => $b, 'label' => wfUtils::makeDuration($b));
											}
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'blockedTime',
												'selectOptions' => $options,
												'selectValue' => wfConfig::getInt('blockedTime'),
												'title' => __('How long is an IP address blocked when it breaks a rule', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_AUTOMATIC_BLOCK_DURATION),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'allowed404s',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('allowed404s')),
												'title' => __('Whitelisted 404 URLs', 'wordfence'),
												'subtitle' => __('These URL patterns will be excluded from the throttling rules used to limit crawlers.', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_WHITELISTED_404),
											))->render();
											?>
										</li>
									</ul>
									<script type="application/javascript">
										(function($) {
											$(function() {
												$('.wf-option.wf-option-rate-limit > .wf-option-content > ul > li.wf-option-select select').select2({
													minimumResultsForSearch: -1
												}).on('change', function () {
													var optionElement = $(this).closest('.wf-option');
													if ($(this).hasClass('wf-rate-limit-rate')) {
														var option = optionElement.data('rateOption');
														var value = $(this).val();
														
														var originalValue = optionElement.data('originalRateValue');
														if (originalValue == value) {
															delete WFAD.pendingChanges[option];
														}
														else {
															WFAD.pendingChanges[option] = value;
														}
													}
													else if ($(this).hasClass('wf-rate-limit-action')) {
														var option = optionElement.data('actionOption');
														var value = $(this).val();

														var originalValue = optionElement.data('originalActionValue');
														if (originalValue == value) {
															delete WFAD.pendingChanges[option];
														}
														else {
															WFAD.pendingChanges[option] = value;
														}
													}
													
													WFAD.updatePendingChanges();
												}).triggerHandler('change');
											});
										})(jQuery);
									</script>
								</div>
							</div>
						</div>
					</div> <!-- end rate limiting -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('waf-options-whitelisted') ? ' wf-active' : '') ?>" data-persistence-key="waf-options-whitelisted">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Whitelisted URLs', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
									<?php if ($firewall->isSubDirectoryInstallation()): ?>
											<li>
												<p><?php printf(__('You are currently running the Wordfence Web Application Firewall from another WordPress installation. Please <a href="%s">click here</a> to configure the Firewall to run correctly on this site.', 'wordfence'), esc_attr(network_admin_url('admin.php?page=WordfenceWAF&subpage=waf_options#configureAutoPrepend'))); ?></p>
											</li>
									<?php else: ?>
										<li>
											<?php
											echo wfView::create('waf/option-whitelist', array(
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled-multiple', array(
												'options' => array(
													array(
														'name' => 'ajaxWatcherDisabled_front',
														'enabledValue' => 0,
														'disabledValue' => 1,
														'value' => wfConfig::get('ajaxWatcherDisabled_front') ? 1 : 0,
														'title' => __('Front-end Website', 'wordfence'),
													),
													array(
														'name' => 'ajaxWatcherDisabled_admin',
														'enabledValue' => 0,
														'disabledValue' => 1,
														'value' => wfConfig::get('ajaxWatcherDisabled_admin') ? 1 : 0,
														'title' => __('Admin Panel', 'wordfence'),
													),
												),
												'title' => __('Monitor background requests from an administrator\'s web browser for false positives', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_WAF_OPTION_MONITOR_AJAX),
											))->render();
											?>
										</li>
									<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end whitelisted urls -->
				</div> <!-- end waf options block -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>
<script type="application/javascript">
	(function($) {
		WFAD.wafData = <?php echo json_encode($wafData); ?>;
		WFAD.restoreWAFData = JSON.parse(JSON.stringify(WFAD.wafData)); //Copied into wafData when canceling changes

		$(function() {
			WFAD.wafConfigPageRender();

			//Hash-based option block linking
			if (window.location.hash) {
				var hashes = window.location.hash.split('#');
				var hash = hashes[hashes.length - 1];
				var block = $('.wf-block[data-persistence-key="' + hash + '"]');
				if (block.length) {
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

		function whitelistCheckAllVisible() {
			$('.wf-whitelist-bulk-select.wf-option-checkbox').toggleClass('wf-checked', true);
			$('.wf-whitelist-table-bulk-checkbox.wf-option-checkbox').each(function() {
				$(this).toggleClass('wf-checked', $(this).closest('tr').is(':visible'));
			});
		}

		function whitelistUncheckAll() {
			$('.wf-whitelist-bulk-select.wf-option-checkbox').toggleClass('wf-checked', false);
			$('.wf-whitelist-table-bulk-checkbox.wf-option-checkbox').toggleClass('wf-checked', false);
		}

		$(window).on('wordfenceWAFInstallWhitelistEventHandlers', function() {
			//Enabled/Disabled
			$('.wf-whitelist-item-enabled.wf-option-checkbox').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var row = $(this).closest('tr');
					var key = row.data('key');
					var value = $(this).hasClass('wf-checked') ? 1 : 0;
					if (value) {
						$(this).removeClass('wf-checked');
						value = 0;
					}
					else {
						$(this).addClass('wf-checked');
						value = 1;
					}

					WFAD.wafWhitelistedChangeEnabled(key, value);
					WFAD.updatePendingChanges();
				});
			});

			//Header/Footer Bulk Action
			$('.wf-whitelist-bulk-select.wf-option-checkbox').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					if ($(this).hasClass('wf-checked')) {
						$(this).removeClass('wf-checked');
						whitelistUncheckAll();
					}
					else {
						$(this).addClass('wf-checked');
						whitelistCheckAllVisible();
					}
				});
			});

			//Row Bulk Action
			$('.wf-whitelist-table-bulk-checkbox.wf-option-checkbox').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var row = $(this).closest('tr');
					var key = row.data('key');
					var value = $(this).hasClass('wf-checked') ? 1 : 0;
					if (value) {
						$(this).removeClass('wf-checked');
					}
					else {
						$(this).addClass('wf-checked');
					}

					var totalCount = $('.wf-whitelist-table-bulk-checkbox.wf-option-checkbox:visible').length;
					var checkedCount = $('.wf-whitelist-table-bulk-checkbox.wf-option-checkbox.wf-checked:visible').length;
					if (totalCount == 0 || (checkedCount != totalCount)) {
						$('.wf-whitelist-bulk-select.wf-option-checkbox').removeClass('wf-checked');
					}
					else {
						$('.wf-whitelist-bulk-select.wf-option-checkbox').addClass('wf-checked');
					}
				});
			});

			$(window).trigger('wordfenceWAFApplyWhitelistFilter');
		});

		$(window).on('wordfenceWAFApplyWhitelistFilter', function() {
			if (WFAD.wafData.whitelistedURLParams.length == 0) {
				return;
			}

			var filterColumn = $('#whitelist-table-controls select').val();
			var filterValue = $('input[name="filterValue"]').val();
			if (typeof filterValue != 'string' || filterValue.length == 0) {
				$('#waf-whitelisted-urls-wrapper .whitelist-table > tbody > tr[data-index]').show();
			}
			else {
				$('#waf-whitelisted-urls-wrapper .whitelist-table > tbody > tr[data-index]').each(function() {
					var text = $(this).find('td[data-column="' + filterColumn + '"]').text();
					if (text.indexOf(filterValue) > -1) {
						$(this).show();
					}
					else {
						$(this).hide();
					}
				});
			}
		});

		$(window).on('wordfenceWAFConfigPageRender', function() {
			delete WFAD.pendingChanges['wafRules'];

			//Add event handler to rule checkboxes
			$('.wf-rule-toggle.wf-boolean-switch').each(function() {
				$(this).on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					$(this).find('.wf-boolean-switch-handle').trigger('click');
				});

				$(this).find('.wf-boolean-switch-handle').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var control = $(this).closest('.wf-boolean-switch');
					var row = $(this).closest('tr');
					var ruleID = row.data('ruleId');
					var value = control.hasClass('wf-active') ? 1 : 0;
					if (value) {
						control.removeClass('wf-active');
						value = 0;
					}
					else {
						control.addClass('wf-active');
						value = 1;
					}

					var originalValue = row.data('originalValue');
					if (originalValue == value) {
						delete WFAD.pendingChanges['wafRules'][ruleID];
						if (Object.keys(WFAD.pendingChanges['wafRules']).length == 0) {
							delete WFAD.pendingChanges['wafRules']
						}
					}
					else {
						if (!(WFAD.pendingChanges['wafRules'] instanceof Object)) {
							WFAD.pendingChanges['wafRules'] = {};
						}
						WFAD.pendingChanges['wafRules'][ruleID] = value;
					}

					$(control).trigger('change', [false]);
					WFAD.updatePendingChanges();
				});
			});

			//Add event handler to whitelist checkboxes
			$(window).trigger('wordfenceWAFInstallWhitelistEventHandlers');
		});

		$(window).on('wfOptionsReset', function() {
			WFAD.wafData = JSON.parse(JSON.stringify(WFAD.restoreWAFData));
			WFAD.wafConfigPageRender();
		});
	})(jQuery);
</script>
<script type="text/x-jquery-template" id="waf-whitelisted-urls-tmpl">
	<div class="whitelist-table-container">
		<table class="wf-striped-table whitelist-table">
			<thead>
			<tr>
				<th style="width: 2%;text-align: center"><div class="wf-whitelist-bulk-select wf-option-checkbox"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></th>
				<th style="width: 5%;"><?php _e('Enabled', 'wordfence'); ?></th>
				<th><?php _e('URL', 'wordfence'); ?></th>
				<th><?php _e('Param', 'wordfence'); ?></th>
				<th><?php _e('Created', 'wordfence'); ?></th>
				<th><?php _e('Source', 'wordfence'); ?></th>
				<th><?php _e('User', 'wordfence'); ?></th>
				<th><?php _e('IP', 'wordfence'); ?></th>
			</tr>
			</thead>
			{{if whitelistedURLParams.length > 5}}
			<tfoot>
			<tr>
				<th style="width: 2%;text-align: center"><div class="wf-whitelist-bulk-select wf-option-checkbox"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></th>
				<th style="width: 5%;"><?php _e('Enabled', 'wordfence'); ?></th>
				<th><?php _e('URL', 'wordfence'); ?></th>
				<th><?php _e('Param', 'wordfence'); ?></th>
				<th><?php _e('Created', 'wordfence'); ?></th>
				<th><?php _e('Source', 'wordfence'); ?></th>
				<th><?php _e('User', 'wordfence'); ?></th>
				<th><?php _e('IP', 'wordfence'); ?></th>
			</tr>
			{{/if}}
			</tfoot>
			<tbody>
			{{each(idx, whitelistedURLParam) whitelistedURLParams}}
			<tr data-index="${idx}" data-adding="{{if (whitelistedURLParam.adding)}}1{{else}}0{{/if}}" data-key="${whitelistedURLParam.path}|${whitelistedURLParam.paramKey}">
				<td style="text-align: center;"><div class="wf-whitelist-table-bulk-checkbox wf-option-checkbox"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></td>
				<td style="text-align: center;"><div class="wf-whitelist-item-enabled wf-option-checkbox{{if (!whitelistedURLParam.data.disabled)}} wf-checked{{/if}}" data-original-value="{{if (!whitelistedURLParam.data.disabled)}}1{{else}}0{{/if}}"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></td>
				<td data-column="url">
					<input name="replaceWhitelistedPath" type="hidden" value="${whitelistedURLParam.path}">
					<span class="whitelist-display">${WFAD.htmlEscape(WFAD.base64_decode(whitelistedURLParam.path))}</span>
					<input name="whitelistedPath" class="whitelist-edit whitelist-path" type="text"
						   value="${WFAD.htmlEscape(WFAD.base64_decode(whitelistedURLParam.path))}">
				</td>
				<td data-column="param">
					<input name="replaceWhitelistedParam" type="hidden" value="${whitelistedURLParam.paramKey}">
					<span class="whitelist-display">${WFAD.htmlEscape(WFAD.base64_decode(whitelistedURLParam.paramKey))}</span>
					<input name="whitelistedParam" class="whitelist-edit whitelist-param-key"
						   type="text" value="${WFAD.htmlEscape(WFAD.base64_decode(whitelistedURLParam.paramKey))}">
				</td>
				<td>
					{{if (whitelistedURLParam.data.timestamp)}}
					${WFAD.dateFormat((new Date(whitelistedURLParam.data.timestamp * 1000)))}
					{{else}}
					-
					{{/if}}
				</td>
				<td data-column="source">
					{{if (whitelistedURLParam.data.description)}}
					${whitelistedURLParam.data.description}
					{{else}}
					-
					{{/if}}
				</td>
				<td data-column="user">
					{{if (whitelistedURLParam.data.userID)}}
					{{if (whitelistedURLParam.data.username)}}
					${whitelistedURLParam.data.username}
					{{else}}
					${whitelistedURLParam.data.userID}
					{{/if}}
					{{else}}
					-
					{{/if}}
				</td>
				<td data-column="ip">
					{{if (whitelistedURLParam.data.ip)}}
					${whitelistedURLParam.data.ip}
					{{else}}
					-
					{{/if}}
				</td>
			</tr>
			{{/each}}
			{{if (whitelistedURLParams.length == 0)}}
			<tr>
				<td colspan="8"><?php _e('No whitelisted URLs currently set.', 'wordfence'); ?></td>
			</tr>
			{{/if}}
			</tbody>
		</table>
	</div>
</script>
<script type="text/x-jquery-template" id="waf-rules-tmpl">
	<table class="wf-striped-table">
		<thead>
		<tr>
			<th style="width: 5%"></th>
			<th><?php _e('Category', 'wordfence'); ?></th>
			<th><?php _e('Description', 'wordfence'); ?></th>
		</tr>
		</thead>
		<tbody>
		{{each(idx, rule) rules}}
		<tr data-rule-id="${rule.ruleID}" data-original-value="{{if (!disabledRules[rule.ruleID])}}1{{else}}0{{/if}}">
			<td style="text-align: center">
				<div class="wf-rule-toggle wf-boolean-switch{{if (!disabledRules[rule.ruleID])}} wf-active{{/if}}<?php echo ($firewall->isSubDirectoryInstallation() ? ' wf-disabled' : ''); ?>"><a href="#" class="wf-boolean-switch-handle"></a></div>
			</td>
			<td>${rule.category}</td>
			<td>${rule.description}</td>
		</tr>
		{{/each}} 
		{{if (rules.length == 0)}}
		<tr>
			<td colspan="4"><?php _e('No rules currently set.', 'wordfence'); ?> <?php if (!($firewall->protectionMode() == wfFirewall::PROTECTION_MODE_EXTENDED && $firewall->isSubDirectoryInstallation())) { _e('<a href="#" onclick="WFAD.wafUpdateRules();return false;">Click here</a> to pull down the latest from the Wordfence servers.', 'wordfence'); } ?>
			</td>
		</tr>
		{{/if}}
		</tbody>
		<tfoot>
		{{if (ruleCount >= 10)}}
		<tr id="waf-show-all-rules">
			<td class="wf-center" colspan="4"><a href="#" id="waf-show-all-rules-button"><?php _e('SHOW ALL RULES', 'wordfence'); ?></a></td>
		</tr>
		{{/if}}
		</tfoot>
	</table>
</script>
<?php
if ($firewall->protectionMode() == wfFirewall::PROTECTION_MODE_BASIC || ($firewall->protectionMode() == wfFirewall::PROTECTION_MODE_EXTENDED && $firewall->isSubDirectoryInstallation())) {
	echo wfView::create('waf/waf-install', array(
	))->render();
}
else {
	echo wfView::create('waf/waf-uninstall', array(
	))->render();
}
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			var updatePendingCircles = function() {
				$('#circle-waf-coverage, #circle-waf-rules, #circle-waf-blacklist, #circle-waf-brute').wfCircularProgress({pendingOverlay: Object.keys(WFAD.pendingChanges).length > 0});
			};
			var coalescingUpdateTimer = false;
			
			$('.wf-option, .wf-rule-toggle').on('change', function() {
				clearTimeout(coalescingUpdateTimer);
				coalescingUpdateTimer = setTimeout(updatePendingCircles, 100);
			}); 
		});
	})(jQuery);
</script>
<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$dashboardURL = network_admin_url('admin.php?page=Wordfence');
$firewall = new wfFirewall();
$scanner = wfScanner::shared();
$d = new wfDashboard();
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Wordfence Global Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;

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
				'backLink' => $dashboardURL,
				'backLabelHTML' => __('Back<span class="wf-hidden-xs"> to Dashboard</span>', 'wordfence'),
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_GLOBAL,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default global settings? This will undo any custom changes you have made to the options on this page. Your configured license key and alert emails will not be changed.', 'wordfence'),
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
<div class="wrap wordfence" id="wf-global-options">
	<div class="wf-container-fluid">
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
						'title' => __('Wordfence Global Options', 'wordfence'),
						'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTIONS),
						'helpLabelHTML' => __('Learn more<span class="wf-hidden-xs"> about Global Options</span>', 'wordfence'),
						'showIcon' => true,
					))->render();
					?>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-active">
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<ul class="wf-block-list wf-block-list-horizontal wf-block-list-nowrap wf-waf-coverage">
												<li>
													<?php
													echo wfView::create('common/status-detail', array(
														'id' => 'waf-coverage',
														'percentage' => $firewall->overallStatus(),
														'activeColor' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? '#ececec' : null /* automatic */),
														'title' => __('Firewall', 'wordfence'),
														'subtitle' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? __('WAF Currently in Learning Mode', 'wordfence') : __('Protection from known and emerging threats', 'wordfence')),
														'link' => wfPage::pageURL(wfPage::PAGE_FIREWALL_OPTIONS, wfPage::PAGE_DASHBOARD_OPTIONS),
														'linkLabel' => __('Manage Firewall', 'wordfence'),
														'statusTitle' => __('Firewall Status', 'wordfence'),
														'statusList' => $firewall->statusList(),
														'statusExtra' => ($firewall->firewallMode() == wfFirewall::FIREWALL_MODE_LEARNING ? wfView::create('waf/status-tooltip-learning-mode')->render() : ''),
														'helpLink' => __('https://www.wordfence.com/help/dashboard/#dashboard-status', 'wordfence'),
													))->render();
													?>
												</li>
												<li>
													<?php
													echo wfView::create('common/status-detail', array(
														'id' => 'wf-scanner-type',
														'percentage' => $scanner->scanTypeStatus(),
														'activeColor' => (!$scanner->isEnabled() ? '#ececec' : null /* automatic */),
														'title' => __('Scan', 'wordfence'),
														'subtitle' => __('Detection of security issues', 'wordfence'),
														'link' => wfPage::pageURL(wfPage::PAGE_SCAN_OPTIONS, wfPage::PAGE_DASHBOARD_OPTIONS),
														'linkLabel' => __('Manage Scan', 'wordfence'),
														'statusTitle' => __('Scan Status', 'wordfence'),
														'statusList' => $scanner->scanTypeStatusList(),
														'helpLink' => __('https://www.wordfence.com/help/dashboard/#dashboard-status', 'wordfence'),
													))->render();
													?>
												</li>
												<li>
													<?php if (wfConfig::get('hasKeyConflict')): ?>
														<?php
														echo wfView::create('common/status-critical', array(
															'id' => 'wf-premium-alert',
															'title' => __('Premium License Conflict', 'wordfence'),
															'subtitle' => __('License already in use', 'wordfence'),
															'link' => 'https://www.wordfence.com/gnl1manageConflict/manage-wordfence-api-keys/',
															'linkLabel' => __('Reset License', 'wordfence'),
															'linkNewWindow' => true,
														))->render();
														?>
													<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_EXPIRED): ?>
														<?php
														echo wfView::create('common/status-critical', array(
															'id' => 'wf-premium-alert',
															'title' => __('Premium Protection Disabled', 'wordfence'),
															'subtitle' => __('License is expired', 'wordfence'),
															'link' => 'https://www.wordfence.com/gnl1renewExpired/manage-wordfence-api-keys/',
															'linkLabel' => __('Renew License', 'wordfence'),
															'linkNewWindow' => true,
														))->render();
														?>
													<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_FREE || wfConfig::get('keyType') === false): ?>
														<div>
															<p><h3><?php _e('Premium Protection Disabled', 'wordfence'); ?></h3></p>
															<p><?php printf(__('As a free Wordfence user, you are currently using the Community version of the Threat Defense Feed. Premium users are protected by an additional %d firewall rules and malware signatures. Upgrade to Premium today to improve your protection.', 'wordfence'), ($d->tdfPremium - $d->tdfCommunity)); ?></p>
															<p><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1dashboardUpgrade/wordfence-signup/#premium-order-form" target="_blank" rel="noopener noreferrer"><?php _e('Upgrade to Premium', 'wordfence'); ?></a>&nbsp;&nbsp;<a class="wf-btn wf-btn-callout-subtle wf-btn-default" href="https://www.wordfence.com/gnl1dashboardLearn/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php _e('Learn More', 'wordfence'); ?></a></p>
														</div>
													<?php elseif (wfConfig::get('keyExpDays') < 30 && (wfConfig::get('premiumAutoRenew', null) === '0' || wfConfig::get('premiumAutoRenew', null) === 0)): ?>
														<?php
														echo wfView::create('common/status-critical', array(
															'id' => 'wf-premium-alert',
															'title' => __('Premium License Expiring', 'wordfence'),
															'subtitle' => __('Auto-renew is disabled', 'wordfence'),
															'link' => 'https://www.wordfence.com/gnl1renewExpiring/manage-wordfence-api-keys/',
															'linkLabel' => __('Renew License', 'wordfence'),
															'linkNewWindow' => true,
														))->render();
														?>
													<?php elseif (wfConfig::get('keyExpDays') < 30): ?>
														<?php
														if (wfConfig::get('premiumPaymentExpiring')) {
															$title = __('Payment Method Expiring', 'wordfence');
														}
														else if (wfConfig::get('premiumPaymentExpired')) {
															$title = __('Payment Method Expired', 'wordfence');
														}
														else if (wfConfig::get('premiumPaymentMissing')) {
															$title = __('Payment Method Missing', 'wordfence');
														}
														else if (wfConfig::get('premiumPaymentHold')) {
															$title = __('Payment Method Invalid', 'wordfence');
														}
														
														if (isset($title)) {
															$days = floor(((int) wfConfig::get('premiumNextRenew') - time()) / 86400);
															if ($days <= 0) {
																$days = __('today', 'wordfence');
															}
															else if ($days == 1) {
																$days = __('tomorrow', 'wordfence');
															}
															else {
																$days = sprintf(__('in %d days', 'wordfence'), $days);
															}
															
															echo wfView::create('dashboard/status-payment-expiring', array(
																'id' => 'wf-premium-alert',
																'title' => $title,
																'subtitle' => sprintf(__('License renews %s', 'wordfence'), $days),
																'link' => 'https://www.wordfence.com/gnl1renewExpiring/manage-wordfence-api-keys/',
																'linkLabel' => __('Update Payment Method', 'wordfence'),
																'linkNewWindow' => true,
															))->render();
														}
														else {
															$days = floor(((int) wfConfig::get('premiumNextRenew') - time()) / 86400);
															if ($days == 0) {
																$days = __('today', 'wordfence');
															}
															else if ($days == 1) {
																$days = __('in 1 day', 'wordfence');
															}
															else {
																$days = sprintf(__('in %d days', 'wordfence'), $days);
															}
															
															echo wfView::create('dashboard/status-renewing', array(
																'id' => 'wf-premium-alert',
																'title' => __('Premium License Expiring', 'wordfence'),
																'subtitle' => sprintf(__('License renews %s', 'wordfence'), $days),
																'link' => 'https://www.wordfence.com/gnl1reviewExpiring/manage-wordfence-api-keys/',
																'linkLabel' => __('Review Payment Method', 'wordfence'),
																'linkNewWindow' => true,
															))->render();
														}
														?>
													<?php elseif (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_CURRENT): ?>
														<div class="wf-block-labeled-value wf-protection-status wf-protection-status-<?php echo esc_attr($firewall->ruleMode()); ?>">
															<div class="wf-block-labeled-value-value"><i class="wf-fa wf-fa-check" aria-hidden="true"></i></div>
															<div class="wf-block-labeled-value-label"><?php _e('Wordfence Premium Enabled', 'wordfence'); ?></div>
														</div>
													<?php endif; ?>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-license') ? ' wf-active' : '') ?>" data-persistence-key="global-options-license">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Wordfence License', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<ul class="wf-flex-vertical wf-flex-full-width wf-add-top wf-add-bottom">
												<li><strong><?php _e('Your Wordfence License', 'wordfence'); ?></strong></li>
												<li>
													<ul class="wf-option wf-option-text" data-text-option="apiKey" data-original-text-value="<?php echo esc_attr(wfConfig::get('apiKey')); ?>">
														<li class="wf-option-title">
															<?php _e('License Key', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_API_KEY); ?>"  target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a>
														</li>
														<li class="wf-option-text wf-option-full-width wf-no-right">
															<input type="text" value="<?php echo esc_attr(wfConfig::get('apiKey')); ?>" id="wf-license-input">
														</li>
													</ul>
												</li>
												<li>
													<ul class="wf-flex-horizontal wf-flex-vertical-xs wf-flex-full-width">
														<li><strong><?php _e('License Status:', 'wordfence'); ?></strong> 
														<?php
														if (wfConfig::get('hasKeyConflict')) {
															_e('Premium License already in use', 'wordfence');
														}
														else if (wfConfig::get('isPaid')) {
															_e('Premium License Active', 'wordfence');
														}
														else if (wfConfig::get('keyType') == wfAPI::KEY_TYPE_PAID_EXPIRED) {
															_e('Premium License Expired', 'wordfence');
														}
														else {
															_e('Free License Active', 'wordfence');
														}
														?>
														</li>
														<li class="wf-right wf-flex-vertical-xs wf-flex-align-left wf-left-xs wf-padding-add-top-xs" id="wf-license-controls">
															<?php if (wfConfig::get('hasKeyConflict')): ?>
																<a href="#" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-downgrade-license"><?php _e('Downgrade to a free license', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="https://www.wordfence.com/gnl1optMngKysReset/manage-wordfence-api-keys/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php _e('Reset Premium License', 'wordfence'); ?></a>
															<?php elseif (wfConfig::get('keyExpDays') < 30 && wfConfig::get('premiumAutoRenew', null) === '0'): ?>
																<a href="#" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-downgrade-license"><?php _e('Downgrade to a free license', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="https://www.wordfence.com/gnl1optMngKysExpiring/manage-wordfence-api-keys/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php _e('Renew Premium License', 'wordfence'); ?></a>
															<?php elseif (wfConfig::get('keyExpDays') < 30 && (wfConfig::get('premiumPaymentExpiring') || wfConfig::get('premiumPaymentExpired') || wfConfig::get('premiumPaymentMissing') || wfConfig::get('premiumPaymentHold'))): ?>
																<a href="#" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-downgrade-license"><?php _e('Downgrade to a free license', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="https://www.wordfence.com/gnl1optMngKysExpiring/manage-wordfence-api-keys/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php _e('Renew Premium License', 'wordfence'); ?></a>
															<?php elseif (wfConfig::get('isPaid')): ?>
																<a href="#" class="wf-btn wf-btn-default wf-btn-callout-subtle wf-downgrade-license"><?php _e('Downgrade to a free license', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="https://www.wordfence.com/gnl1optMngKysReset/manage-wordfence-api-keys/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-default wf-btn-callout-subtle"><?php _e('Renew Premium License', 'wordfence'); ?></a>
															<?php else: ?>
																<a href="https://www.wordfence.com/gnl1optUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer" class="wf-btn wf-btn-primary wf-btn-callout-subtle"><?php _e('Upgrade to Premium', 'wordfence'); ?></a>
															<?php endif ?>
															<a href="#" class="wf-btn wf-btn-primary wf-btn-callout-subtle" style="display: none;" id="wf-install-license"><?php _e('Install License', 'wordfence'); ?></a>
														</li>
													</ul>

													<script type="application/javascript">
														(function($) {
															$(function() {
																$('#wf-install-license').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.setOption('apiKey', $('#wf-license-input').val(), function() {
																		delete WFAD.pendingChanges['apiKey'];
																		WFAD.updatePendingChanges();
																		window.location.reload(true);
																	});
																});
																
																$('#wf-license-input').on('focus', function() {
																	var field = $(this);
																	setTimeout(function() {
																		field.select();
																	}, 100);
																}).on('change paste keyup', function() {
																	setTimeout(function() {
																		var originalKey = $('#wf-license-input').closest('.wf-option').data('originalTextValue');
																		if (originalKey != $('#wf-license-input').val()) {
																			$('#wf-license-controls a').hide();
																			$('#wf-install-license').show();
																		}
																	}, 100);
																});

																$(window).on('wfOptionsReset', function() {
																	$('#wf-license-controls a').show();
																	$('#wf-install-license').hide();
																});
																
																$('.wf-downgrade-license').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	var prompt = $('#wfTmpl_downgradePrompt').tmpl();
																	var promptHTML = $("<div />").append(prompt).html();
																	WFAD.colorboxHTML('400px', promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																		$('#wf-downgrade-prompt-cancel').on('click', function(e) {
																			e.preventDefault();
																			e.stopPropagation();

																			WFAD.colorboxClose();
																		});

																		$('#wf-downgrade-prompt-downgrade').on('click', function(e) {
																			e.preventDefault();
																			e.stopPropagation();

																			WFAD.ajax('wordfence_downgradeLicense', {}, function(res) {
																				window.location.reload(true);
																			});
																		});
																	}});
																});
															});
														})(jQuery);
													</script>
												</li>
											</ul>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end license options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-general') ? ' wf-active' : '') ?>" data-persistence-key="global-options-general">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('General Wordfence Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											$subtitle = __('Automatically updates Wordfence to the newest version within 24 hours of a new release.', 'wordfence');
											if (!wfConfig::get('other_bypassLitespeedNoabort', false) && getenv('noabort') != '1' && stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
												$subtitle .= '<br><br>';
												$subtitle .= __('<span class="wf-red-dark">Warning:</span> You are running the LiteSpeed web server and Wordfence can\'t determine whether "noabort" is set. Please verify that the environmental variable "noabort" is set for the local site, or the server\'s global External Application Abort is set to "No Abort".', 'wordfence');
												$subtitle .= '<br>';
												$subtitle .= '<a href="' . wfSupportController::esc_supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_LITESPEED_WARNING) . '" target="_blank" rel="noopener noreferrer">' . __('Please read this article in our FAQ to make an important change that will ensure your site stability during an update.', 'wordfence') . '</a>';
											}
											
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'autoUpdate',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('autoUpdate') ? 1 : 0,
												'title' => __('Update Wordfence automatically when a new version is released?', 'wordfence'),
												'subtitleHTML' => $subtitle,
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_AUTOMATIC_UPDATE),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-text', array(
												'textOptionName' => 'alertEmails',
												'textValue' => wfConfig::get('alertEmails'),
												'title' => __('Where to email alerts', 'wordfence'),
												'placeholder' => __('Separate multiple addresses with commas', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_ALERT_EMAILS),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('dashboard/option-howgetips')->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'other_hideWPVersion',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('other_hideWPVersion') ? 1 : 0,
												'title' => __('Hide WordPress version', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_HIDE_VERSION),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'disableCodeExecutionUploads',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('disableCodeExecutionUploads') ? 1 : 0,
												'title' => __('Disable Code Execution for Uploads directory', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_DISABLE_UPLOADS_EXECUTION),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'disableCookies',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('disableCookies') ? 1 : 0,
												'title' => __('Disable Wordfence Cookies', 'wordfence'),
												'subtitle' => __('When enabled, all visits in live traffic will appear to be new visits.', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_DISABLE_COOKIES),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'liveActivityPauseEnabled',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('liveActivityPauseEnabled') ? 1 : 0,
												'title' => __('Pause live updates when window loses focus', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_PAUSE_LIVE_UPDATES),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-text', array(
												'textOptionName' => 'actUpdateInterval',
												'textValue' => wfConfig::get('actUpdateInterval'),
												'title' => __('Update interval in seconds', 'wordfence'),
												'subtitle' => __('Setting higher will reduce browser traffic but slow scan starts, live traffic &amp; status updates.', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_UPDATE_INTERVAL),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'other_bypassLitespeedNoabort',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('other_bypassLitespeedNoabort') ? 1 : 0,
												'title' => __('Bypass the LiteSpeed "noabort" check', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_BYPASS_LITESPEED_CHECK),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'deleteTablesOnDeact',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('deleteTablesOnDeact') ? 1 : 0,
												'title' => __('Delete Wordfence tables and data on deactivation', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_DELETE_DEACTIVATION),
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end general options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-dashboard') ? ' wf-active' : '') ?>" data-persistence-key="global-options-dashboard">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Dashboard Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-subtitle"><?php _e('Options to control the types of notifications you will receive on the Wordfence dashboard', 'wordfence'); ?></div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_updatesNeeded',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_updatesNeeded') ? 1 : 0,
												'title' => __('Updates Needed (Plugin, Theme, or Core)', 'wordfence'),
											))->render();
											?>
										</li>
										<?php if (wfConfig::p()): ?>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_securityAlerts',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_securityAlerts') ? 1 : 0,
												'title' => __('Security Alerts', 'wordfence'),
												'premium' => true,
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_promotions',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_promotions') ? 1 : 0,
												'title' => __('Promotions', 'wordfence'),
												'premium' => true,
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_blogHighlights',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_blogHighlights') ? 1 : 0,
												'title' => __('Blog Highlights', 'wordfence'),
												'premium' => true,
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_productUpdates',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_productUpdates') ? 1 : 0,
												'title' => __('Product Updates', 'wordfence'),
												'premium' => true,
											))->render();
											?>
										</li>
										<?php endif; ?>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'notification_scanStatus',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('notification_scanStatus') ? 1 : 0,
												'title' => __('Scan Status', 'wordfence'),
											))->render();
											?>
										</li>
										<?php if (!wfConfig::p()): ?>
											<li>
												<ul class="wf-option">
													<li class="wf-option-spacer"></li>
													<li class="wf-flex-vertical wf-flex-align-left">
														<p><?php _e('Dashboard notifications will also be displayed for Security Alerts, Promotions, Blog Highlights, and Product Updates. These notifications can be disabled by upgrading to a premium license.', 'wordfence'); ?></p>
														<p class="wf-no-top"><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1dashboardUpgrade/wordfence-signup/#premium-order-form" target="_blank" rel="noopener noreferrer"><?php _e('Upgrade to Premium', 'wordfence'); ?></a>&nbsp;&nbsp;<a class="wf-btn wf-btn-callout-subtle wf-btn-default" href="https://www.wordfence.com/gnl1dashboardLearn/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php _e('Learn More', 'wordfence'); ?></a></p>
													</li>
												</ul>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end dashboard options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-alert') ? ' wf-active' : '') ?>" data-persistence-key="global-options-alert">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Alert Preferences', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_update',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_update') ? 1 : 0,
												'title' => __('Email me when Wordfence is automatically updated', 'wordfence'),
												'subtitle' => __('If you have automatic updates enabled (see above), you\'ll get an email when an update occurs.', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_wordfenceDeactivated',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_wordfenceDeactivated') ? 1 : 0,
												'title' => __('Email me if Wordfence is deactivated', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_critical',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_critical') ? 1 : 0,
												'title' => __('Alert on critical problems', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_warnings',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_warnings') ? 1 : 0,
												'title' => __('Alert on warnings', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_block',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_block') ? 1 : 0,
												'title' => __('Alert when an IP address is blocked', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_loginLockout',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_loginLockout') ? 1 : 0,
												'title' => __('Alert when someone is locked out from login', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'alertOn_lostPasswdForm',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_lostPasswdForm') ? 1 : 0,
												'title' => __('Alert when the "lost password" form is used for a valid user', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled-sub', array(
												'optionName' => 'alertOn_adminLogin',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_adminLogin') ? 1 : 0,
												'title' => __('Alert me when someone with administrator access signs in', 'wordfence'),
												
												'subOptionName' => 'alertOn_firstAdminLoginOnly',
												'subEnabledValue' => 1,
												'subDisabledValue' => 0,
												'subValue' => wfConfig::get('alertOn_firstAdminLoginOnly') ? 1 : 0,
												'subTitle' => __('Only alert me when that administrator signs in from a new device or location', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled-sub', array(
												'optionName' => 'alertOn_nonAdminLogin',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('alertOn_nonAdminLogin') ? 1 : 0,
												'title' => __('Alert me when a non-admin user signs in', 'wordfence'),
												
												'subOptionName' => 'alertOn_firstNonAdminLoginOnly',
												'subEnabledValue' => 1,
												'subDisabledValue' => 0,
												'subValue' => wfConfig::get('alertOn_firstNonAdminLoginOnly') ? 1 : 0,
												'subTitle' => __('Only alert me when that user signs in from a new device or location', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'wafAlertOnAttacks',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('wafAlertOnAttacks') ? 1 : 0,
												'title' => __('Alert me when there\'s a large increase in attacks detected on my site', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-text', array(
												'textOptionName' => 'alert_maxHourly',
												'textValue' => wfConfig::get('alert_maxHourly'),
												'title' => __('Maximum email alerts to send per hour', 'wordfence'),
												'subtitle' => __('0 means unlimited alerts will be sent.', 'wordfence'),
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end alert options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-email-summary') ? ' wf-active' : '') ?>" data-persistence-key="global-options-email-summary">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Activity Report', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-toggled-select', array(
												'toggleOptionName' => 'email_summary_enabled',
												'enabledToggleValue' => 1,
												'disabledToggleValue' => 0,
												'toggleValue' => wfConfig::get('email_summary_enabled') ? 1 : 0,
												'selectOptionName' => 'email_summary_interval',
												'selectOptions' => array(
													array('value' => 'daily', 'label' => __('Once a day', 'wordfence')),
													array('value' => 'weekly', 'label' => __('Once a week', 'wordfence')),
													array('value' => 'monthly', 'label' => __('Once a month', 'wordfence')),
												),
												'selectValue' => wfConfig::get('email_summary_interval'),
												'title' => __('Enable email summary', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-textarea', array(
												'textOptionName' => 'email_summary_excluded_directories',
												'textValue' => wfUtils::cleanupOneEntryPerLine(wfConfig::get('email_summary_excluded_directories')),
												'title' => __('List of directories to exclude from recently modified file list', 'wordfence'),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'email_summary_dashboard_widget_enabled',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('email_summary_dashboard_widget_enabled') ? 1 : 0,
												'title' => __('Enable activity report widget on the WordPress dashboard', 'wordfence'),
											))->render();
											?>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div> <!-- end email summary options -->
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block<?php echo (wfPersistenceController::shared()->isActive('global-options-import') ? ' wf-active' : '') ?>" data-persistence-key="global-options-import">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Import/Export Options', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<ul class="wf-flex-horizontal wf-flex-vertical-xs wf-flex-full-width wf-add-top wf-add-bottom">
												<li><?php _e('Export this site\'s Wordfence options for import on another site', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_EXPORT); ?>"  target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a></li>
												<li class="wf-right wf-left-xs wf-padding-add-top-xs-small">
													<a href="#" class="wf-btn wf-btn-primary wf-btn-callout-subtle" id="wf-export-options"><?php _e('Export<span class="wf-hidden-xs"> Wordfence</span> Options', 'wordfence'); ?></a>
												</li>
											</ul>
										</li>
										<li>
											<ul class="wf-flex-vertical wf-flex-full-width wf-add-bottom">
												<li>
													<ul class="wf-option wf-option-text">
														<li class="wf-option-content">
															<ul>
																<li class="wf-option-title">
																	<?php _e('Import Wordfence options from another site using a token', 'wordfence'); ?> <a href="<?php echo wfSupportController::esc_supportURL(wfSupportController::ITEM_DASHBOARD_OPTION_IMPORT); ?>"  target="_blank" rel="noopener noreferrer" class="wf-inline-help"><i class="wf-fa wf-fa-question-circle-o" aria-hidden="true"></i></a>
																</li>
																<li class="wf-option-text wf-option-full-width wf-no-right">
																	<input type="text" value="" id="wf-import-token">
																</li>
															</ul>
														</li>
													</ul>
												</li>
												<li>
													<ul class="wf-flex-horizontal wf-flex-full-width">
														<li class="wf-right wf-left-xs" id="wf-license-controls">
															<a href="#" class="wf-btn wf-btn-primary wf-btn-callout-subtle wf-disabled" id="wf-import-options"><?php _e('Import<span class="wf-hidden-xs"> Wordfence</span> Options', 'wordfence'); ?></a>
														</li>
													</ul>
												</li>
											</ul>
										</li>
									</ul>
									<script type="application/javascript">
										(function($) {
											$(function() {
												$('#wf-export-options').on('click', function(e) {
													e.preventDefault();
													e.stopPropagation();

													WFAD.ajax('wordfence_exportSettings', {}, function(res) {
														if (res.ok && res.token) {
															var prompt = $('#wfTmpl_exportPromptSuccess').tmpl(res);
															var promptHTML = $("<div />").append(prompt).html();
															WFAD.colorboxHTML((WFAD.isSmallScreen ? '300px' : '400px'), promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																$('#wf-export-prompt-close').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.colorboxClose();
																});
															}});
														}
														else {
															var prompt = $('#wfTmpl_exportPromptError').tmpl({err: res.err || 'An unknown error occurred during the export. We received an undefined error from your web server.'});
															var promptHTML = $("<div />").append(prompt).html();
															WFAD.colorboxHTML((WFAD.isSmallScreen ? '300px' : '400px'), promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																$('#wf-export-prompt-close').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.colorboxClose();
																});
															}});
														}
													});
												});
												
												$('#wf-import-token').on('keyup', function() {
													$('#wf-import-options').toggleClass('wf-disabled', $(this).val() == '');
												});

												$('#wf-import-options').on('click', function(e) { 
													e.preventDefault();
													e.stopPropagation();

													WFAD.ajax('wordfence_importSettings', {token: $('#wf-import-token').val()}, function(res) {
														if (res.ok) {
															var prompt = $('#wfTmpl_importPromptSuccess').tmpl(res);
															var promptHTML = $("<div />").append(prompt).html();
															WFAD.colorboxHTML((WFAD.isSmallScreen ? '300px' : '400px'), promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																$('#wf-import-prompt-reload').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	window.location.reload(true);
																});
															}});
														}
														else {
															var prompt = $('#wfTmpl_importPromptError').tmpl({err: res.err || 'An unknown error occurred during the import.'});
															var promptHTML = $("<div />").append(prompt).html();
															WFAD.colorboxHTML((WFAD.isSmallScreen ? '300px' : '400px'), promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
																$('#wf-import-prompt-close').on('click', function(e) {
																	e.preventDefault();
																	e.stopPropagation();

																	WFAD.colorboxClose();
																});
															}});
														}
													});
												});
											});
										})(jQuery);
									</script>
								</div>
							</div>
						</div>
					</div> <!-- end import options -->
				</div> <!-- end options block -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>
<script type="text/x-jquery-template" id="wfTmpl_downgradePrompt">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Confirm Downgrade', 'wordfence'),
		'message' => __('Are you sure you want to downgrade your Wordfence Premium License? This will disable all Premium features and return you to the free version of Wordfence.', 'wordfence'),
		'primaryButton' => array('id' => 'wf-downgrade-prompt-cancel', 'label' => __('Cancel', 'wordfence'), 'link' => '#'),
		'secondaryButtons' => array(array('id' => 'wf-downgrade-prompt-downgrade', 'label' => __('Downgrade', 'wordfence'), 'link' => '#')),
	))->render();
	?>
</script>

<script type="text/x-jquery-template" id="wfTmpl_exportPromptSuccess">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Export Successful', 'wordfence'),
		'messageHTML' => '<p>' . __('We successfully exported your site options. To import your site options on another site, copy and paste the token below into the import text box on the destination site. Keep this token secret &mdash; it is like a password. If anyone else discovers the token it will allow them to import your options excluding your license.', 'wordfence') . '</p><p><input type="text" class="wf-full-width" value="${token}" onclick="this.select();" /></p>',
		'primaryButton' => array('id' => 'wf-export-prompt-close', 'label' => __('Close', 'wordfence'), 'link' => '#'),
	))->render();
	?>
</script>

<script type="text/x-jquery-template" id="wfTmpl_exportPromptError">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Error during Export', 'wordfence'),
		'message' => '${err}',
		'primaryButton' => array('id' => 'wf-export-prompt-close', 'label' => __('Close', 'wordfence'), 'link' => '#'),
	))->render();
	?>
</script>

<script type="text/x-jquery-template" id="wfTmpl_importPromptSuccess">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Import Successful', 'wordfence'),
		'messageHTML' => __('We successfully imported the site options.', 'wordfence'),
		'primaryButton' => array('id' => 'wf-import-prompt-reload', 'label' => __('Reload', 'wordfence'), 'link' => '#'),
	))->render();
	?>
</script>

<script type="text/x-jquery-template" id="wfTmpl_importPromptError">
	<?php
	echo wfView::create('common/modal-prompt', array(
		'title' => __('Error during Import', 'wordfence'),
		'message' => '${err}',
		'primaryButton' => array('id' => 'wf-import-prompt-close', 'label' => __('Close', 'wordfence'), 'link' => '#'),
	))->render();
	?>
</script>

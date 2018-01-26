<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

$backPage = new wfPage(wfPage::PAGE_BLOCKING);
if (isset($_GET['source']) && wfPage::isValidPage($_GET['source'])) {
	$backPage = new wfPage($_GET['source']);
}
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Blocking Options', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;

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
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_BLOCKING,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default Blocking settings? This will undo any custom changes you have made to the options on this page. Any existing blocks will be preserved.', 'wordfence'),
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
						'title' => __('Blocking Options', 'wordfence'),
						'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING),
						'helpLabelHTML' => __('Learn more<span class="wf-hidden-xs"> about Blocking</span>', 'wordfence'),
						'showIcon' => true,
					))->render();
					?>
					<div class="wf-row">
						<div class="wf-col-xs-12">
							<div class="wf-block wf-always-active">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('General', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"></div>
									</div>
								</div>
								<div class="wf-block-content">
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'displayTopLevelBlocking',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('displayTopLevelBlocking') ? 1 : 0,
												'title' => __('Display top level Blocking menu option', 'wordfence'),
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
							<div class="wf-block<?php echo ((!wfConfig::get('isPaid') && !wfPersistenceController::shared()->isConfigured('blocking-options-country')) || wfPersistenceController::shared()->isActive('blocking-options-country') ? ' wf-active' : '') ?>" data-persistence-key="blocking-options-country">
								<div class="wf-block-header">
									<div class="wf-block-header-content">
										<div class="wf-block-title">
											<strong><?php _e('Advanced Country Blocking', 'wordfence'); ?></strong>
										</div>
										<div class="wf-block-header-action"><div class="wf-block-header-action-disclosure"></div></div>
									</div>
								</div>
								<div class="wf-block-content">
								<?php if (wfConfig::get('isPaid')): ?>
									<ul class="wf-block-list">
										<li>
											<?php
											echo wfView::create('options/option-select', array(
												'selectOptionName' => 'cbl_action',
												'selectOptions' => array(
													array('value' => 'block', 'label' => 'Show the standard Wordfence blocked message'),
													array('value' => 'redir', 'label' => 'Redirect to the URL below'),
												),
												'selectValue' => wfConfig::get('cbl_action'),
												'title' => __('What to do when we block someone', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING_OPTION_WHAT_TO_DO),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-text', array(
												'textOptionName' => 'cbl_redirURL',
												'textValue' => wfConfig::get('cbl_redirURL'),
												'title' => __('URL to redirect blocked users to', 'wordfence'),
												'placeholder' => __('Must be a full URL (e.g., http://example.com/blocked/)', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING_OPTION_REDIRECT),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('options/option-toggled', array(
												'optionName' => 'cbl_loggedInBlocked',
												'enabledValue' => 1,
												'disabledValue' => 0,
												'value' => wfConfig::get('cbl_loggedInBlocked') ? 1 : 0,
												'title' => __('Block countries even if they are logged in', 'wordfence'),
												'helpLink' => wfSupportController::supportURL(wfSupportController::ITEM_FIREWALL_BLOCKING_OPTION_BLOCK_LOGGED_IN),
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('blocking/option-bypass-redirect', array(
											))->render();
											?>
										</li>
										<li>
											<?php
											echo wfView::create('blocking/option-bypass-cookie', array(
											))->render();
											?>
										</li>
									</ul>
								<?php else: ?>
									<ul class="wf-flex-vertical wf-padding-add-right-large wf-padding-add-bottom-large">
										<li><h3><?php _e('Put Geographic Protection In Place With Country Blocking', 'wordfence'); ?></h3></li>
										<li><p class="wf-no-top"><?php _e('Wordfence country blocking is designed to stop an attack, prevent content theft, or end malicious activity that originates from a geographic region in less than 1/300,000th of a second. Blocking countries who are regularly creating failed logins, a large number of page not found errors, and are clearly engaged in malicious activity is an effective way to protect your site during an attack.', 'wordfence'); ?></p></li>
										<li><?php echo wfView::create('blocking/country-block-map')->render(); ?></li>
										<li><a class="wf-btn wf-btn-primary wf-btn-callout-subtle" href="https://www.wordfence.com/gnl1countryBlockUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php _e('Upgrade to Premium', 'wordfence'); ?></a></li>
									</ul>
								<?php endif; ?>
								</div>
							</div>
						</div>
					</div> <!-- end country blocking -->
				</div> <!-- end blocking options block -->
			</div> <!-- end content block -->
		</div> <!-- end row -->
	</div> <!-- end container -->
</div>

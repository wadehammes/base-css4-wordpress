<?php
if (!defined('WORDFENCE_VERSION')) { exit; }

global $wpdb;

$blockedToday = (int) $wpdb->get_var("SELECT SUM(blockCount) 
FROM {$wpdb->base_prefix}wfBlockedCommentLog
WHERE unixday >= FLOOR(UNIX_TIMESTAMP() / 86400)");

$blockedThisWeek = (int) $wpdb->get_var("SELECT SUM(blockCount) 
FROM {$wpdb->base_prefix}wfBlockedCommentLog
WHERE unixday >= FLOOR(UNIX_TIMESTAMP() / 86400) - 7");

$blockedThisMonth = (int) $wpdb->get_var("SELECT SUM(blockCount)
FROM {$wpdb->base_prefix}wfBlockedCommentLog
WHERE unixday >= FLOOR(UNIX_TIMESTAMP() / 86400) - 31");

?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Comment Spam Filter', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
		});
	})(jQuery);
</script>
<div id="wf-tools-commentspam">
	<div class="wf-section-title">
		<h2><?php _e('Comment Spam Filter', 'wordfence') ?></h2>
		<span><?php printf(__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about the Comment Spam Filter</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_COMMENT_SPAM)); ?>
			<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i></span>
	</div>

	<p><?php _e("Wordfence reduces spam that is known to slip through traditional filters by using advanced heuristics to identify spam comments and aggregate data to identify spammers.", 'wordfence') ?></p>

	<div class="wf-row">
		<div class="wf-col-xs-12">
			<?php
			echo wfView::create('options/block-controls', array(
				'suppressLogo' => true,
				'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_COMMENT_SPAM,
				'restoreDefaultsMessage' => __('Are you sure you want to restore the default Comment Spam settings? This will undo any custom changes you have made to the options on this page.', 'wordfence'),
			))->render();
			?>
		</div>
	</div>
	<div class="wf-row">
		<div class="wf-col-xs-12">
			<div class="wf-dashboard-item active">
				<div class="wf-dashboard-item-extra">
					<ul class="wf-dashboard-item-list">
						<li>
							<ul class="wf-dashboard-item-list wf-dashboard-item-list-horizontal">
								<li>
									<strong><?php _e('Comment Spam Filter Options', 'wordfence') ?></strong>
								</li>
								<li>
									<strong><?php _e('Advanced Comment Spam Filter Options', 'wordfence') ?>
										<?php if (!wfConfig::p()): ?>
											<a href="https://www.wordfence.com/gnl1optionUpgrade/wordfence-signup/" target="_blank" rel="noopener noreferrer" class="wf-premium-link"><?php _e('Premium Feature', 'wordfence') ?></a>
										<?php endif ?>
									</strong>
								</li>
							</ul>
						</li>
					</ul>
					<ul class="wf-dashboard-item-list">
						<li>
							<ul class="wf-dashboard-item-list wf-dashboard-item-list-horizontal">
								<li>
									<div>
										<?php
										echo wfView::create('options/option-toggled-segmented', array(
											'optionName'    => 'other_noAnonMemberComments',
											'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_COMMENT_SPAM_OPTION_HOLD_ANONYMOUS),
											'enabledValue'  => 1,
											'disabledValue' => 0,
											'value'         => wfConfig::get('other_noAnonMemberComments') ? 1 : 0,
											'htmlTitle'     => __(<<<HTML
<strong>Hold anonymous comments using member emails for moderation</strong><br>Blocks when the comment is posted without being logged in, but provides an email address for the registered user. 
HTML
												, 'wordfence'),
										))->render();
										?>
										<?php
										echo wfView::create('options/option-toggled-segmented', array(
											'optionName'    => 'other_scanComments',
											'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_COMMENT_SPAM_OPTION_SCAN),
											'enabledValue'  => 1,
											'disabledValue' => 0,
											'value'         => wfConfig::get('other_scanComments') ? 1 : 0,
											'htmlTitle'     => __(<<<HTML
<strong>Filter comments for malware and phishing URLs</strong><br>Blocks when a comment contains a URL on a domain blacklist. 
HTML
												, 'wordfence'),
										))->render();
										?>
									</div>
								</li>
								<li>
									<div id="wfAdvancedCommentScanningOption" style="align-self:flex-start">
										<?php
										echo wfView::create('options/option-toggled-segmented', array(
											'optionName'    => 'advancedCommentScanning',
											'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_COMMENT_SPAM_OPTION_ADVANCED),
											'premium'       => !wfConfig::p(),
											'enabledValue'  => 1,
											'disabledValue' => 0,
											'value'         => wfConfig::get('advancedCommentScanning') ? 1 : 0,
											'htmlTitle'     => __(<<<HTML
<strong>Advanced Comment Spam Filter</strong><br>In addtion to free comment filtering, this option filters comments against several additional real-time lists of known spammers and infected hosts.  
HTML
												, 'wordfence'),

										))->render();
										?>

									</div>
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
			<div class="wf-dashboard-item active">
				<div class="wf-dashboard-item-inner">
					<div class="wf-dashboard-item-content">
						<div class="wf-dashboard-item-title">
							<strong><?php _e('Spam Comments Blocked', 'wordfence') ?></strong>
						</div>
					</div>
				</div>
				<div class="wf-dashboard-item-extra">
					<ul class="wf-dashboard-item-list">
						<li>
							<ul class="wf-dashboard-item-list wf-dashboard-item-list-horizontal">
								<li>
									<div class="wf-dashboard-item-labeled-count">
										<div class="wf-dashboard-item-labeled-count-count"><?php echo $blockedToday ?></div>
										<div class="wf-dashboard-item-labeled-count-label">Blocked Today</div>
									</div>
								</li>
								<li>
									<div class="wf-dashboard-item-labeled-count">
										<div class="wf-dashboard-item-labeled-count-count"><?php echo $blockedThisWeek ?></div>
										<div class="wf-dashboard-item-labeled-count-label">Blocked This Week</div>
									</div>
								</li>
								<li>
									<div class="wf-dashboard-item-labeled-count">
										<div class="wf-dashboard-item-labeled-count-count"><?php echo $blockedThisMonth ?></div>
										<div class="wf-dashboard-item-labeled-count-label">Blocked This Month</div>
									</div>
								</li>
							</ul>
						</li>
					</ul>
					<ul class="wf-dashboard-item-list">
						<li>
							<div class="wf-center">
								<?php if (!wfConfig::p()): ?>
									<p>The Wordfence Advanced Comment Spam Filter is automatically enabled for Premium customers, providing an additional layer of filtering. The advanced filter does an additional check on the source IP of inbound comments and any URLs that are included.</p>
									<p><a class="wf-btn wf-btn-primary" href="https://www.wordfence.com/zz11/wordfence-signup/" target="_blank" rel="noopener noreferrer">Upgrade To Premium</a></p>
								<?php else: ?>
									<p><a class="wf-btn wf-btn-primary" href="https://www.wordfence.com/zz10/sign-in/" target="_blank" rel="noopener noreferrer">Protect More Sites</a></p>
								<?php endif ?>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>

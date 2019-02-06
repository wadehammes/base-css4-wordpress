<?php if (!defined('WORDFENCE_VERSION')) { exit; } ?>
<div class="wf-row">
	<div class="wf-col-xs-12 <?php if (wfCentral::isSupported() && wfConfig::get('showWfCentralUI', false)): ?>wf-col-lg-6 wf-col-lg-half-padding-right<?php else: ?>wf-col-lg-12<?php endif ?>">
		<div class="wf-dashboard-item active">
			<div class="wf-dashboard-item-inner">
				<div class="wf-dashboard-item-content">
					<div class="wf-dashboard-item-title">
						<strong>Notifications</strong><span class="wf-dashboard-badge wf-notification-count-container wf-notification-count-value<?php echo (count($d->notifications) == 0 ? ' wf-hidden' : ''); ?>"><?php echo number_format_i18n(count($d->notifications)); ?></span>
					</div>
					<div class="wf-dashboard-item-action"><div class="wf-dashboard-item-action-disclosure"></div></div>
				</div>
			</div>
			<div class="wf-dashboard-item-extra">
				<ul class="wf-dashboard-item-list wf-dashboard-item-list-striped">
					<?php foreach ($d->notifications as $n): ?>
						<li class="wf-notification<?php if ($n->priority % 10 == 1) { echo ' wf-notification-critical'; } else if ($n->priority % 10 == 2) { echo ' wf-notification-warning'; } ?>" data-notification="<?php echo esc_html($n->id); ?>">
							<div class="wf-dashboard-item-list-title"><?php echo $n->html; ?></div>
							<?php foreach ($n->links as $l): ?>
								<div class="wf-dashboard-item-list-action"><a href="<?php echo esc_html($l['link']); ?>"<?php if (preg_match('/^https?:\/\//i', $l['link'])) { echo ' target="_blank" rel="noopener noreferrer"'; } ?>><?php echo esc_html($l['label']); ?></a></div>
							<?php endforeach; ?>
							<div class="wf-dashboard-item-list-dismiss"><a href="#" class="wf-dismiss-notification"><i class="wf-fa wf-fa-times-circle" aria-hidden="true"></i></a></div>
						</li>
					<?php endforeach; ?>
					<?php if (count($d->notifications) == 0): ?>
						<li class="wf-notifications-empty">No notifications received</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
	<?php if (wfCentral::isSupported() && wfConfig::get('showWfCentralUI', false)): ?>
	<div class="wf-col-xs-12 wf-col-lg-6 wf-col-lg-half-padding-left">
		<div class="wf-dashboard-item active">
			<?php if ($d->wordfenceCentralConnected): ?>
				<div class="wf-dashboard-item-inner">
					<div class="wf-dashboard-item-content">
						<div class="wf-dashboard-item-title">
							<strong>Wordfence Central Activated</strong>
						</div>
					</div>
				</div>
				<div class="wf-dashboard-item-extra">
					<ul class="wf-dashboard-item-list">
						<li>
							<div class="wf-row">
								<p class="wf-col-md-9">
									<?php printf(__('Connected by %s on %s', 'wordfence'), esc_html($d->wordfenceCentralConnectEmail), esc_html(date_i18n(get_option('date_format'), $d->wordfenceCentralConnectTime))) ?>

								</p>
								<p class="wf-col-md-3 wf-right-md wf-right-lg">
									<a href="admin.php?page=WordfenceCentral"><strong><?php _e('Disconnect', 'wordfence') ?></strong></a>
								</p>
							</div>
						</li>
					</ul>
				</div>

			<?php elseif ($d->wordfenceCentralDisconnected): ?>
				<div class="wf-dashboard-item-inner">
					<div class="wf-dashboard-item-content">
						<div class="wf-dashboard-item-title">
							<strong><?php _e('Wordfence Central Deactivated', 'wordfence') ?></strong>
						</div>
					</div>
				</div>

				<div class="wf-dashboard-item-extra">
					<ul class="wf-dashboard-item-list">
						<li>
							<div class="wf-row">
								<p class="wf-col-md-9">
									Disconnected by <?php echo esc_html($d->wordfenceCentralDisconnectEmail) ?> on <?php echo esc_html(date_i18n(get_option('date_format'), $d->wordfenceCentralDisconnectTime)) ?>
								</p>
								<p class="wf-col-md-3 wf-right-md wf-right-lg">
									<a href="<?php echo esc_url(WORDFENCE_CENTRAL_URL_SEC) ?>"><strong><?php _e('Visit Cental', 'wordfence') ?></strong></a>
								</p>
							</div>
						</li>
					</ul>
				</div>
			<?php else: ?>
				<div class="wf-central-dashboard">
					<img class="wf-central-dashboard-logo" src="<?php echo wfUtils::getBaseURL() ?>/images/wf-central-logo.svg" alt="Wordfence Central">
					<div class="wf-central-dashboard-copy">
						<p><?php _e('Wordfence Central allows you to manage Wordfence on multiple sites from one location. It makes security monitoring and configuring Wordfence easier.', 'wordfence') ?></p>
						<p><a href="https://www.wordfence.com/help/central"><?php _e('Get Started', 'wordfence') ?></a></p>
					</div>
				</div>
			<?php endif ?>
		</div>
	</div>
	<?php endif ?>
</div>
<script type="application/javascript">
	(function($) {
		$('.wf-dismiss-notification').on('click', function(e) {
			e.preventDefault();
			e.stopPropagation();
			
			var n = $(this).closest('.wf-notification');
			var id = n.data('notification');
			n.fadeOut(400, function() {
				n.remove();
				
				var count = $('.wf-dismiss-notification').length;
				WFDash.updateNotificationCount(count);
			});
			
			WFAD.ajax('wordfence_dismissNotification', {
				id: id
			}, function(res) {
				//Do nothing
			});
		});
	})(jQuery);
</script> 

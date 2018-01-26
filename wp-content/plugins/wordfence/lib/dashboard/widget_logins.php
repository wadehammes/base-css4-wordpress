<?php if (!defined('WORDFENCE_VERSION')) { exit; } ?>
<?php //$d is defined here as a wfDashboard instance ?>
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-dashboard-item active">
			<div class="wf-dashboard-item-inner">
				<div class="wf-dashboard-item-content">
					<div class="wf-dashboard-item-title">
						<strong>Login Attempts</strong>
					</div>
					<div class="wf-dashboard-item-action"><div class="wf-dashboard-item-action-disclosure"></div></div>
				</div>
			</div>
			<div class="wf-dashboard-item-extra">
				<?php if ($firewall->learningModeStatus() !== false): ?>
					<div class="wf-widget-learning-mode"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100.11 100.44"><path d="M96.14,30.67a50.7,50.7,0,0,0-10.66-16A50,50,0,0,0,69.51,4,49.57,49.57,0,0,0,30.6,4a50,50,0,0,0-16,10.69A50.69,50.69,0,0,0,4,30.67,50,50,0,0,0,4,69.74a50.62,50.62,0,0,0,10.66,16,50,50,0,0,0,16,10.69,49.54,49.54,0,0,0,38.91,0,50,50,0,0,0,16-10.69,50.56,50.56,0,0,0,10.66-16,50,50,0,0,0,0-39.07Zm-75.74,39a35.77,35.77,0,0,1-1-37.35,35.21,35.21,0,0,1,12.91-13A34.65,34.65,0,0,1,50.06,14.6a34.22,34.22,0,0,1,19.55,5.93ZM82.71,64a35.4,35.4,0,0,1-7.56,11.37A36,36,0,0,1,63.84,83a34.32,34.32,0,0,1-13.79,2.84A34.85,34.85,0,0,1,30.7,80L79.84,31a34.57,34.57,0,0,1,5.67,19.23A35.17,35.17,0,0,1,82.71,64Zm0,0"/></svg><span><?php _e('No Data Available During Learning Mode', 'wordfence'); ?></span></div>
				<?php else: ?>
				<ul class="wf-dashboard-item-list">
					<li>
						<div>
							<div class="wf-dashboard-toggle-btns">
								<ul class="wf-pagination wf-pagination-sm">
									<li class="wf-active"><a href="#" class="wf-dashboard-login-attempts" data-grouping="success">Successful</a></li>
									<li><a href="#" class="wf-dashboard-login-attempts" data-grouping="fail">Failed</a></li>
								</ul>
							</div>
							<div class="wf-recent-logins wf-recent-logins-success">
								<?php if (count($d->loginsSuccess) == 0): ?>
									<div class="wf-dashboard-item-list-text"><p><em>No successful logins have been recorded.</em></p></div>
								<?php else: ?>
									<?php $data = array_slice($d->loginsSuccess, 0, min(10, count($d->loginsSuccess)), true); include(dirname(__FILE__) . '/widget_content_logins.php'); ?>
									<?php if (count($d->loginsSuccess) > 10): ?>
										<div class="wf-dashboard-item-list-text"><div class="wf-dashboard-show-more" data-grouping="logins" data-period="success"><a href="#">Show more</a></div></div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
							<div class="wf-recent-logins wf-recent-logins-fail wf-hidden">
								<?php if (count($d->loginsFail) == 0): ?>
									<div class="wf-dashboard-item-list-text"><p><em>No failed logins have been recorded.</em></p></div>
								<?php else: ?>
									<?php $data = array_slice($d->loginsFail, 0, min(10, count($d->loginsFail)), true); include(dirname(__FILE__) . '/widget_content_logins.php'); ?>
									<?php if (count($d->loginsFail) > 10): ?>
										<div class="wf-dashboard-item-list-text"><div class="wf-dashboard-show-more" data-grouping="logins" data-period="fail"><a href="#">Show more</a></div></div>
									<?php endif; ?>
								<?php endif; ?>
							</div>
							<script type="application/javascript">
								(function($) {
									$('.wf-dashboard-login-attempts').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();
										
										$(this).closest('ul').find('li').removeClass('wf-active');
										$(this).closest('li').addClass('wf-active'); 
										
										$('.wf-recent-logins').addClass('wf-hidden');
										$('.wf-recent-logins-' + $(this).data('grouping')).removeClass('wf-hidden');
									});

									$('.wf-recent-logins .wf-dashboard-show-more a').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();

										var grouping = $(this).parent().data('grouping');
										var period = $(this).parent().data('period');

										$(this).closest('.wf-dashboard-item-list-text').fadeOut();

										var self = this;
										WFAD.ajax('wordfence_dashboardShowMore', {
											grouping: grouping,
											period: period
										}, function(res) {
											if (res.ok) {
												var table = $('#logins-data-template').tmpl(res);
												$(self).closest('.wf-recent-logins').css('overflow-y', 'auto');
												$(self).closest('.wf-recent-logins').find('table').replaceWith(table);
											}
											else {
												WFAD.colorboxModal('300px', 'An error occurred', 'We encountered an error trying load more data.');
												$(this).closest('.wf-dashboard-item-list-text').fadeIn();
											}
										});
									});
								})(jQuery);
							</script>
						</div>
					</li>
				</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script type="text/x-jquery-template" id="logins-data-template">
	<table class="wf-table wf-table-hover">
		<thead>
		<tr>
			<th>Username</th>
			<th>IP</th>
			<th>Date</th>
		</tr>
		</thead>
		<tbody>
		{{each(idx, d) data}}
		<tr>
			<td>${d.name}</td>
			<td>${d.ip}</td>
			<td>${d.t}</td>
		</tr>
		{{/each}}
		</tbody>
	</table>
</script>
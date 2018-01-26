<?php if (!defined('WORDFENCE_VERSION')) { exit; } ?>
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-dashboard-item active">
			<div class="wf-dashboard-item-inner">
				<div class="wf-dashboard-item-content">
					<div class="wf-dashboard-item-title">
						<strong><?php _e('Total Attacks Blocked:', 'wordfence'); ?> </strong><?php _e('Wordfence Network', 'wordfence'); ?>
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
						<?php if ($d->networkBlock24h === null): ?>
							<div class="wf-dashboard-item-list-text"><p><em><?php _e('Blocked attack counts not available yet.', 'wordfence'); ?></em></p></div>
						<?php else: ?>
							<div class="wf-dashboard-graph-wrapper">
								<div class="wf-dashboard-toggle-btns">
									<ul class="wf-pagination wf-pagination-sm">
										<li class="wf-active"><a href="#" class="wf-dashboard-graph-attacks" data-grouping="24h"><?php _e('24 Hours', 'wordfence'); ?></a></li>
										<!-- <li><a href="#" class="wf-dashboard-graph-attacks" data-grouping="7d">7 Days</a></li> -->
										<li><a href="#" class="wf-dashboard-graph-attacks" data-grouping="30d"><?php _e('30 Days', 'wordfence'); ?></a></li>
									</ul>
								</div>
								<div class="wf-dashboard-network-blocks"><canvas id="wf-dashboard-network-blocks-24h"></canvas></div>
								<div class="wf-dashboard-network-blocks wf-hidden"><canvas id="wf-dashboard-network-blocks-7d"></canvas></div>
								<div class="wf-dashboard-network-blocks wf-hidden"><canvas id="wf-dashboard-network-blocks-30d"></canvas></div>
							</div>
							<script type="application/javascript">
								<?php
								$styling = <<<STYLING
																		label: "Total Attacks",
																		fill: false,
																		lineTension: 0.1,
																		backgroundColor: "rgba(75,192,192,0.4)",
																		borderColor: "#16bc9b",
																		borderCapStyle: 'butt',
																		borderDash: [],
																		borderDashOffset: 0.0,
																		borderJoinStyle: 'miter',
																		pointBorderColor: "rgba(75,192,192,1)",
																		pointBackgroundColor: "#fff",
																		pointBorderWidth: 1,
																		pointHoverRadius: 5,
																		pointHoverBackgroundColor: "rgba(75,192,192,1)",
																		pointHoverBorderColor: "rgba(220,220,220,1)",
																		pointHoverBorderWidth: 2,
																		pointRadius: 1,
																		pointHitRadius: 10,
																		spanGaps: false,
STYLING;
								
								?>
								(function($) {
									$(document).ready(function() {
										new Chart($('#wf-dashboard-network-blocks-24h'), {
											type: 'line',
											data: {
												<?php
												$blocks = $d->networkBlock24h;
												$labels = array();
												$values = array();
												
												foreach ($blocks as $b) {
													$values[] = $b['c'];
													$labels[] = "'" . wfUtils::formatLocalTime('g a', $b['t']) . "'";
												}
												?>
												labels: [<?php echo implode(',', $labels); ?>],
												datasets: [
													{
														<?php echo $styling; ?>
														data: [<?php echo implode(',', $values) ?>]
													}
												]
											},
											options: {
												scales: {
													yAxes: [{
														ticks: {
															beginAtZero: true,
															callback: function(value, index, values) {
																return value.toLocaleString();
															}
														}
													}]
												},
												tooltips: {
													callbacks: {
														label: function(tooltipItem, data) {
															var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
															var label = parseInt(tooltipItem.yLabel).toLocaleString();
															return datasetLabel + ': ' + label;
														}
													}
												}
											}
										});

										new Chart($('#wf-dashboard-network-blocks-7d'), {
											type: 'line',
											data: {
												<?php
												$blocks = $d->networkBlock7d;
												$labels = array();
												$values = array();
												
												foreach ($blocks as $b) {
													$values[] = $b['c'];
													$labels[] = "'" . wfUtils::formatLocalTime('M j', $b['t']) . "'";
												}
												?>
												labels: [<?php echo implode(',', $labels); ?>],
												datasets: [
													{
														<?php echo $styling; ?>
														data: [<?php echo implode(',', $values) ?>]
													}
												]
											},
											options: {
												scales: {
													yAxes: [{
														ticks: {
															beginAtZero: true,
															callback: function(value, index, values) {
																return value.toLocaleString();
															}
														}
													}]
												},
												tooltips: {
													callbacks: {
														label: function(tooltipItem, data) {
															var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
															var label = parseInt(tooltipItem.yLabel).toLocaleString();
															return datasetLabel + ': ' + label;
														}
													}
												}
											}
										});

										new Chart($('#wf-dashboard-network-blocks-30d'), {
											type: 'line',
											data: {
												<?php
												$blocks = $d->networkBlock30d;
												$labels = array();
												$values = array();
												
												foreach ($blocks as $b) {
													$values[] = $b['c'];
													$labels[] = "'" . wfUtils::formatLocalTime('M j', $b['t']) . "'";
												}
												?>
												labels: [<?php echo implode(',', $labels); ?>],
												datasets: [
													{
														<?php echo $styling; ?>
														data: [<?php echo implode(',', $values) ?>]
													}
												]
											},
											options: {
												scales: {
													yAxes: [{
														ticks: {
															beginAtZero: true,
															callback: function(value, index, values) {
																return value.toLocaleString();
															}
														}
													}]
												},
												tooltips: {
													callbacks: {
														label: function(tooltipItem, data) {
															var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
															var label = parseInt(tooltipItem.yLabel).toLocaleString();
															return datasetLabel + ': ' + label;
														}
													}
												}
											}
										});
									});
									
									$('.wf-dashboard-graph-attacks').on('click', function(e) {
										e.preventDefault();
										e.stopPropagation();

										$(this).closest('ul').find('li').removeClass('wf-active');
										$(this).closest('li').addClass('wf-active');

										$('.wf-dashboard-network-blocks').addClass('wf-hidden');
										$('#wf-dashboard-network-blocks-' + $(this).data('grouping')).closest('.wf-dashboard-network-blocks').removeClass('wf-hidden');
									});
								})(jQuery);
							</script>
						<?php endif; ?>
					</li>
				</ul>
				<p class="wf-dashboard-last-updated"><?php printf(__('Last Updated: %s ago', 'wordfence'), esc_html(wfUtils::makeTimeAgo(time() - $d->lastGenerated))); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
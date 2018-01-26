<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
$w = new wfConfig();
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Live Traffic', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
		});
	})(jQuery);
</script>
<div class="wf-section-title">
	<h2><?php _e('Live Traffic', 'wordfence') ?></h2>
	<span><?php printf(__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about Live Traffic</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_LIVE_TRAFFIC)); ?>
		<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i></span>
</div>

<?php if (wfConfig::liveTrafficEnabled() && wfConfig::get('liveActivityPauseEnabled')): ?>
	<div id="wfLiveTrafficOverlayAnchor"></div>
	<div id="wfLiveTrafficDisabledMessage">
		<h2><?php _e('Live Updates Paused', 'wordfence') ?><br/>
			<small><?php _e('Click inside window to resume', 'wordfence') ?></small>
		</h2>
	</div>
<?php endif ?>

<p><?php _e("Wordfence Live Traffic shows you what is happening on your site in real-time. This includes a lot of data that javascript based analytics packages like Google analytics do not show you. The reason they can't show you this data is because Wordfence logs your traffic at the server level. So for example, we will show you visits from Google's crawlers, Bing's crawlers, hack attempts and other visits that don't execute javascript. Whereas Google analytics and other analytics packages will only show you visits from web browsers that are usually operated by a human.", 'wordfence') ?></p>

<div class="wordfenceModeElem" id="wordfenceMode_liveTraffic"></div>

<div id="wf-live-traffic-options" class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-block<?php echo(wfPersistenceController::shared()->isActive('live-traffic-options') ? ' wf-active' : '') ?>" data-persistence-key="live-traffic-options">
			<div class="wf-block-header">
				<div class="wf-block-header-content">
					<div class="wf-block-title">
						<strong><?php _e('Live Traffic Options', 'wordfence'); ?></strong>
					</div>
					<div class="wf-block-header-action">
						<div class="wf-block-header-action-disclosure"></div>
					</div>
				</div>
			</div>
			<div class="wf-block-content wf-clearfix">

				<p>
					<?php _e('These options let you ignore certain types of visitors, based on their level of access, usernames, IP address or browser type. If you run a very high traffic website where it is not feasible to see your visitors in real-time, simply un-check the live traffic option and nothing will be written to the Wordfence tracking tables.', 'wordfence') ?>
				</p>

				<div class="wf-row">
					<div class="wf-col-xs-12">
						<?php
						echo wfView::create('options/block-controls', array(
							'suppressLogo' => true,
							'restoreDefaultsSection' => wfConfig::OPTIONS_TYPE_LIVE_TRAFFIC,
							'restoreDefaultsMessage' => __('Are you sure you want to restore the default Live Traffic settings? This will undo any custom changes you have made to the options on this page.', 'wordfence'),
						))->render();
						?>
					</div>
				</div>

				<ul class="wf-block-list">
					<li>
						<?php
						echo wfView::create('options/option-toggled', array(
							'optionName'    => 'liveTrafficEnabled',
							'enabledValue'  => 1,
							'disabledValue' => 0,
							'value'         => wfConfig::get('liveTrafficEnabled') ? 1 : 0,
							'title'         => __('This option enables live traffic logging.', 'wordfence'),
							'helpLink'      => wfSupportController::supportURL(wfSupportController::ITEM_TOOLS_LIVE_TRAFFIC_OPTION_ENABLE),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-toggled', array(
							'optionName'    => 'liveTraf_ignorePublishers',
							'enabledValue'  => 1,
							'disabledValue' => 0,
							'value'         => wfConfig::get('liveTraf_ignorePublishers') ? 1 : 0,
							'title'         => __("Don't log signed-in users with publishing access.", 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-toggled', array(
							'optionName'    => 'liveTraf_displayExpandedRecords',
							'enabledValue'  => 1,
							'disabledValue' => 0,
							'value'         => wfConfig::get('liveTraf_displayExpandedRecords') ? 1 : 0,
							'title'         => __("Always display expanded Live Traffic records.", 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-text', array(
							'textOptionName' => 'liveTraf_ignoreUsers',
							'textValue'      => wfConfig::get('liveTraf_ignoreUsers'),
							'title'          => __('List of comma separated usernames to ignore.', 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-text', array(
							'textOptionName' => 'liveTraf_ignoreIPs',
							'textValue'      => wfConfig::get('liveTraf_ignoreIPs'),
							'title'          => __('List of comma separated IP addresses to ignore.', 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-text', array(
							'textOptionName' => 'liveTraf_ignoreUA',
							'textValue'      => wfConfig::get('liveTraf_ignoreUA'),
							'title'          => __('Browser user-agent to ignore.', 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-text', array(
							'textOptionName' => 'liveTraf_maxRows',
							'textValue'      => wfConfig::get('liveTraf_maxRows'),
							'title'          => __('Amount of Live Traffic data to store (number of rows).', 'wordfence'),
						))->render();
						?>
					</li>
					<li>
						<?php
						echo wfView::create('options/option-toggled', array(
							'optionName' => 'displayTopLevelLiveTraffic',
							'enabledValue' => 1,
							'disabledValue' => 0,
							'value' => wfConfig::get('displayTopLevelLiveTraffic') ? 1 : 0,
							'title' => __('Display top level Live Traffic menu option', 'wordfence'),
						))->render();
						?>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>
<div id="wf-live-traffic" class="wf-row<?php echo wfConfig::get('liveTraf_displayExpandedRecords') ? ' wf-live-traffic-display-expanded' : '' ?>">
	<div class="wf-col-xs-12">
		<div class="wf-block wf-active">
			<div class="wf-block-content">
				<div class="wf-container-fluid">
					<div class="wf-row">
						<?php
						// $rightRail = new wfView('marketing/rightrail');
						// echo $rightRail;
						?>
						<div class="<?php echo wfStyle::contentClasses(); ?>">
							<?php
							$overridden = false;
							if (!wfConfig::liveTrafficEnabled($overridden)):
								?>
								<div id="wordfenceLiveActivityDisabled"><p>
										<strong><?php _e('Live activity is disabled', 'wordfence') ?><?php
											if ($overridden) {
												_e(' by the host', 'wordfence');
											} ?>.</strong> <?php _e('Login and firewall activity will still appear below.', 'wordfence') ?></p>
								</div>
							<?php endif ?>
							<div class="wf-row wf-add-bottom-small">
								<div class="wf-col-xs-12" id="wf-live-traffic-legend-wrapper">

									<form data-bind="submit: reloadListings">

										<div class="wf-clearfix">
											<div id="wf-live-traffic-legend-placeholder"></div>
											<div id="wf-live-traffic-legend">
												<ul>
													<li class="wfHuman"><?php _e('Human', 'wordfence') ?></li>
													<li class="wfBot"><?php _e('Bot', 'wordfence') ?></li>
													<li class="wfNotice"><?php _e('Warning', 'wordfence') ?></li>
													<li class="wfBlocked"><?php _e('Blocked', 'wordfence') ?></li>
												</ul>
											</div>

											<div class="wfActEvent wf-live-traffic-filter">
												<select id="wf-lt-preset-filters" data-bind="options: presetFiltersOptions, optionsText: presetFiltersOptionsText, value: selectedPresetFilter">
												</select>
												&nbsp;&nbsp;
												<input id="wf-live-traffic-filter-show-advanced" class="wf-option-checkbox" data-bind="checked: showAdvancedFilters" type="checkbox">
												<label for="wf-live-traffic-filter-show-advanced">
													<?php _e('Show Advanced Filters', 'wordfence') ?>
												</label>
											</div>
										</div>

										<div data-bind="visible: showAdvancedFilters" id="wf-lt-advanced-filters">
											<div class="wf-live-traffic-filter-detail">
												<div>
													<div data-bind="foreach: filters">
														<div class="wf-live-traffic-filter-item">
															<div class="wf-live-traffic-filter-item-parameters">
																<div>
																	<select name="param[]" class="wf-lt-advanced-filters-param" data-bind="options: filterParamOptions, optionsText: filterParamOptionsText, value: selectedFilterParamOptionValue, optionsCaption: 'Filter...'"></select>
																</div>
																<div data-bind="visible: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() != 'bool'">
																	<select name="operator[]" class="wf-lt-advanced-filters-operator" data-bind="options: filterOperatorOptions, optionsText: filterOperatorOptionsText, value: selectedFilterOperatorOptionValue"></select>
																</div>
																<div data-bind="attr: {colSpan: (selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'bool' ? 2 : 1)}" class="wf-lt-advanced-filters-value-cell">
																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'enum'">
																		<select data-bind="options: selectedFilterParamOptionValue().values, optionsText: selectedFilterParamOptionValue().optionsText, value: value"></select>
																	</span>

																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'text'">
																		<input data-bind="value: value" type="text">
																	</span>

																	<span data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'bool'">
																		<label>Yes <input data-bind="checked: value" type="radio" value="1"></label>
																		<label>No <input data-bind="checked: value" type="radio" value="0"></label>
																	</span>
																</div>
															</div>
															<div>
																<!--<button data-bind="click: $root.removeFilter" type="button" class="wf-btn wf-btn-default">Remove</button> -->
																<a href="#" data-bind="click: $root.removeFilter" class="wf-live-traffic-filter-remove"><i class="wf-ion-trash-a"></i></a>
															</div>
														</div>
													</div>
													<div>
														<div class="wf-pad-small">
															<button type="button" class="wf-btn wf-btn-default" data-bind="click: addFilter">
																Add Filter
															</button>
														</div>
													</div>
												</div>
												<div class="wf-form wf-form-horizontal">
													<div class="wf-form-group">
														<label for="wf-live-traffic-from" class="wf-col-sm-2">From:&nbsp;</label>
														<div class="wf-col-sm-10">
															<input placeholder="Start date" id="wf-live-traffic-from" type="text" class="wf-datetime" data-bind="value: startDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
															<button data-bind="click: startDate('')" class="wf-btn wf-btn-default wf-btn-sm" type="button">Clear</button>
														</div>
													</div>
													<div class="wf-form-group">
														<label for="wf-live-traffic-to" class="wf-col-sm-2">To:&nbsp;</label>
														<div class="wf-col-sm-10">
															<input placeholder="End date" id="wf-live-traffic-to" type="text" class="wf-datetime" data-bind="value: endDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
															<button data-bind="click: endDate('')" class="wf-btn wf-btn-default wf-btn-sm" type="button">Clear</button>
														</div>
													</div>
													<div class="wf-form-group">
														<label for="wf-live-traffic-group-by" class="wf-col-sm-2">Group&nbsp;By:&nbsp;</label>
														<div class="wf-col-sm-10">
															<select id="wf-live-traffic-group-by" name="groupby" class="wf-lt-advanced-filters-groupby" data-bind="options: filterGroupByOptions, optionsText: filterGroupByOptionsText, value: groupBy, optionsCaption: 'None'"></select>
														</div>
													</div>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<div class="wf-row">
								<div class="wf-col-xs-12">
									<div id="wf-live-traffic-group-by" class="wf-block" data-bind="if: groupBy(), visible: groupBy()">
										<ul class="wf-filtered-traffic wf-block-list" data-bind="foreach: listings">
											<li class="wf-flex-row wf-padding-add-top wf-padding-add-bottom">
												<div class="wf-flex-row-1">
													<!-- ko if: $root.groupBy().param() == 'ip' -->
													<div data-bind="if: loc()">
														<img data-bind="attr: { src: '<?php echo wfUtils::getBaseURL() . 'images/flags/'; ?>' + loc().countryCode.toLowerCase() + '.png',
																	alt: loc().countryName, title: loc().countryName }" width="16" height="11"
																class="wfFlag"/>
														<a data-bind="text: (loc().city ? loc().city + ', ' : '') + loc().countryName,
																	attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
																target="_blank" rel="noopener noreferrer"></a>
													</div>
													<div data-bind="if: !loc()">
														An unknown location at IP
														<span data-bind="text: IP" target="_blank" rel="noopener noreferrer"></span>
													</div>

													<div>
														<strong>IP:</strong>
														<span data-bind="text: IP" target="_blank" rel="noopener noreferrer"></span>
														<span data-bind="if: blocked()">
														[<a data-bind="click: $root.unblockIP">unblock</a>]
													</span>
														<span data-bind="if: rangeBlocked()">
														[<a data-bind="click: $root.unblockNetwork">unblock this range</a>]
													</span>
														<span data-bind="if: !blocked() && !rangeBlocked()">
														[<a data-bind="click: $root.blockIP">block</a>]
													</span>
													</div>
													<div>
														<span class="wfReverseLookup"><span data-bind="text: IP" style="display:none;"></span></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'type' -->
													<div>
														<strong>Type:</strong>
														<span data-bind="if: jsRun() == '1'">Human</span>
														<span data-bind="if: jsRun() == '0'">Bot</span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'user_login' -->
													<div>
														<strong>Username:</strong>
														<span data-bind="text: username()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'statusCode' -->
													<div>
														<strong>HTTP Response Code:</strong>
														<span data-bind="text: statusCode()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'action' -->
													<div>
														<strong>Firewall Response:</strong>
														<span data-bind="text: firewallAction()"></span>
													</div>
													<!-- /ko -->
													<!-- ko if: $root.groupBy().param() == 'url' -->
													<div>
														<strong>URL:</strong>
														<span data-bind="text: displayURL()"></span>
													</div>
													<!-- /ko -->
													<div>
														<strong>Last Hit:</strong> <span
																data-bind="attr: { 'data-timestamp': ctime, text: 'Last hit was ' + ctime() + ' ago.' }"
																class="wfTimeAgo wfTimeAgo-timestamp"></span>
													</div>
												</div>
												<div class="wf-flex-row-0 wf-padding-add-left">
													<span class="wf-filtered-traffic-hits" data-bind="text: hitCount"></span> hits
												</div>
											</li>

										</ul>
									</div>

									<div id="wf-live-traffic-no-group-by" data-bind="if: !groupBy()">
										<table class="wf-striped-table">
											<thead>
											<tr>
												<th>Type</th>
												<th>Location</th>
												<th>Page Visited</th>
												<th>Time</th>
												<th>IP Address</th>
												<th>Hostname</th>
												<th>Response</th>
												<th>View</th>
											</tr>
											</thead>
											<tbody id="wf-lt-listings" class="wf-filtered-traffic" data-bind="foreach: listings">
											<tr data-bind="click: toggleDetails, css: { odd: ($index() % 2 == 1), even: ($index() % 2 == 0), 'wf-details-open': showDetails, highlighted: highlighted }" class="wf-summary-row">
												<td class="wf-center">
													<span data-bind="attr: { 'class': cssClasses }"></span>
												</td>
												<td>
													<span data-bind="if: loc()">
														<img data-bind="attr: { src: '<?php echo wfUtils::getBaseURL() . 'images/flags/'; ?>' + loc().countryCode.toLowerCase() + '.png',
															alt: loc().countryName, title: loc().countryName }" width="16"
																height="11"
																class="wfFlag"/>
														<span data-bind="text: (loc().city ? loc().city + ', ' : '') + loc().countryName"></span>
													</span>
													<span data-bind="if: !loc()">
														<img src="<?php echo wfUtils::getBaseURL(); ?>images/flags/country-missing.svg" width="16" height="16" alt="" class="wfFlag"> Unspecified
													</span>
												</td>
												<td>
													<span class="wf-lt-url wf-split-word-xs"
															data-bind="text: displayURLShort, attr: { title: URL }"></span>
												</td>
												<td class="wf-nowrap" data-bind="text: timestamp"></td>
												<td>
													<span data-bind="attr: { title: IP }, text: $root.trimIP(IP())"></span>
												</td>
												<td>
													<span class="wfReverseLookup" data-reverse-lookup-template="wf-live-traffic-hostname-template">
														<span data-bind="text: IP" style="display:none;"></span>
													</span>
												</td>
												<td data-bind="text: statusCode"></td>
												<td class="wf-live-traffic-show-details">
													<span class="wf-ion-eye"></span>
													<span class="wf-ion-eye-disabled"></span>
												</td>
											</tr>
											<tr data-bind="css: {
												'wf-details-visible': showDetails,
												'wf-details-hidden': !(showDetails()),
												highlighted: highlighted,
												odd: ($index() % 2 == 1), even: ($index() % 2 == 0) }" class="wf-details-row">
												<td colspan="8" data-bind="attr: { id: ('wfActEvent_' + id()) }" class="wf-live-traffic-details">
													<div class="wf-live-traffic-activity-detail-wrapper">
														<div class="wf-live-traffic-activity-type">
															<div data-bind="attr: { 'class': typeIconClass }"></div>
															<div data-bind="text: typeText"></div>
														</div>
														<div class="wf-live-traffic-activity-detail">
															<h2>Activity Detail</h2>
															<div>
																<span data-bind="if: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user()">
																	<span data-bind="html: user.avatar" class="wfAvatar"></span>
																	<a data-bind="attr: { href: user.editLink }, text: user().display_name"
																			target="_blank" rel="noopener noreferrer"></a>
																</span>
																<span data-bind="if: loc()">
																	<span data-bind="if: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user()"> in</span>
																	<img data-bind="attr: { src: '<?php echo wfUtils::getBaseURL() . 'images/flags/'; ?>' + loc().countryCode.toLowerCase() + '.png',
																		alt: loc().countryName, title: loc().countryName }" width="16"
																			height="11"
																			class="wfFlag"/>
																	<a data-bind="text: (loc().city ? loc().city + ', ' : '') + loc().countryName,
																		attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
																			target="_blank" rel="noopener noreferrer"></a>
																</span>
																<span data-bind="if: !loc()">
																	<span data-bind="text: action() != 'loginOK' && action() != 'loginFailValidUsername' && action() != 'loginFailInvalidUsername' && user() ? 'at an' : 'An'"></span> unknown location at IP
																	<a data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }"
																			target="_blank" rel="noopener noreferrer"></a>
																</span>
																<span data-bind="if: referer()">
																	<span data-bind="if: extReferer()">
																		arrived from <a data-bind="text: LiveTrafficViewModel.truncateText(referer(), 100), attr: { title: referer, href: referer }"
																				target="_blank" rel="noopener noreferrer"
																				class="wf-split-word-xs"></a> and
																	</span>
																	<span data-bind="if: !extReferer()">
																		left <a data-bind="text: LiveTrafficViewModel.truncateText(referer(), 100), attr: { title: referer, href: referer }"
																				target="_blank" rel="noopener noreferrer"
																				class="wf-split-word-xs"></a> and
																	</span>
																</span>
																<span data-bind="if: statusCode() == 404">
																	tried to access <span style="color: #F00;">non-existent page</span>
																</span>

																<span data-bind="if: statusCode() == 200 && !action()">
																	visited
																</span>
																<span data-bind="if: statusCode() == 403 || statusCode() == 503">
																	was <span data-bind="text: firewallAction" style="color: #F00;"></span> at
																</span>

																<span data-bind="if: action() == 'loginOK'">
																	logged in successfully as "<strong data-bind="text: username"></strong>".
																</span>
																<span data-bind="if: action() == 'logout'">
																	logged out successfully.
																</span>
																<span data-bind="if: action() == 'lostPassword'">
																	requested a password reset.
																</span>
																<span data-bind="if: action() == 'loginFailValidUsername'">
																	attempted a failed login as "<strong data-bind="text: username"></strong>".
																</span>
																<span data-bind="if: action() == 'loginFailInvalidUsername'">
																	attempted a failed login using an invalid username "<strong
																			data-bind="text: username"></strong>".
																</span>
																<span data-bind="if: action() == 'user:passwordReset'">
																	changed their password.
																</span>
																<a class="wf-lt-url wf-split-word-xs"
																		data-bind="text: displayURL, attr: { href: URL, title: URL }"
																		target="_blank" rel="noopener noreferrer"></a>
															</div>
															<div>
																<span data-bind="text: timeAgo, attr: { 'data-timestamp': ctime }"
																		class="wfTimeAgo-timestamp"></span>&nbsp;&nbsp;
															</div>
															<div>
																<strong>IP:</strong> <span data-bind="text: IP"></span>
																<span class="wfReverseLookup">
																	<span data-bind="text: IP" style="display:none;"></span>
																</span>
																<span data-bind="if: blocked()">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: unblockIP">
																		Unblock IP
																	</a>
																</span>
																<span data-bind="if: rangeBlocked()">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: unblockNetwork">Unblock range
																	</a>
																</span>
																<span data-bind="if: !blocked() && !rangeBlocked()">
																	<a class="wf-btn wf-btn-default wf-btn-sm wf-block-ip-btn"
																			data-bind="click: blockIP">
																		Block IP
																	</a>
																</span>
															</div>
															<div data-bind="visible: (jQuery.inArray(parseInt(statusCode(), 10), [403, 503, 404]) !== -1)">
																<strong>Human/Bot:</strong> <span data-bind="text: (jsRun() === '1' ? 'Human' : 'Bot')"></span>
															</div>
															<div data-bind="if: browser() && browser().browser != 'Default Browser'">
																<strong>Browser:</strong>
																<span data-bind="text: browser().browser +
																(browser().version ? ' version ' + browser().version : '') +
																(browser().platform  && browser().platform != 'unknown' ? ' running on ' + browser().platform : '')
																"></span>
															</div>
															<div data-bind="text: UA"></div>
															<div class="wf-live-traffic-actions">
																<span data-bind="if: blocked()">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: unblockIP">
																		Unblock IP
																	</a>
																</span>
																<span data-bind="if: rangeBlocked()">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: unblockNetwork">Unblock range
																	</a>
																</span>
																<span data-bind="if: !blocked() && !rangeBlocked()">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: blockIP">
																		Block IP
																	</a>
																</span>
																<a class="wf-btn wf-btn-default wf-btn-sm" data-bind="click: showWhoisOverlay,
																attr: { href: 'admin.php?page=WordfenceTools&whoisval=' + IP() + '#top#whois' }"
																		target="_blank" rel="noopener noreferrer">Run Whois</a>
																<a class="wf-btn wf-btn-default wf-btn-sm"
																		data-bind="click: showRecentTraffic, attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank" rel="noopener noreferrer">
																	<span class="wf-hidden-xs"><?php _e('See recent traffic', 'wordfence'); ?></span><span class="wf-visible-xs"><?php _e('Recent', 'wordfence'); ?></span>
																</a>
																<span data-bind="if: action() == 'blocked:waf'">
																	<a href="#" class="wf-btn wf-btn-default wf-btn-sm"
																			data-bind="click: function () { $root.whitelistWAFParamKey(actionData().path, actionData().paramKey, actionData().failedRules) }"
																			title="If this is a false positive, you can exclude this parameter from being filtered by the firewall">
																		Whitelist param from Firewall
																	</a>
																	<?php if (WFWAF_DEBUG): ?>
																		<a href="#" class="wf-btn wf-btn-default wf-btn-sm"
																				data-bind="attr: { href: '<?php echo esc_js(home_url()) ?>?_wfsf=debugWAF&nonce=' + WFAD.nonce + '&hitid=' + id() }" target="_blank" rel="noopener noreferrer">
																			Debug this Request
																		</a>
																	<?php endif ?>
															</span>
															</div>
														</div>
													</div>
												</td>
											</tr>
											</tbody>
										</table>
									</div>
									<div class="wf-live-traffic-none" data-bind="if: listings().length == 0">
										No requests to report yet.
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="wf-live-traffic-util-overlay-wrapper" style="display: none">
	<div class="wf-live-traffic-util-overlay">
		<div class="wf-live-traffic-util-overlay-header"></div>
		<div class="wf-live-traffic-util-overlay-body"></div>
		<span class="wf-live-traffic-util-overlay-close wf-ion-android-close"></span>
	</div>
</div>

<div id="wfrawhtml"></div>

<script type="text/x-jquery-template" id="wf-live-traffic-hostname-template">
	<span title="${ip}">${(ip && ip.length > 22) ? '...' + ip.substring(ip.length - 22) : ip}</span>
</script>

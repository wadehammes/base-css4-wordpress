<?php if (wfConfig::liveTrafficEnabled()): ?>
	<div id="wfLiveTrafficOverlayAnchor"></div>
	<div id="wfLiveTrafficDisabledMessage">
		<h2>Live Updates Paused<br /><small>Click inside window to resume</small></h2>
	</div>
<?php endif ?>
<div class="wrap wordfence">
	<?php require('menuHeader.php'); ?>

	<h2 id="wfHeading">
		<div style="float: left;">
			Your Site Activity in Real-Time
		</div>
		<div class="wordfenceWrap" style="margin: 5px 0 0 15px; float: left;">
			<div class="wfOnOffSwitch" id="wfOnOffSwitchID">
				<input type="checkbox" name="wfOnOffSwitch" class="wfOnOffSwitch-checkbox"
				       id="wfLiveTrafficOnOff" <?php if (wfConfig::liveTrafficEnabled()) {
					echo ' checked ';
				} ?>>
				<label class="wfOnOffSwitch-label" for="wfLiveTrafficOnOff">
					<div class="wfOnOffSwitch-inner"></div>
					<div class="wfOnOffSwitch-switch"></div>
				</label>
			</div>
		</div>
	</h2>
	<a href="http://docs.wordfence.com/en/Live_traffic" target="_blank" class="wfhelp"></a><a
		href="http://docs.wordfence.com/en/Live_traffic" target="_blank">Learn more about Wordfence Live Traffic</a>

	<div class="wordfenceModeElem" id="wordfenceMode_activity"></div>
	<div class="wordfenceLive">
		<table border="0" cellpadding="0" cellspacing="0" class="wordfenceLiveActivity">
			<tr>
				<td><h2>Wordfence Live Activity:</h2></td>
				<td id="wfLiveStatus"></td>
			</tr>
		</table>
		<table border="0" cellpadding="0" cellspacing="0" class="wordfenceLiveStateMessage">
			<tr>
				<td>Live Updates Paused &mdash; Click inside window to resume</td>
			</tr>
		</table>
	</div>
	<div class="wordfenceWrap<?php if (!wfConfig::get('isPaid')) { echo " wordfence-community"; }?>">
		<?php
		$rightRail = new wfView('marketing/rightrail', array('additionalClasses' => 'wordfenceRightRailLiveTraffic'));
		echo $rightRail;
		?>
		<?php if (!wfConfig::liveTrafficEnabled()): ?>
			<div id="wordfenceLiveActivityDisabled"><p><strong>Live activity is disabled.</strong> <?php if (wfConfig::get('cacheType') == 'falcon') { ?>This is done to improve performance because you have Wordfence Falcon Engine enabled.<?php } ?> Login and firewall activity will still appear below.</p></div>
		<?php endif ?>
		
		<div id="wf-live-traffic" class="wfTabsContainer">

				<div id="wf-live-traffic-legend">
					<ul>
						<li class="wfHuman">Human</li>
						<li class="wfBot">Bot</li>
						<li class="wfNotice">Warning</li>
						<li class="wfBlocked">Blocked</li>
					</ul>
				</div>

				<form data-bind="submit: reloadListings">

					<?php if (defined('WP_DEBUG') && WP_DEBUG && false): ?>
						<pre data-bind="text: 'DEBUG: ' + sql(), visible: sql"></pre>
					<?php endif ?>

					<div class="wfActEvent">
						<h2 style="float: left;padding: 0;margin: 0 10px 0 0;">Filter Traffic: </h2>

						<select id="wf-lt-preset-filters" data-bind="options: presetFiltersOptions, optionsText: presetFiltersOptionsText,
							value: selectedPresetFilter">
						</select>
						&nbsp;&nbsp;
						<label>
							<input data-bind="checked: showAdvancedFilters" type="checkbox">
							Show Advanced Filters
						</label>
					</div>

					<div class="wfActEvent" data-bind="visible: showAdvancedFilters" id="wf-lt-advanced-filters">
						<table>
							<tr>
								<td>
									<table>
										<tbody data-bind="foreach: filters">
										<tr>
											<td>
												<select name="param[]" class="wf-lt-advanced-filters-param" data-bind="options: filterParamOptions,
												optionsText: filterParamOptionsText, value: selectedFilterParamOptionValue, optionsCaption: 'Filter...'"></select>
											</td>
											<td data-bind="visible: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() != 'bool'">
												<select name="operator[]" class="wf-lt-advanced-filters-operator"
												        data-bind="options: filterOperatorOptions,
												optionsText: filterOperatorOptionsText, value: selectedFilterOperatorOptionValue"></select>
											</td>
											<td data-bind="attr: {colSpan: (selectedFilterParamOptionValue() &&
												selectedFilterParamOptionValue().type() == 'bool' ? 2 : 1)}"
											    class="wf-lt-advanced-filters-value-cell">

												<span
													data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'enum'">
													<select
														data-bind="options: selectedFilterParamOptionValue().values,
														optionsText: selectedFilterParamOptionValue().optionsText,
														value: value"></select>
												</span>

												<span
													data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'text'">
													<input data-bind="value: value" type="text"/>
												</span>

												<span
													data-bind="if: selectedFilterParamOptionValue() && selectedFilterParamOptionValue().type() == 'bool'">
													<label>Yes <input data-bind="checked: value" type="radio"
													                  value="1"></label>
													<label>No <input data-bind="checked: value" type="radio"
													                 value="0"></label>
												</span>

											</td>
											<td>
												<button data-bind="click: $root.removeFilter" type="button"
												        class="button">
													Remove
												</button>
											</td>
										</tr>
										</tbody>
										<tbody>
										<tr>
											<td colspan="3">
												<div class="wf-pad-small">
													<button type="button" class="button" data-bind="click: addFilter">
														Add Filter
													</button>
												</div>
											</td>
										</tr>
										</tbody>
									</table>
								</td>
								<td>
									<table>
										<tbody>
										<tr>
											<td>
												<label for="wf-live-traffic-from">From:&nbsp;</label>
											</td>
											<td><input placeholder="Start date" id="wf-live-traffic-from" type="text"
											           class="wf-datetime"
											           data-bind="value: startDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
											</td>
											<td>
												<button data-bind="click: startDate('')" class="button small"
												        type="button">
													Clear
												</button>
											</td>
										</tr>
										<tr>
											<td>
												<label for="wf-live-traffic-to">To:&nbsp;</label>
											</td>
											<td><input placeholder="End date" id="wf-live-traffic-to" type="text"
											           class="wf-datetime"
											           data-bind="value: endDate, datetimepicker: null, datepickerOptions: { timeFormat: 'hh:mm tt z' }">
											</td>
											<td>
												<button data-bind="click: endDate('')" class="button small"
												        type="button">
													Clear
												</button>
											</td>
										</tr>
										<tr>
											<td>
												<label for="wf-live-traffic-group-by">Group&nbsp;By:&nbsp;</label>
											</td>
											<td>
												<select id="wf-live-traffic-group-by" name="groupby"
												        class="wf-lt-advanced-filters-groupby"
												        data-bind="options: filterGroupByOptions,
														optionsText: filterGroupByOptionsText, value: groupBy, optionsCaption: 'None'"></select>
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</table>
					</div>
				</form>

				<table data-bind="if: groupBy()" border="0" style="width: 100%">
					<tbody data-bind="foreach: listings">
					<tr>
						<td>
							<div data-bind="if: loc()">
								<img data-bind="attr: { src: '<?php echo wfUtils::getBaseURL() . 'images/flags/'; ?>' + loc().countryCode.toLowerCase() + '.png',
											alt: loc().countryName, title: loc().countryName }" width="16" height="11"
								     class="wfFlag"/>
								<a data-bind="text: (loc().city ? loc().city + ', ' : '') + loc().countryName,
											attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
								   target="_blank"></a>
							</div>
							<div data-bind="if: !loc()">
								An unknown location at IP <a
									data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank"></a>
							</div>

							<div>
								<strong>IP:</strong>&nbsp;<a
									data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank"></a>
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
								&nbsp;<span class="wfReverseLookup"><span data-bind="text: IP"
								                                          style="display:none;"></span></span>
							</div>
							<div>
								<span
									data-bind="attr: { 'data-timestamp': ctime, text: 'Last hit was ' + ctime() + ' ago.' }"
									class="wfTimeAgo wfTimeAgo-timestamp"></span>
							</div>
						</td>
						<td style="font-size: 28px; color: #999;">
							<span data-bind="text: hitCount"></span> hits
						</td>
					</tr>
					</tbody>
				</table>

				<div data-bind="if: !groupBy()">
					<div id="wf-lt-listings" data-bind="foreach: listings">
						<div data-bind="attr: { id: ('wfActEvent_' + id()), 'class': cssClasses }">
							<table border="0" cellpadding="1" cellspacing="0">
								<tr>
									<td>
										<span data-bind="if: action() != 'loginOK' && user()">
											<span data-bind="html: user.avatar" class="wfAvatar"></span>
											<a data-bind="attr: { href: user.editLink }, text: user().display_name"
											   target="_blank"></a>
										</span>
										<span data-bind="if: loc()">
											<span data-bind="if: action() != 'loginOK' && user()"> in</span>
											<img data-bind="attr: { src: '<?php echo wfUtils::getBaseURL() . 'images/flags/'; ?>' + loc().countryCode.toLowerCase() + '.png',
												alt: loc().countryName, title: loc().countryName }" width="16"
											     height="11"
											     class="wfFlag"/>
											<a data-bind="text: (loc().city ? loc().city + ', ' : '') + loc().countryName,
												attr: { href: 'http://maps.google.com/maps?q=' + loc().lat + ',' + loc().lon + '&z=6' }"
											   target="_blank"></a>
										</span>
										<span data-bind="if: !loc()">
											<span
												data-bind="text: action() != 'loginOK' && user() ? 'at an' : 'An'"></span> unknown location at IP <a
												data-bind="text: IP, attr: { href: WFAD.makeIPTrafLink(IP()) }"
												target="_blank"></a>
										</span>
										<span data-bind="if: referer()">
											<span data-bind="if: extReferer()">
												arrived from <a data-bind="text: referer, attr: { href: referer }"
												                target="_blank"
												                style="color: #A00; font-weight: bold;"></a> and
											</span>
											<span data-bind="if: !extReferer()">
												left <a data-bind="text: referer, attr: { href: referer }"
												        target="_blank"
												        style="color: #999; font-weight: normal;"></a> and
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
										<a class="wf-lt-url"
										   data-bind="text: displayURL, attr: { href: URL, title: URL }"
										   target="_blank"></a>
									</td>
								</tr>
								<tr>
									<td><span data-bind="text: timeAgo, attr: { 'data-timestamp': ctime }"
									          class="wfTimeAgo wfTimeAgo-timestamp"></span>&nbsp;&nbsp;
										<strong>IP:</strong> <a
											data-bind="attr: { href: WFAD.makeIPTrafLink(IP()) }, text: IP"
											target="_blank"></a>
										<span data-bind="if: blocked()">
											[<a data-bind="click: $root.unblockIP">unblock</a>]
										</span>
										<span data-bind="if: rangeBlocked()">
											[<a data-bind="click: $root.unblockNetwork">unblock this range</a>]
										</span>
										<span data-bind="if: !blocked() && !rangeBlocked()">
											[<a data-bind="click: $root.blockIP">block</a>]
										</span>
										&nbsp;
										<span class="wfReverseLookup">
											<span data-bind="text: IP"
											      style="display:none;"></span>
										</span>
									</td>
								</tr>

								<tr data-bind="if: browser() && browser().browser != 'Default Browser'">
									<td>
										<strong>Browser:</strong>
										<span data-bind="text: browser().browser +
											(browser().version ? ' version ' + browser().version : '') +
											(browser().platform  && browser().platform != 'unknown' ? ' running on ' + browser().platform : '')
											">
										</span>
									</td>
								</tr>
								<tr>
									<td data-bind="text: UA" style="color: #AAA;"></td>
								</tr>
								<tr>
									<td>
										<span data-bind="if: blocked()">
											<a href="#" class="button button-small"
											        data-bind="click: $root.unblockIP">
												Unblock this IP
											</a>
										</span>
										<span data-bind="if: rangeBlocked()">
											<a href="#" class="button button-small"
											        data-bind="click: $root.unblockNetwork">Unblock this range
											</a>
										</span>
										<span data-bind="if: !blocked() && !rangeBlocked()">
											<a href="#" class="button button-small"
											        data-bind="click: $root.blockIP">
												Block this IP
											</a>
										</span>
										<a class="button button-small"
										        data-bind="attr: { href: 'admin.php?page=WordfenceWhois&whoisval=' + IP() + '&wfnetworkblock=1'}">
											Block this network
										</a>
										<a class="button button-small" data-bind="text: 'Run WHOIS on ' + IP(),
											attr: { href: 'admin.php?page=WordfenceWhois&whoisval=' + IP() }"
										        target="_blank"></a>
										<a class="button button-small"
										        data-bind="attr: { href: WFAD.makeIPTrafLink(IP()) }" target="_blank">
											See recent traffic
										</a>
										<span data-bind="if: action() == 'blocked:waf'">
											<a href="#" class="button button-small"
											        data-bind="click: function () { $root.whitelistWAFParamKey(actionData().path, actionData().paramKey, actionData().failedRules) }"
											        title="If this is a false positive, you can exclude this parameter from being filtered by the firewall">
												Whitelist param from Firewall
											</a>
											<?php if (WFWAF_DEBUG): ?>
												<a href="#" class="button button-small"
												        data-bind="attr: { href: '<?php echo esc_js(home_url()) ?>?_wfsf=debugWAF&nonce=' + WFAD.nonce + '&hitid=' + id() }" target="_blank">
													Debug this Request
												</a>
											<?php endif ?>
										</span>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
				<div data-bind="if: !listings">
					No events to report yet.
				</div>
			</div>
	</div>
</div>

<script type="text/x-jquery-template" id="wfWelcomeContent3">
	<div>
		<h3>Welcome to ALL Your Site Visits, Live!</h3>
		<strong><p>Traffic you've never seen before</p></strong>

		<p>
			Google Analytics and other Javascript analytics packages can't show you crawlers, RSS feed readers, hack
			attempts and other non-human traffic that hits your site.
			Wordfence runs on your server and shows you, in real-time, all the traffic that is hitting your server right
			now, including those non-human crawlers, feed readers and hackers that Analytics can't track.
		</p>
		<strong><p>Separated into the important categories</p></strong>

		<p>
			You'll notice that you can filter traffic. The options include "All Hits" to simply view everything that is
			hitting your server right now. We then sub-divide that into human visits, your site members, crawlers -
			which we further break down into Google crawlers - and various other choices.
		</p>

		<p>
			<strong>How to use this page when your site is being attacked</strong>
		</p>

		<p>
			Start by looking at "All Hits" because you may notice that a single IP address is generating most of your
			traffic.
			This could be a denial of service attack, someone stealing your content or a hacker probing for weaknesses.
			If you see a suspicious pattern, simply block that IP address. If they attack from a different IP on the
			same network, simply block that network.
			You can also run a WHOIS on any IP address to find the host and report abuse via email.
		</p>

		<p>
			If you don't see any clear patterns of attack, take a look at "Pages Not Found" which will show you IP
			addresses that are generating excessive page not found errors. It's common for an attacker probing for
			weaknesses to generate a lot of these errors. If you see one IP address that is generating many of these
			requests, and it's not Google or another trusted crawler, then you should consider blocking them.
		</p>

		<p>
			Next look at "Logins and Logouts". If you see a large number of failed logins from an IP address, block them
			if you don't recognize who they are.
		</p>

	</div>
</script>

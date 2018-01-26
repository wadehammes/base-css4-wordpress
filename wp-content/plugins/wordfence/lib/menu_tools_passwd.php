<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
?>
<script type="application/javascript">
	(function($) {
		$(function() {
			document.title = "<?php esc_attr_e('Password Audit', 'wordfence'); ?>" + " \u2039 " + WFAD.basePageName;
		});
	})(jQuery);
</script>
<div id="wf-tools-password-audit">
	<div class="wf-section-title">
		<h2><?php _e('Password Audit', 'wordfence') ?></h2>
		<span><?php printf(__('<a href="%s" target="_blank" rel="noopener noreferrer" class="wf-help-link">Learn more<span class="wf-hidden-xs"> about Password Auditing</span></a>', 'wordfence'), wfSupportController::esc_supportURL(wfSupportController::ITEM_TOOLS_PASSWD_AUDIT)); ?>
			<i class="wf-fa wf-fa-external-link" aria-hidden="true"></i></span>
	</div>

	<p><?php _e("Audit your site passwords by having us securely simulate a password cracking attempt using our high performance servers. Your report will appear here and you can easily alert your users to a weak password or change their passwords and email them the change.", 'wordfence') ?></p>

	<?php if (!wfConfig::get('isPaid')): ?>
		<div class="wf-premium-callout wf-add-bottom">
			<h3><?php _e("Identify Login Security Risks Before It's Too Late With Password Auditing", 'wordfence') ?></h3>
			<p><?php _e('Strong passwords are crucial to the security of your site, and Wordfence ensures your passwords are strong by checking them against a database of common passwords and simulating a crack attempt using our password auditing GPU cluster. A Wordfence Password Audit simulates what a hacker would do if they stole your password database and launched an attack on it. Upgrade to Premium to use this powerful feature.', 'wordfence') ?></p>

			<p><img src="<?php echo wfUtils::getBaseURL() . 'images/password-audit.svg' ?>" alt=""></p>

			<p class="center">
				<a class="wf-btn wf-btn-primary wf-btn-callout" href="https://www.wordfence.com/gnl1pwAuditUp1/wordfence-signup/" target="_blank" rel="noopener noreferrer"><?php _e('Get Premium', 'wordfence') ?></a>
			</p>
		</div>

	<?php else: ?>
		<div>
			<div id="wordfenceMode_passwd"></div>

			<div>
				<div class="wf-row">
					<div class="wf-col-xs-12">
						<div class="wf-block wf-active">
							<div class="wf-block-content wf-clearfix">
								<ul class="wf-block-list">
									<li class="wf-block-list-title">
										<strong><?php _e('Start a Password Audit', 'wordfence'); ?></strong>
									</li>
									<li>
										<div class="wf-form-horizontal wf-form-control-static">
											<div class="wf-form-group wf-form-field">
												<label for="auditType" class="wf-col-sm-4 wf-control-label wf-padding-no-top"><?php _e('Select the kind of audit you would like to do', 'wordfence') ?></label>
												<div class="wf-col-sm-8 wf-option-select">
													<select id="auditType" class="wf-select2 wf-form-control">
														<option value="admin"><?php _e('Audit administrator level accounts (extensive audit against a large dictionary of approx. 609 million passwords)', 'wordfence') ?></option>
														<option value="user"><?php _e('Audit user level accounts (less extensive against a dictionary of approximately 50,000 passwords)', 'wordfence') ?></option>
														<option value="both"><?php _e('Audit all WordPress accounts', 'wordfence') ?></option>
													</select>
												</div>
											</div>
											<div class="wf-form-group wf-form-field">
												<label for="emailAddr" class="wf-col-sm-4 wf-control-label wf-padding-no-top"><?php _e('Notify when ready', 'wordfence') ?></label>
												<div class="wf-col-sm-8 wf-option-text">
													<input type="text" id="emailAddr" class="wf-form-control" size="50" maxlength="255" value="<?php wfConfig::f('alertEmails') ?>">
												</div>
											</div>
											<div class="wf-form-group">
												<div class="wf-col-sm-8 wf-col-sm-offset-4">
													<input id="wf-start-passwd-audit" type="button" name="but4" class="wf-btn wf-btn-primary" value="<?php esc_attr_e('Start Password Audit', 'wordfence') ?>">
													<input id="wf-stop-passwd-audit" style="display: none;" type="button" name="but4" class="wf-btn wf-btn-primary" value="<?php esc_attr_e('Stop Password Audit', 'wordfence') ?>">
												</div>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<div id="wfAuditJobs"></div>

				<div id="wfAuditResults"></div>
			</div>
		</div>

		<script type="text/javascript">
			(function($) {
				$(function() {
					$(window).on('wf-passwd-audit-running', function(evt, jobID) {
						startPasswdAudit.hide();
						stopPasswdAudit.show()
						.data('jobID', jobID);

						$('#auditType').add('#emailAddr').attr('disabled', true);
					});
					$(window).on('wf-passwd-audit-not-running', function() {
						startPasswdAudit.show()
						.attr('disabled', false);
						stopPasswdAudit.hide();

						$('#auditType').add('#emailAddr').attr('disabled', false);
					});

					var startPasswdAudit = $('#wf-start-passwd-audit').on('click', function() {
						$(this).attr('disabled', true);
						WFAD.startPasswdAudit($('#auditType').val(), $('#emailAddr').val());
					});
					var stopPasswdAudit = $('#wf-stop-passwd-audit').on('click', function() {
						var runningJobID = $(this).data('jobID');
						if (runningJobID) {
							WFAD.killPasswdAudit(runningJobID);
						}
					})
				});
			})(jQuery);
		</script>
		<script type="text/x-jquery-template" id="wfAuditResultsStarted">
			<h2><?php _e('Password Audit Results', 'wordfence') ?></h2>
			<p><?php _e('Your password audit started successfully. The results will appear here once it is complete.', 'wordfence') ?></p>
			<p><?php printf(__('You will also receive an email letting you know the results are ready at: %s', 'wordfence'), '${emailAddr}') ?></p>
		</script>
		<script type="text/x-jquery-template" id="wfAuditResultsNoWeakPasswords">
			<h2><?php _e('Password Audit Results', 'wordfence') ?></h2>
			<p><?php _e("You don't have any user accounts with a weak password at this time.", 'wordfence') ?></p>
		</script>
		<script type="text/x-jquery-template" id="wfAuditResultsTable">
			<div class="wf-add-top wf-flex-row wf-form-field">
				<h2 class="wf-flex-row-0"><?php _e('Password Audit Results', 'wordfence') ?></h2>
				<div class="wf-flex-row-1 wf-padding-add-left wf-padding-add-right wf-option-select">
					<select style="width: 100%;" id="wfPasswdFixAction" class="wf-select2">
						<option value="email"><?php _e('Action: Email selected users and ask them to change their weak password.', 'wordfence') ?></option>
						<option value="fix"><?php _e('Action: Change weak passwords to a strong password and email users the new password.', 'wordfence') ?></option>
					</select>
				</div>
				<input type="button" value="<?php esc_attr_e('Fix Weak Passwords', 'wordfence') ?>" onclick="WFAD.doFixWeakPasswords(); return false;" class="wf-btn wf-btn-primary wf-flex-row-0"/>
			</div>
			<table class="wf-striped-table">
				<thead>
				<th style="text-align: center">
					<input class="wf-option-checkbox" type="checkbox" id="wfSelectAll" onclick="jQuery('.wfUserCheck').attr('checked', this.checked);"/>
					<label for="wfSelectAll"></label>
				</th>
				<th><?php _e('User Level', 'wordfence') ?></th>
				<th><?php _e('Username', 'wordfence') ?></th>
				<th><?php _e('Full Name', 'wordfence') ?></th>
				<th><?php _e('Email', 'wordfence') ?></th>
				<th><?php _e('Password', 'wordfence') ?></th>
				<th><?php _e('Crack Time', 'wordfence') ?></th>
				<th><?php _e('Crack Difficulty', 'wordfence') ?></th>
				</thead>
				<tbody class="wf-pw-audit-tbody"></tbody>
			</table>
		</script>

		<script type="text/x-jquery-template" id="wfAuditResultsRow">
			<tr>
				<td style="text-align: center;">
					<input id="wf-passwd-audit-user-${wpUserID}" type="checkbox" class="wfUserCheck wf-option-checkbox" value="${wpUserID}"/>
					<label for="wf-passwd-audit-user-${wpUserID}"></label>
				</td>
				<td>{{if wpIsAdmin == '1'}}<span style="color: #F00;"><?php _e('Admin', 'wordfence') ?></span>{{else}}<?php _e('User', 'wordfence') ?>{{/if}}
				</td>
				<td>${username}</td>
				<td>${firstName} ${lastName}</td>
				<td>${email}</td>
				<td>${starredPassword}</td>
				<td>${crackTime}</td>
				<td>${crackDifficulty}</td>
			</tr>
		</script>

		<script type="text/x-jquery-template" id="wfAuditJobsTable">
			<h2><?php _e('Audit Status', 'wordfence') ?></h2>
			<table id="wf-passwd-audit-jobs" class="wf-striped-table">
				<thead>
				<th><?php _e('Audit Type', 'wordfence') ?></th>
				<th><?php _e('Admin Accounts', 'wordfence') ?></th>
				<th><?php _e('User Accounts', 'wordfence') ?></th>
				<th><?php _e('Run Time', 'wordfence') ?></th>
				<th><?php _e('Email results to', 'wordfence') ?></th>
				<th><?php _e('Weak Passwords Found', 'wordfence') ?></th>
				<th colspan="2"><?php _e('Status', 'wordfence') ?></th>
				</thead>
				<tbody class="wf-pw-audit-tbody"></tbody>
			</table>
		</script>
		<script type="text/x-jquery-template" id="wfAuditJobsInProg">
			<tr data-job-status="${jobStatus}" data-job-id="${id}">
				<td>
					{{if auditType == 'admin'}}
					<?php _e('Admin Accounts', 'wordfence') ?>
					{{else auditType == 'user'}}
					<?php _e('User Accounts', 'wordfence') ?>
					{{else auditType == 'both'}}
					<?php _e('All WordPress Accounts', 'wordfence') ?>
					{{/if}}
				</td>
				<td>${totalAdmins}</td>
				<td>${totalUsers}</td>
				<td>${WFAD.makeTimeAgo(timeTaken)}</td>
				<td>${email}</td>
				<td>${weakFound}</td>
				{{if jobStatus == 'done'}}
				<td colspan="2">
					<span class="wf-diagnostic-success"><?php _e('Complete', 'wordfence') ?></span>
				</td>
				{{else jobStatus == 'killed'}}
				<td colspan="2">
					<span class="wf-diagnostic-fail"><?php _e('Stopped', 'wordfence') ?></span>
				</td>
				{{else jobStatus == 'queued'}}
				<td colspan="2">
					<span class="wf-diagnostic-fail"><?php _e('Queued', 'wordfence') ?></span>
				</td>
				{{else jobStatus == 'running'}}
				<td colspan="2">
					<span class="wf-diagnostic-success"><?php _e('Running', 'wordfence') ?></span>
				</td>
				{{/if}}
			</tr>
		</script>
	<?php endif ?>
</div>
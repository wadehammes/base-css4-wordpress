<?php
if (!defined('WORDFENCE_VERSION')) { exit; }
/**
 * Presents the list of blocks.
 *
 */
?>
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-flex-horizontal wf-flex-full-width wf-add-bottom-small">
			<h3 class="wf-no-top wf-no-bottom"><?php printf(__('All blocks<span class="wf-hidden-xs"> for %s</span>', 'wordfence'), preg_replace('/^https?:\/\//i', '', wfUtils::wpSiteURL())); ?></h3>
			<div class="wf-right">
				<div class="wf-inline-block">
				<ul class="wf-option wf-option-toggled-boolean-switch wf-option-no-spacing" data-option="displayAutomaticBlocks" data-enabled-value="1" data-disabled-value="0" data-original-value="<?php echo wfConfig::get('displayAutomaticBlocks') ? 1 : 0; ?>">
					<li class="wf-boolean-switch<?php echo wfConfig::get('displayAutomaticBlocks') ? ' wf-active' : ''; ?>"><a href="#" class="wf-boolean-switch-handle"></a></li>
					<li class="wf-option-title wf-padding-add-left wf-no-right wf-padding-no-right">
						<?php echo __('Show<span class="wf-hidden-xs"> Wordfence</span> Automatic<span class="wf-hidden-xs"> Blocks</span>', 'wordfence'); ?> 
					</li>
				</ul>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="wf-row">
	<div class="wf-col-xs-12">
		<div class="wf-row">
			<div class="wf-col-xs-12">
				<div class="wf-block wf-block-no-header wf-active">
					<div class="wf-block-content wf-padding-add-top-large wf-padding-add-bottom-large">
						<ul class="wf-flex-horizontal wf-flex-full-width wf-no-top">
							<li></li>
							<li class="wf-right wf-flex-vertical-xs">
								<a href="#" id="blocks-bulk-unblock" class="wf-btn wf-btn-callout wf-btn-default"><?php _e('Unblock', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="#" id="blocks-bulk-make-permanent" class="wf-btn wf-btn-callout wf-btn-default"><?php _e('Make Permanent', 'wordfence'); ?></a>&nbsp;&nbsp;<a href="<?php echo wfUtils::siteURLRelative(); ?>?_wfsf=blockedIPs&amp;nonce=<?php echo wp_create_nonce('wp-ajax'); ?>" id="blocks-export-ips" class="wf-btn wf-btn-callout wf-btn-default"><?php _e('Export<span class="wf-hidden-xs"> All IPs</span>', 'wordfence'); ?></a>
							</li>
						</ul>
						<div class="wf-block wf-block-no-padding wf-block-no-header wf-active wf-no-bottom wf-overflow-y-auto-xs">
							<div class="wf-block-content">
								<div id="wf-blocks-wrapper"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div> <!-- end block list -->
<script type="text/x-jquery-template" id="wf-blocks-tmpl">
	<div class="wf-blocks-table-container">
		<table class="wf-striped-table wf-blocks-table">
			<thead>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
			</tfoot>
		</table>
	</div>
</script>
<script type="text/x-jquery-template" id="wf-blocks-columns-tmpl">
	<tr class="wf-blocks-columns">
		<th style="width: 2%;text-align: center"><div class="wf-blocks-bulk-select wf-option-checkbox"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></th>
		<th><?php _e('Block Type', 'wordfence'); ?></th>
		<th><?php _e('Detail', 'wordfence'); ?></th>
		<th><?php _e('Rule Added', 'wordfence'); ?></th>
		<th><?php _e('Reason', 'wordfence'); ?></th>
		<th><?php _e('Expiration', 'wordfence'); ?></th>
		<th><?php _e('Block Count', 'wordfence'); ?></th>
		<th><?php _e('Last Attempt', 'wordfence'); ?></th>
	</tr>
</script>
<script type="text/x-jquery-template" id="wf-no-blocks-tmpl">
	<tr id="wf-no-blocks">
		<td colspan="8"><?php _e('No blocks are currently active.', 'wordfence'); ?></td>
	</tr>
</script>
<script type="text/x-jquery-template" id="wf-blocks-loading-tmpl">
	<tr id="wf-blocks-loading">
		<td colspan="8" class="wf-center wf-padding-add-top wf-padding-add-bottom">
			<?php
			echo wfView::create('common/indeterminate-progress', array(
				'size' => 50,
			))->render();
			?>
		</td>
	</tr>
</script>
<script type="text/x-jquery-template" id="wf-block-row-tmpl">
	<tr class="wf-block-record" data-id="${id}" data-expiration="${expiration}">
		<td style="text-align: center;"><div class="wf-blocks-table-bulk-checkbox wf-option-checkbox"><i class="wf-ion-ios-checkmark-empty" aria-hidden="true"></i></div></td>
		<td data-column="type" data-sort="${typeSort}">${typeDisplay}</td>
		<td data-column="detail" data-sort="${detailSort}">${detailDisplay}{{if (editable)}}&nbsp;<a href="#" class="wf-block-edit" data-edit-type="${editType}" data-edit-values="${editValues}"><i class="wf-ion-edit" aria-hidden="true"></i></a>{{/if}}</td>
		<td data-column="ruleAdded" data-sort="${ruleAddedSort}">${ruleAddedDisplay}</td>
		<td data-column="reason" data-sort="${reasonSort}">${reasonDisplay}</td>
		<td data-column="expiration" data-sort="${expirationSort}">${expirationDisplay}</td>
		<td data-column="blockCount" data-sort="${blockCountSort}">${blockCountDisplay}</td>
		<td data-column="lastAttempt" data-sort="${lastAttemptSort}">${lastAttemptDisplay}</td>
	</tr>
</script>
<script type="application/javascript">
	(function($) {
		WFAD.blockHeaderCheckboxAction = function(checkbox) { //Top-level checkboxes
			$('.wf-blocks-bulk-select.wf-option-checkbox').toggleClass('wf-checked');
			var checked = $(checkbox).hasClass('wf-checked');
			$('.wf-blocks-table-bulk-checkbox.wf-option-checkbox').toggleClass('wf-checked', checked);
			$(window).trigger('wordfenceUpdateBlockButtons');
		};
		
		
		$(window).on('wordfenceRefreshBlockList', function(e, payload, append) {
			if (!payload.hasOwnProperty('loading')) {
				payload['loading'] = false;
			}
			
			//Create table if needed
			var table = $(".wf-blocks-table-container");
			if (!append || table.length == 0) {
				$(".wf-blocks-table-container").remove();
				var wrapperTemplate = $('#wf-blocks-tmpl').tmpl();
				$('#wf-blocks-wrapper').append(wrapperTemplate);
				table = $(".wf-blocks-table-container");
			}
			
			//Create header if needed
			if (table.find('thead > .wf-blocks-columns').length == 0) {
				table.find('thead').append($('#wf-blocks-columns-tmpl').tmpl());
				table.find('thead .wf-blocks-bulk-select.wf-option-checkbox').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					WFAD.blockHeaderCheckboxAction($(this));
				});
			}
			
			//Create or remove footer if needed
			if (payload['blocks'].length > 5 && table.find('tfoot > .wf-blocks-columns').length == 0) {
				table.find('tfoot').append($('#wf-blocks-columns-tmpl').tmpl());
				table.find('tfoot .wf-blocks-bulk-select.wf-option-checkbox').on('click', function(e) {
					e.preventDefault();
					e.stopPropagation();
					WFAD.blockHeaderCheckboxAction($(this));
				});
			}
			else {
				table.find('tfoot > .wf-blocks-columns').remove();
			}
			
			//Add row(s)
			$('#wf-blocks-loading').remove();
			if (!append && payload['blocks'].length == 0) {
				table.find('tbody').append($('#wf-no-blocks-tmpl').tmpl());
			}
			else {
				$('#wf-no-blocks').remove();
				for (var i = 0; i < payload['blocks'].length; i++) {
					var row = $('#wf-block-row-tmpl').tmpl(payload['blocks'][i]);

					row.find('.wf-blocks-table-bulk-checkbox.wf-option-checkbox').on('click', function() { //Individual checkboxes
						e.preventDefault();
						e.stopPropagation();

						$(this).toggleClass('wf-checked');
						$(window).trigger('wordfenceUpdateBulkSelect');
						$(window).trigger('wordfenceUpdateBlockButtons');
					});

					row.find('.wf-block-edit').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						var editType = $(this).data('editType');
						$('#wf-block-type > li > a[data-value="' + editType + '"]').trigger('click');
					});
					
					var existing = table.find('tbody tr[data-id="' + payload['blocks'][i]['id'] + '"]');
					if (existing.length > 0) {
						existing.replaceWith(row);
					}
					else {
						table.find('tbody').append(row);
					}
				}
			}
			
			$('#wf-blocks-wrapper').data('hasCountryBlock', !!payload.hasCountryBlock ? 1 : 0);

			$(window).trigger('wordfenceUpdateBlockButtons');
		});

		$(window).on('wordfenceUpdateBlockButtons', function() {
			var totalCount = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox').length;
			var checked = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox.wf-checked');
			var allowUnblock = false;
			var allowMakeForever = false;
			for (var i = 0; i < checked.length; i++) {
				var tr = $(checked[i]).closest('tr');
				if (tr.is(':visible')) {
					allowUnblock = true;
					if (tr.data('expiration') > 0) {
						allowMakeForever = true;
					}
				}
			}
			
			$('#blocks-bulk-unblock').toggleClass('wf-disabled', !allowUnblock);
			$('#blocks-bulk-make-permanent').toggleClass('wf-disabled', !allowMakeForever);
			$('#blocks-export-ips').toggleClass('wf-disabled', (totalCount == 0));
		});

		$(window).on('wordfenceUpdateBulkSelect', function() {
			var totalCount = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox:visible').length;
			var checkedCount = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox.wf-checked:visible').length;
			$('.wf-blocks-bulk-select.wf-option-checkbox:visible').toggleClass('wf-checked', (totalCount > 0 && checkedCount == totalCount));
		});
		
		$(function() {
			$(window).trigger('wordfenceRefreshBlockList', [{blocks: [], loading: true}, false]);
			
			WFAD.loadingBlocks = true;
			WFAD.ajax('wordfence_getBlocks', {offset: 0}, function(res) {
				$(window).trigger('wordfenceRefreshBlockList', [res, false]);
				WFAD.loadingBlocks = false;
			});

			var issuesWrapper = $('#wf-blocks-wrapper');
			var hasScrolled = false;
			$(window).on('scroll', function() {
				var win = $(this);
				var currentScrollBottom = win.scrollTop() + window.innerHeight;
				var scrollThreshold = issuesWrapper.outerHeight() + issuesWrapper.offset().top;
				if (hasScrolled && !WFAD.loadingBlocks && currentScrollBottom >= scrollThreshold) {
					hasScrolled = false;
					var offset = $('.wf-block-record').length;
					WFAD.loadingBlocks = true;
					WFAD.ajax('wordfence_getBlocks', {offset: offset}, function(res) {
						$(window).trigger('wordfenceRefreshBlockList', [res, true]);
						WFAD.loadingBlocks = false;
					});
				}
				else if (currentScrollBottom < scrollThreshold) {
					hasScrolled = true;
				}
			});
			
			$('#blocks-bulk-unblock').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var totalCount = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox:visible').length;
				var checked = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox.wf-checked:visible');
				var checkedCount = checked.length;
				var blockIDs = [];
				var rows = [];
				for (var i = 0; i < checked.length; i++) {
					var tr = $(checked[i]).closest('tr');
					rows.push(tr);
					blockIDs.push(tr.data('id'));
				}

				var prompt = $('#wfTmpl_unblockPrompt').tmpl({count: checkedCount});
				var promptHTML = $("<div />").append(prompt).html();
				WFAD.colorboxHTML('400px', promptHTML, {overlayClose: false, closeButton: false, className: 'wf-modal', onComplete: function() {
					$('#wf-blocking-prompt-cancel').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();
						
						WFAD.colorboxClose();
					});

					$('#wf-blocking-prompt-unblock').on('click', function(e) {
						e.preventDefault();
						e.stopPropagation();

						WFAD.loadingBlocks = true;
						WFAD.ajax('wordfence_deleteBlocks', {blocks: JSON.stringify(blockIDs)}, function(res) {
							WFAD.loadingBlocks = false;
							if (totalCount == checkedCount) {
								$(window).trigger('wordfenceRefreshBlockList', [res, false]); //Everything deleted, just reload it
							}
							else {
								for (var i = 0; i < rows.length; i++) {
									$(rows[i]).remove();
								}
								$(window).trigger('wordfenceUpdateBulkSelect');
								$(window).trigger('wordfenceUpdateBlockButtons');
							}

							WFAD.colorboxClose();
						});
					});
				}});
			});
			
			$('#blocks-bulk-make-permanent').on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();

				var checked = $('.wf-blocks-table-bulk-checkbox.wf-option-checkbox.wf-checked:visible');
				var updateIDs = [];
				for (var i = 0; i < checked.length; i++) {
					var tr = $(checked[i]).closest('tr');
					if (tr.is(':visible')) {
						updateIDs.push(tr.data('id'));
					}
				}

				WFAD.loadingBlocks = true;
				WFAD.ajax('wordfence_makePermanentBlocks', {updates: JSON.stringify(updateIDs)}, function(res) {
					WFAD.loadingBlocks = false;
					$(window).trigger('wordfenceRefreshBlockList', [res, false]);
				});
			});

			$('.wf-option.wf-option-toggled-boolean-switch[data-option="displayAutomaticBlocks"]').on('change', function() {
				delete WFAD.pendingChanges['displayAutomaticBlocks'];
				var isOn = $(this).find('.wf-boolean-switch').hasClass('wf-active');
				WFAD.setOption($(this).data('option'), (isOn ? $(this).data('enabledValue') : $(this).data('disabledValue')), function() {
					WFAD.ajax('wordfence_getBlocks', {offset: 0}, function(res) {
						$(window).trigger('wordfenceRefreshBlockList', [res, false]);
					});
				});
			});
		});
	})(jQuery);
</script>
<script type="text/x-jquery-template" id="wfTmpl_unblockPrompt">
<?php
echo wfView::create('common/modal-prompt', array(
	'title' => __('Unblocking', 'wordfence'),
	'message' => '{{if count == 1}}' . __('Are you sure you want to stop blocking the selected IP, range, or country?') . ' {{else}}' . __('Are you sure you want to stop blocking the ${count} selected IPs, ranges, and countries?') . '{{/if}}',
	'primaryButton' => array('id' => 'wf-blocking-prompt-cancel', 'label' => __('Cancel', 'wordfence'), 'link' => '#'),
	'secondaryButtons' => array(array('id' => 'wf-blocking-prompt-unblock', 'label' => __('Unblock', 'wordfence'), 'link' => '#')),
))->render();
?>
</script>
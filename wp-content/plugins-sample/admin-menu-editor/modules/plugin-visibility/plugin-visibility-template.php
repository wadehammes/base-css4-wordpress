<?php do_action('admin_menu_editor-display_header'); ?>

<div id="ame-plugin-visibility-editor">
		<form method="post" data-bind="submit: saveChanges" class="ame-pv-save-form" action="<?php
		echo esc_attr(add_query_arg(
			array(
				'page' => 'menu_editor',
				'noheader' => '1',
				'sub_section' => amePluginVisibility::TAB_SLUG,
			),
			admin_url('options-general.php')
		));
		?>">

			<?php submit_button('Save Changes', 'primary', 'submit', false); ?>

			<input type="hidden" name="action" value="save_plugin_visibility">
			<?php wp_nonce_field('save_plugin_visibility'); ?>

			<input type="hidden" name="settings" value="" data-bind="value: settingsData">
			<input type="hidden" name="selected_actor" value="" data-bind="value: selectedActor">
		</form>

		<?php require AME_ROOT_DIR . '/modules/actor-selector/actor-selector-template.php'; ?>

		<table class="widefat plugins">
			<thead>
				<tr>
					<th scope="col" class="ame-check-column">
						<!--suppress HtmlFormInputWithoutLabel -->
						<input type="checkbox" data-bind="checked: areAllPluginsChecked">
					</th>
					<th scope="col">Plugin</th>
					<th scope="col">Description</th>
				</tr>
			</thead>

			<tbody data-bind="foreach: plugins">
			<tr
				data-bind="
				css: {
					'active': isActive,
					'inactive': !isActive
				}
			">

				<!--
				Alas, we can't use the "check-column" class for this checkbox because WP would apply
				the default "check all boxes" behaviour and override our Knockout bindings.
				-->
				<th scope="row" class="ame-check-column">
					<!--suppress HtmlFormInputWithoutLabel -->
					<input
						type="checkbox"
						data-bind="
						checked: isChecked,
						attr: {
							id: 'ame-plugin-visible-' + $index(),
							'data-plugin-file': fileName
						}">
				</th>

				<td class="plugin-title">
					<label data-bind="attr: { 'for': 'ame-plugin-visible-' + $index() }">
						<strong data-bind="text: name"></strong>
					</label>
				</td>

				<td><p data-bind="text: description"></p></td>
			</tr>
			</tbody>

			<tfoot>
				<tr>
					<th scope="col" class="ame-check-column">
						<!--suppress HtmlFormInputWithoutLabel -->
						<input type="checkbox" data-bind="checked: areAllPluginsChecked">
					</th>
					<th scope="col">Plugin</th>
					<th scope="col">Description</th>
				</tr>
			</tfoot>

		</table>

	</div> <!-- /module container -->

<?php do_action('admin_menu_editor-display_footer'); ?>
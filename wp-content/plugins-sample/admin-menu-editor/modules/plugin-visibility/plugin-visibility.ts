/// <reference path="../../js/knockout.d.ts" />
/// <reference path="../../js/jquery.d.ts" />
/// <reference path="../../js/jqueryui.d.ts" />
/// <reference path="../../js/lodash-3.10.d.ts" />
/// <reference path="../../modules/actor-selector/actor-selector.ts" />

declare var amePluginVisibility: AmePluginVisibilityModule;
declare var wsPluginVisibilityData: PluginVisibilityScriptData;

interface PluginVisibilityScriptData {
	isMultisite: boolean,
	canManagePlugins: {[roleId : string] : boolean},
	selectedActor: string,
	installedPlugins: Array<PvPluginInfo>,
	settings: PluginVisibilitySettings,
	isProVersion: boolean,

	adminAjaxUrl: string,
	dismissNoticeNonce: string
}

interface PluginVisibilitySettings {
	grantAccessByDefault: GrantAccessMap,
	plugins: {
		[fileName : string] : {
			isVisibleByDefault: boolean,
			grantAccess: GrantAccessMap
		}
	}
}

interface GrantAccessMap {
	[actorId : string] : boolean
}

interface PvPluginInfo {
	name: string,
	fileName: string,
	description: string,
	isActive: boolean
}

class AmePluginVisibilityModule {
	static _ = wsAmeLodash;

	plugins: Array<AmePlugin>;
	private canRoleManagePlugins: {[roleId: string] : boolean};
	grantAccessByDefault: {[actorId: string] : KnockoutObservable<boolean>};
	private isMultisite: boolean;

	actorSelector: AmeActorSelector;
	selectedActor: KnockoutComputed<string>;
	settingsData: KnockoutObservable<string>;

	areAllPluginsChecked: KnockoutComputed<boolean>;

	/**
	 * Actors that don't lose access to a plugin when you uncheck it in the "All" view.
	 * This is a convenience feature that lets the user quickly hide a bunch of plugins from everyone else.
	 */
	private privilegedActors: Array<AmeBaseActor>;

	constructor(scriptData: PluginVisibilityScriptData) {
		const _ = AmePluginVisibilityModule._;

		this.actorSelector = new AmeActorSelector(AmeActors, scriptData.isProVersion);

		//Wrap the selected actor in a computed observable so that it can be used with Knockout.
		var _selectedActor = ko.observable(this.actorSelector.selectedActor);
		this.selectedActor = ko.computed<string>({
			read: function () {
				return _selectedActor();
			},
			write: (newActor: string) => {
				this.actorSelector.setSelectedActor(newActor);
			}
		});
		this.actorSelector.onChange((newSelectedActor: string) => {
			_selectedActor(newSelectedActor);
		});

		//Re-select the previously selected actor, or select "All" (null) by default.
		this.selectedActor(scriptData.selectedActor);

		this.canRoleManagePlugins = scriptData.canManagePlugins;
		this.isMultisite = scriptData.isMultisite;

		this.grantAccessByDefault = {};
		_.forEach(this.actorSelector.getVisibleActors(), (actor: AmeBaseActor) => {
			this.grantAccessByDefault[actor.id] = ko.observable<boolean>(
				_.get(scriptData.settings.grantAccessByDefault, actor.id, this.canManagePlugins(actor))
			);
		});

		this.plugins = _.map(scriptData.installedPlugins, (plugin) => {
			return new AmePlugin(plugin, _.get(scriptData.settings.plugins, plugin.fileName, {}), this);
		});

		this.privilegedActors = [this.actorSelector.getCurrentUserActor()];
		if (this.isMultisite) {
			this.privilegedActors.push(AmeActors.getSuperAdmin());
		}

		this.areAllPluginsChecked = ko.computed({
			read: () => {
				return _.every(this.plugins, (plugin) => {
					return this.isPluginVisible(plugin);
				});
			},
			write: (isChecked) => {
				if (this.selectedActor() !== null) {
					let canSeePluginsByDefault = this.getGrantAccessByDefault(this.selectedActor());
					canSeePluginsByDefault(isChecked);
				}
				_.forEach(this.plugins, (plugin) => {
					this.setPluginVisibility(plugin, isChecked);
				});
			}
 		});

		//This observable will be populated when saving changes.
		this.settingsData = ko.observable('');
	}

	isPluginVisible(plugin: AmePlugin): boolean {
		let actorId = this.selectedActor();
		if (actorId === null) {
			return plugin.isVisibleByDefault();
		} else {
			let canSeePluginsByDefault = this.getGrantAccessByDefault(actorId),
				isVisible = plugin.getGrantObservable(actorId, plugin.isVisibleByDefault() && canSeePluginsByDefault());
			return isVisible();
		}
	}

	setPluginVisibility(plugin: AmePlugin, isVisible: boolean) {
		const selectedActor = this.selectedActor();
		if (selectedActor === null) {
			plugin.isVisibleByDefault(isVisible);

			//Show/hide from everyone except the current user and Super Admin.
			//However, don't enable plugins for roles that can't access the "Plugins" page in the first place.
			const _ = AmePluginVisibilityModule._;
			_.forEach(this.actorSelector.getVisibleActors(), (actor: AmeBaseActor) => {
				let allowAccess = plugin.getGrantObservable(actor.id, isVisible);
				if (!this.canManagePlugins(actor)) {
					allowAccess(false);
				} else if (_.includes(this.privilegedActors, actor)) {
					allowAccess(true);
				} else {
					allowAccess(isVisible);
				}
			});
		} else {
			//Show/hide from the selected role or user.
			let allowAccess = plugin.getGrantObservable(selectedActor, isVisible);
			allowAccess(isVisible);
		}
	}

	private canManagePlugins(actor: AmeBaseActor) {
		const _ = AmePluginVisibilityModule._;
		if ((actor instanceof AmeRole) && _.has(this.canRoleManagePlugins, actor.name)) {
			return this.canRoleManagePlugins[actor.name];
		}
		if (actor instanceof AmeSuperAdmin) {
			return true;
		}

		if (actor instanceof AmeUser) {
			//Can any of the user's roles manage plugins?
			let result = false;
			_.forEach(actor.roles, (roleId) => {
				if (_.get(this.canRoleManagePlugins, roleId, false)) {
					result = true;
					return false;
				}
			});
			return (result || AmeActors.hasCap(actor.id, 'activate_plugins'));
		}

		return false;
	}

	private getGrantAccessByDefault(actorId: string): KnockoutObservable<boolean> {
		if (!this.grantAccessByDefault.hasOwnProperty(actorId)) {
			this.grantAccessByDefault[actorId] = ko.observable(this.canManagePlugins(AmeActors.getActor(actorId)));
		}
		return this.grantAccessByDefault[actorId];
	}

	private getSettings(): PluginVisibilitySettings {
		const _ = AmePluginVisibilityModule._;
		let result: PluginVisibilitySettings = <PluginVisibilitySettings>{};

		result.grantAccessByDefault = _.mapValues(this.grantAccessByDefault, (allow): boolean => {
			return allow();
		});
		result.plugins = {};
		_.forEach(this.plugins, (plugin: AmePlugin) => {
			result.plugins[plugin.fileName] = {
				isVisibleByDefault: plugin.isVisibleByDefault(),
				grantAccess: _.mapValues(plugin.grantAccess, (allow): boolean => {
					return allow();
				})
			};
		});

		return result;
	}

	saveChanges() {
		const settings = this.getSettings();

		//Remove settings associated with roles and users that no longer exist or are not visible.
		const _ = AmePluginVisibilityModule._,
			visibleActorIds = _.pluck(this.actorSelector.getVisibleActors(), 'id');
		_.forEach(settings.plugins, (plugin) => {
			plugin.grantAccess = _.pick<GrantAccessMap, GrantAccessMap>(plugin.grantAccess, visibleActorIds);
		});

		//Populate form field(s).
		this.settingsData(jQuery.toJSON(settings));

		return true;
	}
}

class AmePlugin implements PvPluginInfo {
	name: string;
	fileName: string;
	description: string;
	isActive: boolean;

	isChecked: KnockoutComputed<boolean>;

	isVisibleByDefault: KnockoutObservable<boolean>;
	grantAccess: {[actorId : string] : KnockoutObservable<boolean>};

	constructor(details: PvPluginInfo, visibility: Object, module: AmePluginVisibilityModule) {
		this.name = AmePlugin.stripAllTags(details.name);
		this.description = AmePlugin.stripAllTags(details.description);
		this.fileName = details.fileName;
		this.isActive = details.isActive;

		const _ = AmePluginVisibilityModule._;
		this.isVisibleByDefault = ko.observable(_.get(visibility, 'isVisibleByDefault', true));

		const emptyGrant: {[actorId : string] : boolean} = {};
		this.grantAccess = _.mapValues(_.get(visibility, 'grantAccess', emptyGrant), (hasAccess) => {
			return ko.observable<boolean>(hasAccess);
		});

		this.isChecked = ko.computed<boolean>({
			read: () => {
				return module.isPluginVisible(this);
			},
			write: (isVisible: boolean) => {
				return module.setPluginVisibility(this, isVisible);
			}
		});
	}

	getGrantObservable(actorId: string, defaultValue: boolean = true): KnockoutObservable<boolean> {
		if (!this.grantAccess.hasOwnProperty(actorId)) {
			this.grantAccess[actorId] = ko.observable<boolean>(defaultValue);
		}
		return this.grantAccess[actorId];
	}

	static stripAllTags(input): string {
		//Based on: http://phpjs.org/functions/strip_tags/
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
			commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(commentsAndPhpTags, '').replace(tags, '');
	}
}

jQuery(function ($) {
	amePluginVisibility = new AmePluginVisibilityModule(wsPluginVisibilityData);
	ko.applyBindings(amePluginVisibility, document.getElementById('ame-plugin-visibility-editor'));

	//Permanently dismiss the usage hint via AJAX.
	$('#ame-pv-usage-notice').on('click', '.notice-dismiss', function() {
		$.post(
			wsPluginVisibilityData.adminAjaxUrl,
			{
				'action' : 'ws_ame_dismiss_pv_usage_notice',
				'_ajax_nonce' : wsPluginVisibilityData.dismissNoticeNonce
			}
		);
	});
});
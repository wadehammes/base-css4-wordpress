//(c) W-Shadow

/*global wsEditorData, defaultMenu, customMenu, _:false */

/**
 * @property wsEditorData
 * @property {boolean} wsEditorData.wsMenuEditorPro
 *
 * @property {object} wsEditorData.blankMenuItem
 * @property {object} wsEditorData.itemTemplates
 * @property {object} wsEditorData.customItemTemplate
 *
 * @property {string} wsEditorData.adminAjaxUrl
 * @property {string} wsEditorData.imagesUrl
 *
 * @property {string} wsEditorData.menuFormatName
 * @property {string} wsEditorData.menuFormatVersion
 *
 * @property {boolean} wsEditorData.hideAdvancedSettings
 * @property {boolean} wsEditorData.showExtraIcons
 * @property {boolean} wsEditorData.dashiconsAvailable
 * @property {string}  wsEditorData.submenuIconsEnabled
 * @property {Object}  wsEditorData.showHints
 *
 * @property {string} wsEditorData.hideAdvancedSettingsNonce
 * @property {string} wsEditorData.getPagesNonce
 * @property {string} wsEditorData.getPageDetailsNonce
 * @property {string} wsEditorData.disableDashboardConfirmationNonce
 *
 * @property {string} wsEditorData.captionShowAdvanced
 * @property {string} wsEditorData.captionHideAdvanced
 *
 * @property {string} wsEditorData.unclickableTemplateId
 * @property {string} wsEditorData.unclickableTemplateClass
 * @property {string} wsEditorData.embeddedPageTemplateId
 *
 * @property {string} wsEditorData.currentUserLogin
 * @property {string|null} wsEditorData.selectedActor
 *
 * @property {object} wsEditorData.actors
 * @property {object} wsEditorData.roles
 * @property {object} wsEditorData.users
 * @property {string[]} wsEditorData.visibleUsers
 *
 * @property {object} wsEditorData.postTypes
 * @property {object} wsEditorData.taxonomies
 *
 * @property {boolean} wsEditorData.isDemoMode
 * @property {boolean} wsEditorData.isMasterMode
 */

wsEditorData.wsMenuEditorPro = !!wsEditorData.wsMenuEditorPro; //Cast to boolean.
var wsIdCounter = 0;

//A bit of black magic/hack to convince my IDE that wsAmeLodash is an alias for lodash.
window.wsAmeLodash = (function() {
	'use strict';
	if (typeof wsAmeLodash !== 'undefined') {
		return wsAmeLodash;
	}
	return _.noConflict();
})();

//These two properties must be objects, not arrays.
jQuery.each(['grant_access', 'hidden_from_actor'], function(unused, key) {
	'use strict';
	if (wsEditorData.blankMenuItem.hasOwnProperty(key) && !jQuery.isPlainObject(wsEditorData.blankMenuItem[key])) {
		wsEditorData.blankMenuItem[key] = {};
	}
});

var AmeCapabilityManager = (function(roles, users, _) {
	'use strict';

	/**
	 * A user.
	 *
	 * @typedef {Object} AmeUserActor
	 *
	 * @property {string} user_login
	 * @property {string} display_name
	 *
	 * @property {Object}   capabilities    A dictionary of ["capability" => boolean].
	 * @property {string[]} roles
	 * @property {boolean}  is_super_admin
	 */

	var me = {};
	/**
	 * @type {Object.<String, AmeUserActor>}
	 */
	users = users || {};

	var defaultCapabilities = {},
		grantedCapabilities = {},

		emptyObject = {},
		cachedContextList = [emptyObject, grantedCapabilities, defaultCapabilities];

	me.setRoles = function(newRoles) {
		roles = newRoles;
		_.forEach(roles, function(role, name) {
			defaultCapabilities['role:' + name] = role.capabilities;
		});
	};

	me.addUsers = function(newUsers) {
		_.forEach(newUsers, function(user) {
			users[user.user_login] = user;
			defaultCapabilities['user:' + user.user_login] = user.capabilities;
		});
	};

	me.getUsers = function() {
		return users;
	};

	me.setRoles(roles);
	me.addUsers(users);

	function parseActorString(actor) {
		var separator = actor.indexOf(':');
		if (separator === -1) {
			throw {
				name: 'InvalidActorException',
				message: "Actor string does not contain a colon.",
				value: actor
			};
		}

		return {
			'type': actor.substring(0, separator),
			'id': actor.substring(separator + 1)
		};
	}

	function actorHasCap(actor, capability, contextList) {
		//Check for explicit settings first.
		var result = null, actorValue, len = contextList.length;
		for (var i = 0; i < len; i++) {
			if (contextList[i].hasOwnProperty(actor)) {
				actorValue = contextList[i][actor];
				if (typeof actorValue === 'boolean') {
					return actorValue;
				} else if (actorValue.hasOwnProperty(capability)) {
					result = actorValue[capability];
					return (typeof result === 'boolean') ? result : result[0];
				}
			}
		}

		//Super admins have access to everything by default, unless specifically denied.
		if (actor === 'special:super_admin') {
			return (capability !== 'do_not_allow');
		}

		//Roles only have the capabilities that they actually have.
		if (actor.lastIndexOf('role:', 0) === 0) {
			return false;
		}

		//Users can have a capability through their roles or the "super admin" flag.
		if (actor.lastIndexOf('user:', 0) === 0) {
			var user = users[actor.substr('user:'.length)];
			if (user.is_super_admin) {
				return actorHasCap('special:super_admin', capability, contextList);
			}

			//Check if any of the user's roles have the capability.
			result = false;
			for(var index = 0; index < user.roles.length; index++) {
				result = result || actorHasCap('role:' + user.roles[index], capability, contextList);
			}
			return result;

		} else {
			throw {
				name: 'InvalidActorTypeException',
				message: "The specified actor type is not supported",
				value: actor
			};
		}
	}

    me.hasCap = function(actor, capability, context) {
		cachedContextList[0] = context || emptyObject;
		return actorHasCap(actor, capability, cachedContextList);
    };

	me.hasCapByDefault = function(actor, capability) {
		return actorHasCap(actor, capability, [defaultCapabilities]);
	};

	/**
	 *
	 * @param {string} login
	 * @param {boolean} skipLoginActor
	 * @returns {Array} Caution: Do not modify the returned array. Returns a reference to an internal array.
	 */
	me.getUserActors = function(login, skipLoginActor) {
		if (!users.hasOwnProperty(login)) {
			throw {
				name: 'UnknownUserException',
				message: 'Can not get actors of an unknown user',
				value: login
			};
		}

		//Check the cache first.
		var user = users[login];
		if (skipLoginActor && user.hasOwnProperty('actorsWithoutSelf')) {
			return user.actorsWithoutSelf;
		}
		if (!skipLoginActor && user.hasOwnProperty('actors')) {
			return user.actors;
		}

		//Generate the list and cache it.
		var actors = [], actorsWithoutSelf = [];
		actors.push('user:' + login);
		if (user.is_super_admin) {
			actorsWithoutSelf.push('special:super_admin');
		}
		for (var i = 0; i < user.roles.length; i++) {
			actorsWithoutSelf.push('role:' + user.roles[i]);
		}
		actors = actors.concat(actorsWithoutSelf);

		user.actors = actors;
		user.actorsWithoutSelf = actorsWithoutSelf;

		return skipLoginActor ? actorsWithoutSelf : actors;
	};

	me.getUser = function(login) {
		if (!users.hasOwnProperty(login)) {
			throw {
				name: 'UnknownUserException',
				message: 'User not found',
				value: login
			};
		}
		return users[login];
	};

	me.roleExists = function(roleId) {
		return (typeof roleId === 'string') && roles.hasOwnProperty(roleId);
	};

	/**
	 * Compare the specificity of two actors.
	 *
	 * Returns 1 if the first actor is more specific than the second, 0 if they're both
	 * equally specific, and -1 if the second actor is more specific.
	 *
	 * @param {String} actor1
	 * @param {String} actor2
	 * @return {Number}
	 */
    me.compareActorSpecificity = function(actor1, actor2) {
		var delta = me.getActorSpecificity(actor1) - me.getActorSpecificity(actor2);
		if (delta !== 0) {
			delta = (delta > 0) ? 1 : -1;
		}
		return delta;
    };

    me.getActorSpecificity = function(actorString) {
        var actor = parseActorString(actorString);
		var specificity = 0;
        switch(actor.type) {
            case 'role':
                specificity = 1;
				break;
			case 'special':
				specificity = 2;
				break;
			case 'user':
				specificity = 10;
				break;
			default:
				specificity = 0;
        }
		return specificity;
    };

	me.setCap = function(actor, capability, hasCap, sourceType, sourceName) {
		me.setCapInContext(grantedCapabilities, actor, capability, hasCap, sourceType, sourceName);
	};

	/**
	 * Grant or deny a capability to an actor.
	 *
	 * @param {Object} context
	 * @param {string} actor
	 * @param {string} capability
	 * @param {boolean} hasCap
	 * @param {string} [sourceType]
	 * @param {string} [sourceName]
	 */
	me.setCapInContext = function(context, actor, capability, hasCap, sourceType, sourceName) {
		var grant = sourceType ? [hasCap, sourceType, sourceName || null] : hasCap;
		_.set(context, [actor, capability], grant);
	};

	me.resetCap = function(actor, capability) {
		me.resetCapInContext(grantedCapabilities, actor, capability);
	};

	me.resetCapInContext = function(context, actor, capability) {
		if (_.has(context, [actor, capability])) {
			delete context[actor][capability];
		}
	};

	me.setGrantedCapabilities = function(newGrants) {
		grantedCapabilities = _.cloneDeep(newGrants);
		cachedContextList[1] = grantedCapabilities;
	};

	me.getGrantedCapabilities = function() {
		return grantedCapabilities;
	};

	/**
	 * Remove redundant granted capabilities.
	 *
	 * For example, if user "jane" has been granted the "edit_posts" capability both directly and via the Editor role,
	 * the direct grant is redundant. We can remove it. Jane will still have "edit_posts" because she's an editor.
	 */
	me.pruneGrantedCapabilities = function(actorType) {
		actorType = actorType || null;
		var pruned = _.cloneDeep(grantedCapabilities),
			context = [pruned, defaultCapabilities];

		var actorKeys = _(pruned).keys().filter(function(actor) {
			var parsed = parseActorString(actor);
			//Skip users that are not loaded.
			if (parsed.type === 'user' && !users.hasOwnProperty(actor.id)) {
				return false;
			}
			return !(actorType && parsed.type !== actorType);
		}).value();

		_.forEach(actorKeys, function(actor) {
			_.forEach(_.keys(pruned[actor]), function(capability) {
				var grant = pruned[actor][capability];
				delete pruned[actor][capability];

				var hasCap = _.isArray(grant) ? grant[0] : grant,
					hasCapWhenPruned = actorHasCap(actor, capability, context);

				if (hasCap !== hasCapWhenPruned) {
					pruned[actor][capability] = grant; //Restore.
				}
			});
		});

		me.setGrantedCapabilities(pruned);
		return pruned;
	};

	return me;
})(wsEditorData.roles, wsEditorData.users, wsAmeLodash);

/**
 * A utility for retrieving post and page titles.
 */
var AmePageTitles = (function($) {
	'use strict';

	var me = {}, cache = {};

	function getCacheKey(pageId, blogId) {
		return blogId + '_' + pageId;
	}

	/**
	 * Add a page title to the cache.
	 *
	 * @param {Number} pageId Post or page ID.
	 * @param {Number} blogId Blog ID.
	 * @param {String} title The title of the post or page.
	 */
	me.add = function(pageId, blogId, title) {
		cache[getCacheKey(pageId, blogId)] = title;
	};

	/**
	 * Get page title.
	 *
	 * Note: This method does not return the title. Instead, it calls the provided callback with the title
	 * as the first argument. The callback will be executed asynchronously if the title hasn't been cached yet.
	 *
	 * @param {Number} pageId
	 * @param {Number} blogId
	 * @param {Function} callback
	 */
	me.get = function(pageId, blogId, callback) {
		var key = getCacheKey(pageId, blogId);
		if (typeof cache[key] !== 'undefined') {
			callback(cache[key], pageId, blogId);
			return;
		}

		$.getJSON(
			wsEditorData.adminAjaxUrl,
			{
				'action' : 'ws_ame_get_page_details',
				'_ajax_nonce' : wsEditorData.getPageDetailsNonce,
				'post_id' : pageId,
				'blog_id' : blogId
			},
			function(details) {
				var title;
				if (typeof details.error !== 'undefined'){
					title = details.error;
				} else if ((typeof details !== 'object') || (typeof details.post_title === 'undefined')) {
					title = '< Server error >';
				} else {
					title = details.post_title;
				}
				cache[key] = title;

				callback(cache[key], pageId, blogId);
			}
		);
	};

	return me;
})(jQuery);

var AmeEditorApi = {};
window.AmeEditorApi = AmeEditorApi;


(function ($, _){
'use strict';

var selectedActor = null;

var itemTemplates = {
	templates: wsEditorData.itemTemplates,

	getTemplateById: function(templateId) {
		if (wsEditorData.itemTemplates.hasOwnProperty(templateId)) {
			return wsEditorData.itemTemplates[templateId];
		} else if ((templateId === '') || (templateId === 'custom')) {
			return wsEditorData.customItemTemplate;
		}
		return null;
	},

	getDefaults: function (templateId) {
		var template = this.getTemplateById(templateId);
		if (template) {
			return template.defaults;
		} else {
			return null;
		}
	},

	getDefaultValue: function (templateId, fieldName) {
		if (fieldName === 'template_id') {
			return null;
		}

		var defaults = this.getDefaults(templateId);
		if (defaults && (typeof defaults[fieldName] !== 'undefined')) {
			return defaults[fieldName];
		}
		return null;
	},

	hasDefaultValue: function(templateId, fieldName) {
		return (this.getDefaultValue(templateId, fieldName) !== null);
	}
};

/**
 * Set an input field to a value. The only difference from jQuery.val() is that
 * setting a checkbox to true/false will check/clear it.
 *
 * @param input
 * @param value
 */
function setInputValue(input, value) {
	if (input.attr('type') === 'checkbox'){
		input.prop('checked', value);
    } else {
        input.val(value);
    }
}

/**
 * Get the value of an input field. The only difference from jQuery.val() is that
 * checked/unchecked checkboxes will return true/false.
 *
 * @param input
 * @return {*}
 */
function getInputValue(input) {
	if (input.attr('type') === 'checkbox'){
		return input.is(':checked');
	}
	return input.val();
}


/*
 * Utility function for generating pseudo-random alphanumeric menu IDs.
 * Rationale: Simpler than atomically auto-incrementing or globally unique IDs.
 */
function randomMenuId(prefix, size){
	prefix = (typeof prefix === 'undefined') ? 'custom_item_' : prefix;
	size = (typeof size === 'undefined') ? 5 : size;

    var suffix = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < size; i++ ) {
        suffix += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return prefix + suffix;
}

function outputWpMenu(menu){
	var menuCopy = $.extend(true, {}, menu);
	var menuBox = $('#ws_menu_box');

	//Remove the current menu data
	menuBox.empty();
	$('#ws_submenu_box').empty();

	//Display the new menu
	var i = 0;
	for (var filename in menuCopy){
		if (!menuCopy.hasOwnProperty(filename)){
			continue;
		}
		outputTopMenu(menuCopy[filename]);
		i++;
	}

	//Automatically select the first top-level menu
	menuBox.find('.ws_menu:first').click();
}

/**
 * Load a menu configuration in the editor.
 * Note: All previous settings will be discarded without warning. Unsaved changes will be lost.
 *
 * @param {Object} adminMenu The menu structure to load.
 */
function loadMenuConfiguration(adminMenu) {
	//There are some menu properties that need to be objects, but PHP JSON-encodes empty associative
	//arrays as numeric arrays. We want them to be empty objects instead.
	if (adminMenu.hasOwnProperty('color_presets') && !$.isPlainObject(adminMenu.color_presets)) {
		adminMenu.colorPresets = {};
	}

	var objectProperties = ['grant_access', 'hidden_from_actor'];
	//noinspection JSUnusedLocalSymbols
	function fixEmptyObjects(unused, menuItem) {
		for (var i = 0; i < objectProperties.length; i++) {
			var key = objectProperties[i];
			if (menuItem.hasOwnProperty(key) && !$.isPlainObject(menuItem[key])) {
				menuItem[key] = {};
			}
		}
		if (menuItem.hasOwnProperty('items')) {
			$.each(menuItem.items, fixEmptyObjects);
		}
	}
	$.each(adminMenu.tree, fixEmptyObjects);

	//Load color presets from the new configuration.
	if (typeof adminMenu.color_presets === 'object') {
		colorPresets = $.extend(true, {}, adminMenu.color_presets);
	} else {
		colorPresets = {};
	}
	wasPresetDropdownPopulated = false;

	//Load capabilities.
	AmeCapabilityManager.setGrantedCapabilities(_.get(adminMenu, 'granted_capabilities', {}));

	//Display the new admin menu.
	outputWpMenu(adminMenu.tree);
}

/*
 * Create edit widgets for a top-level menu and its submenus and append them all to the DOM.
 *
 * Inputs :
 *	menu - an object containing menu data
 *	afterNode - if specified, the new menu widget will be inserted after this node. Otherwise,
 *	            it will be added to the end of the list.
 * Outputs :
 *	Object with two fields - 'menu' and 'submenu' - containing the DOM nodes of the created widgets.
 */
function outputTopMenu(menu, afterNode){
	//Create the menu widget
	var menu_obj = buildMenuItem(menu, true);

	if ( (typeof afterNode !== 'undefined') && (afterNode !== null) ){
		$(afterNode).after(menu_obj);
	} else {
		menu_obj.appendTo('#ws_menu_box');
	}

	//Create a container for menu items, even if there are none
	var submenu = buildSubmenu(menu.items, menu_obj.attr('id'));
	submenu.appendTo('#ws_submenu_box');
	menu_obj.data('submenu_id', submenu.attr('id'));

	//Note: Update the menu only after its children are ready. It needs the submenu items to decide whether to display
	//the access checkbox as checked or indeterminate.
	updateItemEditor(menu_obj);

	return {
		'menu' : menu_obj,
		'submenu' : submenu
	};
}

/*
 * Create and populate a submenu container.
 */
function buildSubmenu(items, parentMenuId){
	//Create a container for menu items, even if there are none
	var submenu = $('<div class="ws_submenu" style="display:none;"></div>');
	submenu.attr('id', 'ws-submenu-'+(wsIdCounter++));

	if (parentMenuId) {
		submenu.data('parent_menu_id', parentMenuId);
	}

	//Only show menus that have items.
	//Skip arrays (with a length) because filled menus are encoded as custom objects.
	var entry = null;
	if (items) {
		$.each(items, function(index, item) {
			entry = buildMenuItem(item, false);
			if ( entry ){
				submenu.append(entry);
				updateItemEditor(entry);
			}
		});
	}

	//Make the submenu sortable
	makeBoxSortable(submenu);

	return submenu;
}

/**
 * Create an edit widget for a menu item.
 *
 * @param {Object} itemData
 * @param {Boolean} [isTopLevel] Specify if this is a top-level menu or a sub-menu item. Defaults to false (= sub-item).
 * @return {*} The created widget as a jQuery object.
 */
function buildMenuItem(itemData, isTopLevel) {
	isTopLevel = (typeof isTopLevel === 'undefined') ? false : isTopLevel;

	//Create the menu HTML
	var item = $('<div></div>')
		.attr('class', "ws_container")
		.attr('id', 'ws-menu-item-' + (wsIdCounter++))
		.data('menu_item', itemData)
		.data('field_editors_created', false);

	item.addClass(isTopLevel ? 'ws_menu' : 'ws_item');
	if ( itemData.separator ) {
		item.addClass('ws_menu_separator');
	}

	//Add a header and a container for property editors (to improve performance
	//the editors themselves are created later, when the user tries to access them
	//for the first time).
	var contents = [];
	var menuTitle = ((itemData.menu_title !== null) ? itemData.menu_title : itemData.defaults.menu_title);
	if (menuTitle === '') {
		menuTitle = '&nbsp;';
	}

	contents.push(
		'<div class="ws_item_head">',
			itemData.separator ? '' : '<a class="ws_edit_link"> </a><div class="ws_flag_container"> </div>',
			'<input type="checkbox" class="ws_actor_access_checkbox">',
			'<span class="ws_item_title">',
				stripAllTags(menuTitle),
			'&nbsp;</span>',

		'</div>',
		'<div class="ws_editbox" style="display: none;"></div>'
	);
	item.append(contents.join(''));

	//Apply flags based on the item's state
	var flags = ['hidden', 'unused', 'custom'];
	for (var i = 0; i < flags.length; i++) {
		setMenuFlag(item, flags[i], getFieldValue(itemData, flags[i], false));
	}

	if ( isTopLevel && !itemData.separator ){
		//Allow the user to drag menu items to top-level menus
		item.droppable({
			'hoverClass' : 'ws_menu_drop_hover',

			'accept' : (function(thing){
				return thing.hasClass('ws_item');
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				var new_item = buildMenuItem(droppedItemData, false);

				var sourceSubmenu = ui.draggable.parent();
				var submenu = $('#' + item.data('submenu_id'));
				submenu.append(new_item);

				if ( !event.ctrlKey ) {
					ui.draggable.remove();
				}

				updateItemEditor(new_item);

				//Moving an item can change aggregate menu permissions. Update the UI accordingly.
				updateParentAccessUi(submenu);
				updateParentAccessUi(sourceSubmenu);
			})
		});
	}

	return item;
}

function jsTrim(str){
	return str.replace(/^\s+|\s+$/g, "");
}

//Expose this handy tool to our other scripts.
AmeEditorApi.jsTrim = jsTrim;

function stripAllTags(input) {
	//Based on: http://phpjs.org/functions/strip_tags/
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
		commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '').replace(tags, '');
}

//Editor field spec template.
var baseField = {
	caption : '[No caption]',
    standardCaption : true,
	advanced : false,
	type : 'text',
	defaultValue: '',
	onlyForTopMenus: false,
	addDropdown : false,
	visible: true,

	write: null,
	display: null
};

/*
 * List of all menu fields that have an associated editor
 */
var knownMenuFields = {
	'menu_title' : $.extend({}, baseField, {
		caption : 'Menu title',
		display: function(menuItem, displayValue, input, containerNode) {
			//Update the header as well.
			containerNode.find('.ws_item_title').html(stripAllTags(displayValue) + '&nbsp;');
			return displayValue;
		},
		write: function(menuItem, value, input, containerNode) {
			menuItem.menu_title = value;
			containerNode.find('.ws_item_title').html(stripAllTags(input.val()) + '&nbsp;');
		}
	}),

	'template_id' : $.extend({}, baseField, {
		caption : 'Target page',
		type : 'select',
		options : (function(){
			//Generate name => id mappings for all item templates + the special "Custom" template.
			var itemTemplateIds = [];
			itemTemplateIds.push([wsEditorData.customItemTemplate.name, '']);

			for (var template_id in wsEditorData.itemTemplates) {
				if (wsEditorData.itemTemplates.hasOwnProperty(template_id)) {
					itemTemplateIds.push([wsEditorData.itemTemplates[template_id].name, template_id]);
				}
			}

			itemTemplateIds.sort(function(a, b) {
				if (a[1] === b[1]) {
					return 0;
				}

				//The "Custom" item is always first.
				if (a[1] === '') {
					return -1;
				} else if (b[1] === '') {
					return 1;
				}

				//Top-level items go before submenus.
				var aIsTop = (a[1].charAt(0) === '>') ? 1 : 0;
				var bIsTop = (b[1].charAt(0) === '>') ? 1 : 0;
				if (aIsTop !== bIsTop) {
					return bIsTop - aIsTop;
				}

				//Everything else is sorted by name, in alphabetical order.
				if (a[0] > b[0]) {
					return 1;
				} else if (a[0] < b[0]) {
					return -1;
				}
				return 0;
			});

			return itemTemplateIds;
		})(),

		write: function(menuItem, value, input, containerNode) {
			var oldTemplateId = menuItem.template_id;

			menuItem.template_id = value;
			menuItem.defaults = itemTemplates.getDefaults(menuItem.template_id);
		    menuItem.custom = (menuItem.template_id === '');

		    // The file/URL of non-custom items is read-only and equal to the default
		    // value. Rationale: simplifies menu generation, prevents some user mistakes.
		    if (menuItem.template_id !== '') {
			    menuItem.file = null;
		    }

		    // The new template might not have default values for some of the fields
		    // currently set to null (= "default"). In those cases, we need to make
		    // the current values explicit.
		    containerNode.find('.ws_edit_field').each(function(index, field){
			    field = $(field);
			    var fieldName = field.data('field_name');
			    var isSetToDefault = (menuItem[fieldName] === null);
			    var hasDefaultValue = itemTemplates.hasDefaultValue(menuItem.template_id, fieldName);

			    if (isSetToDefault && !hasDefaultValue) {
					var oldDefaultValue = itemTemplates.getDefaultValue(oldTemplateId, fieldName);
					if (oldDefaultValue !== null) {
						menuItem[fieldName] = oldDefaultValue;
					}
			    }
		    });
		}
	}),

	'embedded_page_id' : $.extend({}, baseField, {
		caption: 'Embedded page ID',
		defaultValue: 'Select page to display',
		type: 'text',
		visible: false, //Displayed on-demand.
		addDropdown: 'ws_embedded_page_selector',

		display: function(menuItem, displayValue, input) {
			//Only show this field if the "Embed WP page" template is selected.
			input.closest('.ws_edit_field').toggle(menuItem.template_id === wsEditorData.embeddedPageTemplateId);

			input.prop('readonly', true);
			var pageId = parseInt(getFieldValue(menuItem, 'embedded_page_id', 0), 10),
				blogId = parseInt(getFieldValue(menuItem, 'embedded_page_blog_id', 1), 10),
				formattedId = 'ID: ' + pageId;

			if (pageId <= 0) {
				return 'Select page =>';
			}

			if (blogId !== 1) {
				formattedId = formattedId + ', blog ID: ' + blogId;
			}
			displayValue = formattedId;

			AmePageTitles.get(pageId, blogId, function(title) {
				//If we retrieved the title via AJAX, the user might have selected a different page in the meantime.
				//Make sure it's still the same page before displaying the title.
				var currentPageId = parseInt(getFieldValue(menuItem, 'embedded_page_id', 0), 10),
					currentBlogId = parseInt(getFieldValue(menuItem, 'embedded_page_blog_id', 1), 10);
				if ((currentPageId !== pageId) || (currentBlogId !== blogId)) {
					return;
				}

				displayValue = title + ' (' + formattedId + ')';
				input.val(displayValue);
			});

			return displayValue;
		},

		write: function() {
			//The user cannot directly edit this field. We deliberately ignore writes.
		}
	}),

	'file' : $.extend({}, baseField, {
		caption: 'URL',
		display: function(menuItem, displayValue, input) {
			// The URL/file field is read-only for default menus. Also, since the "file"
			// field is usually set to a page slug or plugin filename for plugin/hook pages,
			// we display the dynamically generated "url" field here (i.e. the actual URL) instead.
			if (menuItem.template_id !== '') {
				input.attr('readonly', 'readonly');
				displayValue = itemTemplates.getDefaultValue(menuItem.template_id, 'url');
			} else {
				input.removeAttr('readonly');
			}
			return displayValue;
		},

		write: function(menuItem, value) {
			// A menu must always have a non-empty URL. If the user deletes the current value,
			// reset it to the old value.
			if (value === '') {
				value = menuItem.file;
			}
			// Default menus always point to the default file/URL.
			if (menuItem.template_id !== '') {
				value = null;
			}
			menuItem.file = value;
		}
	}),

	'access_level' : $.extend({}, baseField, {
		caption: 'Permissions',
		defaultValue: 'read',
		type: 'access_editor',
		visible: false, //Will be set to visible only in Pro version.

		display: function(menuItem) {
			//Permissions display is a little complicated and could use improvement.
			var requiredCap = getFieldValue(menuItem, 'access_level', '');
			var extraCap = getFieldValue(menuItem, 'extra_capability', '');

			var displayValue = (menuItem.template_id === '') ? '< Custom >' : requiredCap;
			if (extraCap !== '') {
				if (menuItem.template_id === '') {
					displayValue = extraCap;
				} else {
					displayValue = displayValue + '+' + extraCap;
				}
			}

			return displayValue;
		},

		write: function(menuItem) {
			//The required capability can't be directly edited and always equals the default.
			menuItem.access_level = null;
		}
	}),

	'extra_capability' : $.extend({}, baseField, {
		caption: 'Required capability',
		defaultValue: 'read',
		type: 'text',
		addDropdown: 'ws_cap_selector',

		display: function(menuItem) {
			//Permissions display is a little complicated and could use improvement.
			var requiredCap = getFieldValue(menuItem, 'access_level', '');
			var extraCap = getFieldValue(menuItem, 'extra_capability', '');

			var displayValue = extraCap;
			if ((extraCap === '') || (extraCap === null)) {
				displayValue = requiredCap;
			}

			return displayValue;
		},

		write: function(menuItem, value) {
			value = jsTrim(value);

			//Reset to default if the user clears the input.
			if (value === '') {
				menuItem.extra_capability = null;
				return;
			}

			//It would be redundant to set an extra_capability that it matches access_level.
			var requiredCap = getFieldValue(menuItem, 'access_level', '');
			var extraCap = getFieldValue(menuItem, 'extra_capability', '');
			if (extraCap === '' && value === requiredCap) {
				return;
			}

			menuItem.extra_capability = value;
		}
	}),

	'page_title' : $.extend({}, baseField, {
		caption: "Window title",
        standardCaption : true,
		advanced : true
	}),

	'open_in' : $.extend({}, baseField, {
		caption: 'Open in',
		advanced : true,
		type : 'select',
		options : [
			['Same window or tab', 'same_window'],
			['New window', 'new_window'],
			['Frame', 'iframe']
		],
		defaultValue: 'same_window',
		visible: false
	}),

	'iframe_height' : $.extend({}, baseField, {
		caption: 'Frame height (pixels)',
		advanced : true,
		visible: function(menuItem) {
			return wsEditorData.wsMenuEditorPro && (getFieldValue(menuItem, 'open_in') === 'iframe');
		},

		display: function(menuItem, displayValue, input) {
			input.prop('placeholder', 'Auto');
			if (displayValue === 0 || displayValue === '0') {
				displayValue = '';
			}
			return displayValue;
		},

		write: function(menuItem, value) {
			value = parseInt(value, 10);
			if (isNaN(value) || (value < 0)) {
				value = 0;
			}
			value = Math.round(value);

			if (value > 10000) {
				value = 10000;
			}

			if (value === 0) {
				menuItem.iframe_height = null;
			} else {
				menuItem.iframe_height = value;
			}

		}
	}),

	'css_class' : $.extend({}, baseField, {
		caption: 'CSS classes',
		advanced : true,
		onlyForTopMenus: true
	}),

	'icon_url' : $.extend({}, baseField, {
		caption: 'Icon URL',
		type : 'icon_selector',
		advanced : true,
		defaultValue: 'div',
		onlyForTopMenus: true,

		display: function(menuItem, displayValue, input, containerNode) {
			//Display the current icon in the selector.
			var cssClass = getFieldValue(menuItem, 'css_class', '');
			var iconUrl = getFieldValue(menuItem, 'icon_url', '', containerNode);
			displayValue = iconUrl;

			//When submenu icon visibility is set to "only if manually selected",
			//don't show the default submenu icons.
			var isDefault = (typeof menuItem.icon_url === 'undefined') || (menuItem.icon_url === null);
			if (isDefault && (wsEditorData.submenuIconsEnabled === 'if_custom') && containerNode.hasClass('ws_item')) {
				iconUrl = 'none';
				cssClass = '';
			}

			var selectButton = input.closest('.ws_edit_field').find('.ws_select_icon');
			var cssIcon = selectButton.find('.icon16');
			var imageIcon = selectButton.find('img');

			var matches = cssClass.match(/\b(ame-)?menu-icon-([^\s]+)\b/);
			var dashiconMatches = iconUrl && iconUrl.match(/^\s*(dashicons-[a-z0-9\-]+)/);

			//Icon URL takes precedence over icon class.
			if ( iconUrl && iconUrl !== 'none' && iconUrl !== 'div' && !dashiconMatches ) {
				//Regular image icon.
				cssIcon.hide();
				imageIcon.prop('src', iconUrl).show();
			} else if ( dashiconMatches ) {
				//Dashicon.
				imageIcon.hide();
				cssIcon.removeClass().addClass('icon16 dashicons ' + dashiconMatches[1]).show();
			} else if ( matches ) {
				//Other CSS-based icon.
				imageIcon.hide();
				var iconClass = (matches[1] ? matches[1] : '') + 'icon-' + matches[2];
				cssIcon.removeClass().addClass('icon16 ' + iconClass).show();
			} else {
				//This menu has no icon at all. This is actually a valid state
				//and WordPress will display a menu like that correctly.
				imageIcon.hide();
				cssIcon.removeClass().addClass('icon16').show();
			}

			return displayValue;
		}
	}),

	'colors' : $.extend({}, baseField, {
		caption: 'Color scheme',
		defaultValue: 'Default',
		type: 'color_scheme_editor',
		onlyForTopMenus: true,
		visible: false,
		advanced : true,

		display: function(menuItem, displayValue, input, containerNode) {
			var colors = getFieldValue(menuItem, 'colors', {}) || {};
			var colorList = containerNode.find('.ws_color_scheme_display');

			colorList.empty();
			var count = 0, maxColorsToShow = 7;

			$.each(colors, function(name, value) {
				if ( !value || (count >= maxColorsToShow) ) {
					return;
				}

				colorList.append(
					$('<span></span>').addClass('ws_color_display_item').css('background-color', value)
				);
				count++;
			});

			if (count === 0) {
				colorList.append('Default');
			}

			return 'Placeholder. You should never see this.';
		},

		write: function(menuItem) {
			//Menu colors can't be directly edited.
		}
	}),

	'page_heading' : $.extend({}, baseField, {
		caption: 'Page heading',
		advanced : true,
		onlyForTopMenus: false,
		visible: false
	}),

	'hookname' : $.extend({}, baseField, {
		caption: 'Hook name',
		advanced : true,
		onlyForTopMenus: true
	}),

	'is_always_open' : $.extend({}, baseField, {
		caption: 'Keep this menu open',
		advanced : true,
		onlyForTopMenus: true,
		type: 'checkbox',
		standardCaption: false
	})
};

AmeEditorApi.getItemDisplayUrl = function(menuItem) {
	var url = getFieldValue(menuItem, 'file', '');
	if (menuItem.template_id !== '') {
		var defaultUrl = itemTemplates.getDefaultValue(menuItem.template_id, 'url');
		if (defaultUrl) {
			url = defaultUrl;
		}
	}
	return url;
};

/*
 * Create editors for the visible fields of a menu entry and append them to the specified node.
 */
function buildEditboxFields(fieldContainer, entry, isTopLevel){
	isTopLevel = (typeof isTopLevel === 'undefined') ? false : isTopLevel;

	var basicFields = $('<div class="ws_edit_panel ws_basic"></div>').appendTo(fieldContainer);
    var advancedFields = $('<div class="ws_edit_panel ws_advanced"></div>').appendTo(fieldContainer);

    if ( wsEditorData.hideAdvancedSettings ){
    	advancedFields.css('display', 'none');
    }

	for (var field_name in knownMenuFields){
		if (!knownMenuFields.hasOwnProperty(field_name)) {
			continue;
		}

		var fieldSpec = knownMenuFields[field_name];
		if (fieldSpec.onlyForTopMenus && !isTopLevel) {
			continue;
		}

		var field = buildEditboxField(entry, field_name, fieldSpec);
		if (field){
            if (fieldSpec.advanced){
                advancedFields.append(field);
            } else {
                basicFields.append(field);
            }
		}
	}

	//Add a link that shows/hides advanced fields
	fieldContainer.append(
		'<div class="ws_toggle_container"><a href="#" class="ws_toggle_advanced_fields"'+
		(wsEditorData.hideAdvancedSettings ? '' : ' style="display:none;" ' )+'>'+
		(wsEditorData.hideAdvancedSettings ? wsEditorData.captionShowAdvanced : wsEditorData.captionHideAdvanced)
		+'</a></div>'
	);
}

/*
 * Create an editor for a specified field.
 */
//noinspection JSUnusedLocalSymbols
function buildEditboxField(entry, field_name, field_settings){
	//Build a form field of the appropriate type
	var inputBox = null;
	var basicTextField = '<input type="text" class="ws_field_value">';
	//noinspection FallthroughInSwitchStatementJS
	switch(field_settings.type){
		case 'select':
			inputBox = $('<select class="ws_field_value">');
			var option = null;
			for( var index = 0; index < field_settings.options.length; index++ ){
				var optionTitle = field_settings.options[index][0];
				var optionValue = field_settings.options[index][1];

				option = $('<option>')
					.val(optionValue)
					.text(optionTitle);
				option.appendTo(inputBox);
			}
			break;

        case 'checkbox':
            inputBox = $('<label><input type="checkbox" class="ws_field_value"> <span class="ws_field_label_text">'+
                field_settings.caption + '</span></label>'
            );
            break;

		case 'access_editor':
			inputBox = $('<input type="text" class="ws_field_value" readonly="readonly">')
                .add('<input type="button" class="button ws_launch_access_editor" value="Edit...">');
			break;

		case 'icon_selector':
			//noinspection HtmlUnknownTag
			inputBox = $(basicTextField)
                .add('<button class="button ws_select_icon" title="Select icon"><div class="icon16 icon-settings"></div><img src="" style="display:none;"></button>');
			break;

		case 'color_scheme_editor':
			inputBox = $('<span class="ws_color_scheme_display">Placeholder</span>')
				.add('<input type="button" class="button ws_open_color_editor" value="Edit...">');
			break;

		case 'text':
			/* falls through */
		default:
			inputBox = $(basicTextField);
	}


	var className = "ws_edit_field ws_edit_field-"+field_name;
	if (field_settings.addDropdown){
		className += ' ws_has_dropdown';
	}
	if (!field_settings.standardCaption) {
		className += ' ws_no_field_caption';
	}

	var caption = '';
	if (field_settings.standardCaption) {
		caption = '<span class="ws_field_label_text">' + field_settings.caption + '</span><br>';
	}
	var editField = $('<div>' + caption + '</div>')
		.attr('class', className)
		.append(inputBox);

	if (field_settings.addDropdown) {
		//Add a dropdown button
		var dropdownId = field_settings.addDropdown;
		editField.append(
			$('<input type="button" value="&#9660;">')
				.addClass('button ws_dropdown_button ' + dropdownId + '_trigger')
				.attr('tabindex', '-1')
				.data('dropdownId', dropdownId)
		);
	}

	editField
		.append(
			$('<img class="ws_reset_button" title="Reset to default value">')
				.attr('src', wsEditorData.imagesUrl + '/transparent16.png')
		).data('field_name', field_name);

	var visible = true;
	if (typeof field_settings.visible === 'function') {
		visible = field_settings.visible(entry, field_name);
	} else {
		visible = field_settings.visible;
	}
	if (!visible) {
		editField.css('display', 'none');
	}

	return editField;
}

/**
 * Get the parent menu of a menu item.
 *
 * @param containerNode A DOM element as a jQuery object.
 * @return {jQuery} Parent container node, or an empty jQuery set.
 */
function getParentMenuNode(containerNode) {
	var submenu = containerNode.closest('.ws_submenu', '#ws_menu_editor'),
		parentId = submenu.data('parent_menu_id');
	if (parentId) {
		return $('#' + parentId);
	} else {
		return $([]);
	}
}

/**
 * Get all submenu items of a menu item.
 *
 * @param {jQuery} containerNode
 * @return {jQuery} A list of submenu item container nodes, or an empty set.
 */
function getSubmenuItemNodes(containerNode) {
	var subMenuId = containerNode.data('submenu_id');
	if (subMenuId) {
		return $('#' + subMenuId).find('.ws_container');
	} else {
		return $([]);
	}
}

/**
 * Apply a callback recursively to a menu item and all of its children, in depth-first order.
 * The callback will be invoked with two arguments: (containerNode, menuItem).
 *
 * @param containerNode
 * @param {Function} callback
 */
function walkMenuTree(containerNode, callback) {
	getSubmenuItemNodes(containerNode).each(function() {
		walkMenuTree($(this), callback);
	});
	callback(containerNode, containerNode.data('menu_item'));
}

/**
 * Update the UI elements that that indicate whether the currently selected
 * actor can access a menu item.
 *
 * @param containerNode
 */
function updateActorAccessUi(containerNode) {
	//Update the permissions checkbox & UI
	var menuItem = containerNode.data('menu_item');
	if (selectedActor !== null) {
		var hasAccess = actorCanAccessMenu(menuItem, selectedActor);
		var hasCustomPermissions = actorHasCustomPermissions(menuItem, selectedActor);

		var isOverrideActive = !hasAccess && getFieldValue(menuItem, 'restrict_access_to_items', false);

		//Check if the parent menu has the "hide all submenus if this is hidden" override in effect.
		var currentChild = containerNode, parentNode, parentItem;
		do {
			parentNode = getParentMenuNode(currentChild);
			parentItem = parentNode.data('menu_item');
			if (
				parentItem
				&& getFieldValue(parentItem, 'restrict_access_to_items', false)
				&& !actorCanAccessMenu(parentItem, selectedActor)
			) {
				hasAccess = false;
				isOverrideActive = true;
				break;
			}
			currentChild = parentNode;
		} while (parentNode.length > 0);

		var checkbox = containerNode.find('.ws_actor_access_checkbox');
		checkbox.prop('checked', hasAccess);

		//Display the checkbox differently if some items of this menu are hidden and some are visible,
		//or if their permissions don't match this menu's permissions.
		var submenuItems = getSubmenuItemNodes(containerNode);
		if ((submenuItems.length === 0) || isOverrideActive) {
			//Either this menu doesn't contain any items, or their permissions don't matter because they're overridden.
			checkbox.prop('indeterminate', false);
		} else {
			var differentPermissions = false;
			submenuItems.each(function() {
				var item = $(this).data('menu_item');
				if ( !item ) { //Skip placeholder items created by drag & drop operations.
					return true;
				}
				var hasSubmenuAccess = actorCanAccessMenu(item, selectedActor);
				if (hasSubmenuAccess !== hasAccess) {
					differentPermissions = true;
					return false;
				}
				return true;
			});

			checkbox.prop('indeterminate', differentPermissions);
		}

		containerNode.toggleClass('ws_is_hidden_for_actor', !hasAccess);
		containerNode.toggleClass('ws_has_custom_permissions_for_actor', hasCustomPermissions);
		setMenuFlag(containerNode, 'custom_actor_permissions', hasCustomPermissions);
		setMenuFlag(containerNode, 'hidden_from_others', false);
	} else {
		containerNode.removeClass('ws_is_hidden_for_actor ws_has_custom_permissions_for_actor');
		setMenuFlag(containerNode, 'custom_actor_permissions', false);

		var currentUserActor = 'user:' + wsEditorData.currentUserLogin;
		var otherActors = _(wsEditorData.actors).keys().without(currentUserActor, 'special:super_admin').value(),
			hiddenFromCurrentUser = ! actorCanAccessMenu(menuItem, currentUserActor),
			hiddenFromOthers = ! _.some(otherActors, _.curry(actorCanAccessMenu, 2)(menuItem));
		setMenuFlag(
			containerNode,
			'hidden_from_others',
			hiddenFromOthers,
			hiddenFromCurrentUser ? 'Hidden from everyone' : 'Hidden from everyone except you'
		);
	}

	//Update the "hidden" flag.
	setMenuFlag(containerNode, 'hidden', itemHasHiddenFlag(menuItem, selectedActor));
}

/**
 * Like updateActorAccessUi() except it updates the specified menu's parent, not the menu itself.
 * If the menu has no parent (i.e. it's a top-level menu), this function does nothing.
 *
 * @param containerNode Either a menu item or a submenu container.
 */
function updateParentAccessUi(containerNode) {
	var submenu;
	if ( containerNode.is('.ws_submenu') ) {
		submenu = containerNode;
	} else {
		submenu = containerNode.parent();
	}

	var parentId = submenu.data('parent_menu_id');
	if (parentId) {
		updateActorAccessUi($('#' + parentId));
	}
}

/**
 * Update an edit widget with the current menu item settings.
 *
 * @param containerNode
 */
function updateItemEditor(containerNode) {
	var menuItem = containerNode.data('menu_item');

	//Apply flags based on the item's state.
	var flags = ['hidden', 'unused', 'custom'];
	for (var i = 0; i < flags.length; i++) {
		setMenuFlag(containerNode, flags[i], getFieldValue(menuItem, flags[i], false));
	}

	//Update the permissions checkbox & other actor-specific UI
	updateActorAccessUi(containerNode);

	//Update all input fields with the current values.
	containerNode.find('.ws_edit_field').each(function(index, field) {
		field = $(field);
		var fieldName = field.data('field_name');
		var input = field.find('.ws_field_value').first();

		var hasADefaultValue = itemTemplates.hasDefaultValue(menuItem.template_id, fieldName);
		var defaultValue = itemTemplates.getDefaultValue(menuItem.template_id, fieldName);
		var isDefault = hasADefaultValue && ((typeof menuItem[fieldName] === 'undefined') || (menuItem[fieldName] === null));

        if (fieldName === 'access_level') {
            isDefault = (getFieldValue(menuItem, 'extra_capability', '') === '')
				&& isEmptyObject(menuItem.grant_access)
				&& (!getFieldValue(menuItem, 'restrict_access_to_items', false));
        }

		field.toggleClass('ws_has_no_default', !hasADefaultValue);
		field.toggleClass('ws_input_default', isDefault);

		var displayValue = isDefault ? defaultValue : menuItem[fieldName];
		if (knownMenuFields[fieldName].display !== null) {
			displayValue = knownMenuFields[fieldName].display(menuItem, displayValue, input, containerNode);
		}

        setInputValue(input, displayValue);

		if (typeof (knownMenuFields[fieldName].visible) === 'function') {
			var isFieldVisible = knownMenuFields[fieldName].visible(menuItem, fieldName);
			if (isFieldVisible) {
				field.css('display', '');
			} else {
				field.css('display', 'none');
			}
		}
    });
}

function isEmptyObject(obj) {
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            return false;
        }
    }
    return true;
}

/**
 * Get the current value of a single menu field.
 *
 * If the specified field is not set, this function will attempt to retrieve it
 * from the "defaults" property of the menu object. If *that* fails, it will return
 * the value of the optional third argument defaultValue.
 *
 * @param {Object} entry
 * @param {string} fieldName
 * @param {*} [defaultValue]
 * @param {jQuery} [containerNode]
 * @return {*}
 */
function getFieldValue(entry, fieldName, defaultValue, containerNode){
	if ( (typeof entry[fieldName] === 'undefined') || (entry[fieldName] === null) ) {

		//By default, a submenu item has the same icon as its parent.
		if ((fieldName === 'icon_url') && containerNode && (wsEditorData.submenuIconsEnabled !== 'never')) {
			var parentContainerNode = getParentMenuNode(containerNode),
				parentMenuItem = parentContainerNode.data('menu_item');
			if (parentMenuItem) {
				return getFieldValue(parentMenuItem, fieldName, defaultValue, parentContainerNode);
			}
		}

		var hasDefault = (typeof entry.defaults !== 'undefined') && (typeof entry.defaults[fieldName] !== 'undefined');
		if (hasDefault){
			return entry.defaults[fieldName];
		} else {
			return defaultValue;
		}
	} else {
		return entry[fieldName];
	}
}

AmeEditorApi.getFieldValue = getFieldValue;

/*
 * Make a menu container sortable
 */
function makeBoxSortable(menuBox){
	//Make the submenu sortable
	menuBox.sortable({
		items: '> .ws_container',
		cursor: 'move',
		dropOnEmpty: true,
		cancel : '.ws_editbox, .ws_edit_link',

		placeholder: 'ws_container ws_sortable_placeholder',
		forcePlaceholderSize: true,

		stop: function(even, ui) {
			//Fix incorrect item overlap caused by jQuery.sortable applying the initial z-index as an inline style.
			ui.item.css('z-index', '');

			//Fix submenu container height. It should be tall enough to reach the selected parent menu.
			if (ui.item.hasClass('ws_menu') && ui.item.hasClass('ws_active')) {
				AmeEditorApi.updateSubmenuBoxHeight(ui.item);
			}
		}
	});
}

/**
 * Iterates over all menu items invoking a callback for each item.
 *
 * The callback will be passed two arguments: the menu item and its UI container node (a jQuery object).
 * You can stop iteration by returning false from the callback.
 *
 * @param {Function} callback
 * @param {boolean} [skipSeparators] Defaults to true. Set to false to include separators in the iteration.
 */
AmeEditorApi.forEachMenuItem = function(callback, skipSeparators) {
	if (typeof skipSeparators === 'undefined') {
		skipSeparators = true;
	}

	$('#ws_menu_editor').find('.ws_container').each(function() {
		var containerNode = $(this);
		if ( !(skipSeparators && containerNode.hasClass('ws_menu_separator')) ) {
			return callback(containerNode.data('menu_item'), containerNode);
		}
	});
};

/***************************************************************************
                       Parsing & encoding menu inputs
 ***************************************************************************/

/**
 * Encode the current menu structure as JSON
 *
 * @return {String} A JSON-encoded string representing the current menu tree loaded in the editor.
 */
function encodeMenuAsJSON(tree){
	if (typeof tree === 'undefined' || !tree) {
		tree = readMenuTreeState();
	}
	tree.format = {
		name: wsEditorData.menuFormatName,
		version: wsEditorData.menuFormatVersion
	};
	return $.toJSON(tree);
}

function readMenuTreeState(){
	var tree = {};
	var menuPosition = 0;
	var itemsByFilename = {};

	//Gather all menus and their items
	$('#ws_menu_box').find('.ws_menu').each(function() {
		var containerNode = this;
		var menu = readItemState(containerNode, menuPosition++);

		//Attach the current menu to the main structure.
		var filename = (menu.file !== null) ? menu.file : menu.defaults.file;

		//Give unclickable items unique keys.
		if (menu.template_id === wsEditorData.unclickableTemplateId) {
			ws_paste_count++;
			filename = '#' + wsEditorData.unclickableTemplateClass + '-' + ws_paste_count;
		} else if (menu.template_id === wsEditorData.embeddedPageTemplateId) {
			ws_paste_count++;
			filename = '#embedded-page-' + ws_paste_count;
		}

		//Prevent the user from saving top level items with duplicate URLs.
		//WordPress indexes the submenu array by parent URL and AME uses a {url : menu_data} hashtable internally.
		//Duplicate URLs would cause problems for both.
		if (itemsByFilename.hasOwnProperty(filename)) {
			throw {
				code: 'duplicate_top_level_url',
				message: 'Error: Found a duplicate URL! All top level menus must have unique URLs.',
				duplicates: [itemsByFilename[filename], containerNode]
			};
		}

		tree[filename] = menu;
		itemsByFilename[filename] = containerNode;
	});

	AmeCapabilityManager.pruneGrantedCapabilities('user');

	return {
		tree: tree,
		color_presets: $.extend(true, {}, colorPresets),
		granted_capabilities: AmeCapabilityManager.getGrantedCapabilities()
	};
}

AmeEditorApi.readMenuTreeState = readMenuTreeState;
AmeEditorApi.encodeMenuAsJson = encodeMenuAsJSON;

/**
 * Extract the current menu item settings from its editor widget.
 *
 * @param itemDiv DOM node containing the editor widget, usually with the .ws_item or .ws_menu class.
 * @param {Number} [position] Menu item position among its sibling menu items. Defaults to zero.
 * @return {Object} A menu object in the tree format.
 */
function readItemState(itemDiv, position){
	position = (typeof position === 'undefined') ? 0 : position;

	itemDiv = $(itemDiv);
	var item = $.extend({}, wsEditorData.blankMenuItem, itemDiv.data('menu_item'), readAllFields(itemDiv));

	item.defaults = itemDiv.data('menu_item').defaults;

	//Save the position data
	item.position = position;
	item.defaults.position = position; //The real default value will later overwrite this

	item.separator = itemDiv.hasClass('ws_menu_separator');
	item.custom = menuHasFlag(itemDiv, 'custom');

	//Gather the menu's sub-items, if any
	item.items = [];
	var subMenuId = itemDiv.data('submenu_id');
	if (subMenuId) {
		var itemPosition = 0;
		$('#' + subMenuId).find('.ws_item').each(function () {
			var sub_item = readItemState(this, itemPosition++);
			item.items.push(sub_item);
		});
	}

	return item;
}

/*
 * Extract the values of all menu/item fields present in a container node
 *
 * Inputs:
 *	container - a jQuery collection representing the node to read.
 */
function readAllFields(container){
	if ( !container.hasClass('ws_container') ){
		container = container.closest('.ws_container');
	}

	if ( !container.data('field_editors_created') ){
		return container.data('menu_item');
	}

	var state = {};

	//Iterate over all fields of the item
	container.find('.ws_edit_field').each(function() {
		var field = $(this);

		//Get the name of this field
		var field_name = field.data('field_name');
		//Skip if unnamed
		if (!field_name) {
			return true;
		}

		//Hackety-hack. The "Page" input is for display purposes and contains more than just the ID. Skip it.
		//Eventually we'll need a better way to handle this.
		if (field_name === 'embedded_page_id') {
			return true;
		}

		//Find the field (usually an input or select element).
		var input_box = field.find('.ws_field_value');

		//Save null if default used, custom value otherwise
		if (field.hasClass('ws_input_default')){
			state[field_name] = null;
		} else {
			state[field_name] = getInputValue(input_box);
		}
		return true;
	});

    //Permission settings are not stored in the visible access_level field (that's just for show),
    //so do not attempt to read them from there.
    state.access_level = null;

	return state;
}


/***************************************************************************
 Flag manipulation
 ***************************************************************************/

var item_flags = {
	'custom':'This is a custom menu item',
	'unused':'This item was automatically recreated. You cannot delete a non-custom item, but you could hide it.',
	'hidden':'Cosmetically hidden',
	'custom_actor_permissions' : "The selected role has custom permissions for this item.",
	'hidden_from_others' : 'Hidden from everyone except you.'
};

function setMenuFlag(item, flag, state, title) {
	title = title || item_flags[flag];
	item = $(item);

	var item_class = 'ws_' + flag;
	var img_class = 'ws_' + flag + '_flag';

	item.toggleClass(item_class, state);
	if (state) {
		//Add the flag image.
		var flag_container = item.find('.ws_flag_container');
		var image = flag_container.find('.' + img_class);
		if (image.length === 0) {
			image = $('<div></div>').addClass('ws_flag').addClass(img_class);
			flag_container.append(image);
		}
		image.attr('title', title);
	} else {
		//Remove the flag image.
		item.find('.' + img_class).remove();
	}
}

function menuHasFlag(item, flag){
	return $(item).hasClass('ws_'+flag);
}

//The "hidden" flag is special. There's both a global version and one that's actor-specific.

/**
 * Check if a menu item is hidden from an actor.
 * This function only checks the "hidden" and "hidden_from_actor" flags, not permissions.
 *
 * @param {Object} menuItem
 * @param {string|null} actor
 * @returns {boolean}
 */
function itemHasHiddenFlag(menuItem, actor) {
	var isHidden = false,
		userActors,
		userPrefix = 'user:',
		userLogin;

	//(Only) A globally hidden item is hidden from everyone.
	if ((actor === null) || menuItem.hidden) {
		return menuItem.hidden;
	}

	if (actor.substr(0, userPrefix.length) === userPrefix) {
		//You can set an exception for a specific user. It takes precedence.
		if (menuItem.hidden_from_actor.hasOwnProperty(actor)) {
			isHidden = menuItem.hidden_from_actor[actor];
		} else {
			//Otherwise the item is hidden only if it is hidden from all of the user's roles.
			userLogin = selectedActor.substr(userPrefix.length);
			userActors = AmeCapabilityManager.getUserActors(userLogin, true);
			for (var i = 0; i < userActors.length; i++) {
				if (menuItem.hidden_from_actor.hasOwnProperty(userActors[i]) && menuItem.hidden_from_actor[userActors[i]]) {
					isHidden = true;
				} else {
					isHidden = false;
					break;
				}
			}
		}
	} else {
		//Roles and the super admin are straightforward.
		isHidden = menuItem.hidden_from_actor.hasOwnProperty(actor) && menuItem.hidden_from_actor[actor];
	}

	return isHidden;
}

/**
 * Toggle menu visibility without changing its permissions.
 *
 * Applies to the selected actor, or all actors if no actor is selected.
 *
 * @param {jQuery} selection A menu container node.
 * @param {boolean} [isHidden] Optional. True = hide the menu, false = show the menu.
 */
function toggleItemHiddenFlag(selection, isHidden) {
	var menuItem = selection.data('menu_item');

	//By default, invert the current state.
	if (typeof isHidden === 'undefined') {
		isHidden = !itemHasHiddenFlag(menuItem, selectedActor);
	}

	//Mark the menu as hidden/visible
	if (selectedActor === null) {
		//For ALL roles and users.
		menuItem.hidden = isHidden;
		menuItem.hidden_from_actor = {};
	} else {
		//Just for the current role.
		if (isHidden) {
			menuItem.hidden_from_actor[selectedActor] = true;
		} else {
			if (selectedActor.indexOf('user:') === 0) {
				//User-specific exception. Lets you can hide a menu from all admins but leave it visible to yourself.
				menuItem.hidden_from_actor[selectedActor] = false;
			} else {
				delete menuItem.hidden_from_actor[selectedActor];
			}
		}

		//When the user un-hides a menu that was globally hidden via the "hidden" flag, we must remove
		//that flag but also make sure the menu stays hidden from other roles.
		if (!isHidden && menuItem.hidden) {
			menuItem.hidden = false;
			$.each(wsEditorData.actors, function(otherActor) {
				if (otherActor !== selectedActor) {
					menuItem.hidden_from_actor[otherActor] = true;
				}
			});
		}
	}
	setMenuFlag(selection, 'hidden', isHidden);

	//Also mark all of it's submenus as hidden/visible
	var submenuId = selection.data('submenu_id');
	if (submenuId) {
		$('#' + submenuId + ' .ws_item').each(function(){
			toggleItemHiddenFlag($(this), isHidden);
		});
	}
}

/***********************************************************
                  Capability manipulation
 ************************************************************/

function actorCanAccessMenu(menuItem, actor) {
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	//By default, any actor that has the required cap has access to the menu.
	//Users can override this on a per-menu basis.
	var requiredCap = getFieldValue(menuItem, 'access_level', '< Error: access_level is missing! >');
	var actorHasAccess = false;
	if (menuItem.grant_access.hasOwnProperty(actor)) {
		actorHasAccess = menuItem.grant_access[actor];
	} else {
		actorHasAccess = AmeCapabilityManager.hasCap(actor, requiredCap, menuItem.grant_access);
	}
	return actorHasAccess;
}

AmeEditorApi.actorCanAccessMenu = actorCanAccessMenu;

function actorHasCustomPermissions(menuItem, actor) {
	if (menuItem.grant_access && menuItem.grant_access.hasOwnProperty && menuItem.grant_access.hasOwnProperty(actor)) {
		return (menuItem.grant_access[actor] !== null);
	}
	return false;
}

/**
 * @param containerNode
 * @param {string|Object.<string, boolean>} actor
 * @param {boolean} [allowAccess]
 */
function setActorAccess(containerNode, actor, allowAccess) {
	var menuItem = containerNode.data('menu_item');

	//grant_access comes from PHP, which JSON-encodes empty assoc. arrays as arrays.
	//However, we want it to be a dictionary.
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	if (typeof actor === 'string') {
		menuItem.grant_access[actor] = !!allowAccess;
	} else {
		_.assign(menuItem.grant_access, actor);
	}
}

function setSelectedActor(actor) {
	//Check if the specified actor really exists. The actor ID
	//could be invalid if it was supplied by the user.
	if (actor !== null) {
		var newSelectedItem = $('a[href$="#'+ actor +'"]');
		if (newSelectedItem.length === 0) {
			return;
		}
	}

	selectedActor = actor;

	//Highlight the actor.
	var actorSelector = $('#ws_actor_selector');
	$('.current', actorSelector).removeClass('current');

	if (selectedActor === null) {
		$('a.ws_no_actor').addClass('current');
	} else {
		newSelectedItem.addClass('current');
	}

	//There are some UI elements that can be visible or hidden depending on whether an actor is selected.
	var editorNode = $('#ws_menu_editor');
	editorNode.toggleClass('ws_is_actor_view', (selectedActor !== null));

	//Update the menu item states to indicate whether they're accessible.
	editorNode.find('.ws_container').each(function() {
		updateActorAccessUi($(this));
	});
}

/**
 * Make a menu item inaccessible to everyone except a particular actor.
 *
 * Will not change access settings for actors that are more specific than the input actor.
 * For example, if the input actor is a "role:", this function will only disable other roles,
 * but will leave "user:" actors untouched.
 *
 * @param {Object} menuItem
 * @param {String} actor
 * @return {Object}
 */
function denyAccessForAllExcept(menuItem, actor) {
	//grant_access comes from PHP, which JSON-encodes empty assoc. arrays as arrays.
	//However, we want it to be a dictionary.
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	$.each(wsEditorData.actors, function(otherActor) {
		//If the input actor is more or equally specific...
		if ((actor === null) || (AmeCapabilityManager.compareActorSpecificity(actor, otherActor) >= 0)) {
			menuItem.grant_access[otherActor] = false;
		}
	});

	if (actor !== null) {
		menuItem.grant_access[actor] = true;
	}
	return menuItem;
}

/***************************************************************************
 Event handlers
 ***************************************************************************/

//Cut & paste stuff
var menu_in_clipboard = null;
var ws_paste_count = 0;

//Color preset stuff.
var colorPresets = {},
	wasPresetDropdownPopulated = false;

$(document).ready(function(){
	//Some editor elements are only available in the Pro version.
	if (wsEditorData.wsMenuEditorPro) {
		knownMenuFields.open_in.visible = true;
		knownMenuFields.access_level.visible = true;
		knownMenuFields.page_heading.visible = true;
		knownMenuFields.colors.visible = true;
		knownMenuFields.extra_capability.visible = false; //Superseded by the "access_level" field.

		//The Pro version supports submenu icons, but they can be disabled by the user.
		knownMenuFields.icon_url.onlyForTopMenus = (wsEditorData.submenuIconsEnabled === 'never');

		$('.ws_hide_if_pro').hide();
	}

	//Let other plugins filter knownMenuFields.
	$(document).trigger('filterMenuFields.adminMenuEditor', [knownMenuFields, baseField]);

	//Make the top menu box sortable (we only need to do this once)
    var mainMenuBox = $('#ws_menu_box');
    makeBoxSortable(mainMenuBox);

	/***************************************************************************
	                  Event handlers for editor widgets
	 ***************************************************************************/
	var menuEditorNode = $('#ws_menu_editor'),
		submenuBox = $('#ws_submenu_box'),
		submenuDropZone = submenuBox.closest('.ws_main_container').find('.ws_dropzone');

	//Highlight the clicked menu item and show it's submenu
	var currentVisibleSubmenu = null;
	menuEditorNode.on('click', '.ws_container', (function () {
		var container = $(this);
		if (container.hasClass('ws_active')) {
			return;
		}

		//Highlight the active item and un-highlight the previous one
		container.addClass('ws_active');
		container.siblings('.ws_active').removeClass('ws_active');
		if (container.hasClass('ws_menu')) {
			//Show/hide the appropriate submenu
			if ( currentVisibleSubmenu ){
				currentVisibleSubmenu.hide();
			}
			currentVisibleSubmenu = $('#' + container.data('submenu_id')).show();

			updateSubmenuBoxHeight(container);

			currentVisibleSubmenu.closest('.ws_main_container')
				.find('.ws_toolbar .ws_delete_menu_button')
				.toggleClass('ws_button_disabled', !canDeleteItem(getSelectedSubmenuItem()));
		}

		//Make the "delete" button appear disabled if you can't delete this item.
		container.closest('.ws_main_container')
			.find('.ws_toolbar .ws_delete_menu_button')
			.toggleClass('ws_button_disabled', !canDeleteItem(container));
    }));

	function updateSubmenuBoxHeight(selectedMenu) {
		//Make the submenu box tall enough to reach the selected item.
		//This prevents the menu tip (if any) from floating in empty space.
		if (selectedMenu.hasClass('ws_menu_separator')) {
			submenuBox.css('min-height', '');
		} else {
			var menuTipHeight = 30,
				empiricalExtraHeight = 4,
				verticalBoxOffset = (submenuBox.offset().top - mainMenuBox.offset().top),
				minSubmenuHeight = (selectedMenu.offset().top - mainMenuBox.offset().top)
					- verticalBoxOffset
					+ menuTipHeight - submenuDropZone.outerHeight() + empiricalExtraHeight;
			minSubmenuHeight = Math.max(minSubmenuHeight, 0);
			submenuBox.css('min-height', minSubmenuHeight);
		}
	}

	AmeEditorApi.updateSubmenuBoxHeight = updateSubmenuBoxHeight;

	//Show a notification icon next to the "Permissions" field when the menu item supports extended permissions.
	function updateExtPermissionsIndicator(container, menuItem) {
		var extPermissions = AmeItemAccessEditor.detectExtPermissions(AmeEditorApi.getItemDisplayUrl(menuItem)),
			fieldTitle = container.find('.ws_edit_field-access_level .ws_field_label_text'),
			indicator = fieldTitle.find('.ws_ext_permissions_indicator');

		if (wsEditorData.wsMenuEditorPro && (extPermissions !== null)) {
			if (indicator.length < 1) {
				indicator = $('<div class="dashicons dashicons-info ws_ext_permissions_indicator"></div>');
				fieldTitle.append(" ").append(indicator);
			}
			//Idea: Change the icon based on the kind of permissions available (post type, tags, etc).
			indicator.show().data('ext_permissions', extPermissions);
		} else {
			indicator.hide();
		}
	}

	menuEditorNode.on('adminMenuEditor:fieldChange', function(event, menuItem, fieldName) {
		if ((fieldName === 'template_id') || (fieldName === 'file')) {
			updateExtPermissionsIndicator($(event.target), menuItem);
		}
	});

	//Show/hide a menu's properties
	menuEditorNode.on('click', '.ws_edit_link', (function (event) {
		event.preventDefault();

		var container = $(this).parents('.ws_container').first();
		var box = container.find('.ws_editbox');

		//For performance, the property editors for each menu are only created
		//when the user tries to access access them for the first time.
		if ( !container.data('field_editors_created') ){
			var menuItem = container.data('menu_item');
			buildEditboxFields(box, menuItem, container.hasClass('ws_menu'));
			container.data('field_editors_created', true);
			updateItemEditor(container);
			updateExtPermissionsIndicator(container, menuItem);
		}

		$(this).toggleClass('ws_edit_link_expanded');
		//show/hide the editbox
		if ($(this).hasClass('ws_edit_link_expanded')){
			box.show();
		} else {
			//Make sure changes are applied before the menu is collapsed
			box.find('input').change();
			box.hide();
		}
    }));

    //The "Default" button : Reset to default value when clicked
    menuEditorNode.on('click', '.ws_reset_button', (function () {
        //Find the field div (it holds the field name)
        var field = $(this).parents('.ws_edit_field');
	    var fieldName = field.data('field_name');

		if ( (field.length > 0) && fieldName ) {
			//Extract the default value from the menu item.
            var containerNode = field.closest('.ws_container');
			var menuItem = containerNode.data('menu_item');

			if (fieldName === 'access_level') {
	            //This is a pretty nasty hack.
	            menuItem.grant_access = {};
	            menuItem.extra_capability = null;
				menuItem.restrict_access_to_items = false;
				delete menuItem.had_access_before_hiding;
            }

			if (itemTemplates.hasDefaultValue(menuItem.template_id, fieldName)) {
				menuItem[fieldName] = null;
				updateItemEditor(containerNode);
				updateParentAccessUi(containerNode);
			}
		}
	}));

	//When a field is edited, change it's appearance if it's contents don't match the default value.
    function fieldValueChange(){
	    /* jshint validthis:true */
        var input = $(this);
		var field = input.parents('.ws_edit_field').first();
	    var fieldName = field.data('field_name');

        if ((fieldName === 'access_level') || (fieldName === 'embedded_page_id')) {
            //These fields are read-only and can never be directly edited by the user.
            //Ignore spurious change events.
            return;
        }

	    var containerNode = field.parents('.ws_container').first();
	    var menuItem = containerNode.data('menu_item');

	    var oldValue = menuItem[fieldName];
	    var value = getInputValue(input);
	    var defaultValue = itemTemplates.getDefaultValue(menuItem.template_id, fieldName);
        var hasADefaultValue = (defaultValue !== null);

	    //Some fields/templates have no default values.
        field.toggleClass('ws_has_no_default', !hasADefaultValue);
        if (!hasADefaultValue) {
            field.removeClass('ws_input_default');
        }

        if (field.hasClass('ws_input_default') && (value == defaultValue)) {
            value = null; //null = use default.
        }

	    //Ignore changes where the new value is the same as the old one.
	    if (value === oldValue) {
		    return;
	    }

	    //Update the item.
	    if (knownMenuFields[fieldName].write !== null) {
		    knownMenuFields[fieldName].write(menuItem, value, input, containerNode);
	    } else {
		    menuItem[fieldName] = value;
	    }

	    updateItemEditor(containerNode);
	    updateParentAccessUi(containerNode);

	    containerNode.trigger('adminMenuEditor:fieldChange', [menuItem, fieldName]);
    }
	menuEditorNode.on('click change', '.ws_field_value', fieldValueChange);

	//Show/hide advanced fields
	menuEditorNode.on('click', '.ws_toggle_advanced_fields', function(){
		var self = $(this);
		var advancedFields = self.parents('.ws_container').first().find('.ws_advanced');

		if ( advancedFields.is(':visible') ){
			advancedFields.hide();
			self.text(wsEditorData.captionShowAdvanced);
		} else {
			advancedFields.show();
			self.text(wsEditorData.captionHideAdvanced);
		}

		return false;
	});

	//Allow/forbid items in actor-specific views
	menuEditorNode.on('click', 'input.ws_actor_access_checkbox', function() {
		if (selectedActor === null) {
			return;
		}

		var checked = $(this).is(':checked');
		var containerNode = $(this).closest('.ws_container');

		var menu = containerNode.data('menu_item');
		//Ask for confirmation if the user tries to hide Dashboard -> Home.
		if ( !checked && ((menu.template_id === 'index.php>index.php') || (menu.template_id === '>index.php')) ) {
			updateItemEditor(containerNode); //Resets the checkbox back to the old value.
			confirmDashboardHiding(function(ok) {
				if (ok) {
					setActorAccessForTreeAndUpdateUi(containerNode, selectedActor, checked);
				}
			});
		} else {
			setActorAccessForTreeAndUpdateUi(containerNode, selectedActor, checked);
		}
	});

	/**
	 * This confusingly named function sets actor access for the specified menu item
	 * and all of its children (if any). It also updates the UI with the new settings.
	 *
	 * (And it violates SRP in a particularly egregious manner.)
	 *
	 * @param containerNode
	 * @param {String|Object.<String, Boolean>} actor
	 * @param {Boolean} [allowAccess]
	 */
	function setActorAccessForTreeAndUpdateUi(containerNode, actor, allowAccess) {
		setActorAccess(containerNode, actor, allowAccess);

		//Apply the same permissions to sub-menus.
		var subMenuId = containerNode.data('submenu_id');
		if (subMenuId && containerNode.hasClass('ws_menu')) {
			$('.ws_item', '#' + subMenuId).each(function() {
				var node = $(this);
				setActorAccess(node, actor, allowAccess);
				updateItemEditor(node);
			});
		}

		updateItemEditor(containerNode);
		updateParentAccessUi(containerNode);
	}

	/**
	 * Confirm with the user that they want to hide "Dashboard -> Home".
	 *
	 * This particular menu is important because hiding it can cause an "insufficient permissions" error
	 * to be displayed right when someone logs in, making it look like login failed.
	 */
	var permissionConfirmationDialog = $('#ws-ame-dashboard-hide-confirmation').dialog({
		autoOpen: false,
		modal: true,
		closeText: ' ',
		width: 380,
		title: 'Warning'
	});
	var currentConfirmationCallback = function(ok) {};

	/**
	 * Confirm hiding "Dashboard -> Home".
	 *
	 * @param callback Called when the user selects an option. True = confirmed.
	 */
	function confirmDashboardHiding(callback) {
		//The user can disable the confirmation dialog.
		if (!wsEditorData.dashboardHidingConfirmationEnabled) {
			callback(true);
			return;
		}

		currentConfirmationCallback = callback;
		permissionConfirmationDialog.dialog('open');
	}

	$('#ws_confirm_menu_hiding, #ws_cancel_menu_hiding').click(function() {
		var confirmed = $(this).is('#ws_confirm_menu_hiding');
		var dontShowAgain = permissionConfirmationDialog.find('.ws_dont_show_again input[type="checkbox"]').is(':checked');

		currentConfirmationCallback(confirmed);
		permissionConfirmationDialog.dialog('close');

		if (dontShowAgain) {
			wsEditorData.dashboardHidingConfirmationEnabled = false;
			//Run an AJAX request to disable the dialog for this user.
			$.post(
				wsEditorData.adminAjaxUrl,
				{
					'action' : 'ws_ame_disable_dashboard_hiding_confirmation',
					'_ajax_nonce' : wsEditorData.disableDashboardConfirmationNonce
				}
			);
		}
	});


	/*************************************************************************
	                  Access editor dialog
	 *************************************************************************/

	AmeItemAccessEditor.setup({
		api: AmeEditorApi,
		actors: wsEditorData.actors,
		postTypes: wsEditorData.postTypes,
		taxonomies: wsEditorData.taxonomies,
		lodash: _,
		isPro: wsEditorData.wsMenuEditorPro,

		save: function(menuItem, containerNode, settings) {
			//Save the new settings.
			menuItem.extra_capability         = settings.extraCapability;
			menuItem.grant_access             = settings.grantAccess;
			menuItem.restrict_access_to_items = settings.restrictAccessToItems;

			//Save granted capabilities.
			var newlyDisabledCaps = {};
			_.forEach(settings.grantedCapabilities, function(capabilities, actor) {
				_.forEach(capabilities, function(grant, capability) {
					if (!_.isArray(grant)) {
						grant = [grant, null, null];
					}

					AmeCapabilityManager.setCap(actor, capability, grant[0], grant[1], grant[2]);

					if (!grant[0]) {
						if (!newlyDisabledCaps.hasOwnProperty(capability)) {
							newlyDisabledCaps[capability] = [];
						}
						newlyDisabledCaps[capability].push(actor);
					}
				});
			});

			AmeEditorApi.forEachMenuItem(function(menuItem, containerNode) {
				//When the user unchecks a capability, uncheck ALL menu items associated with that capability.
				//Anything less won't actually get rid of the capability as enabled menus auto-grant req. caps.
				var requiredCap = getFieldValue(menuItem, 'access_level');
				if (newlyDisabledCaps.hasOwnProperty(requiredCap)) {
					//It's enough to remove custom "allow" settings. The rest happens automatically - items that
					//have no custom per-role settings use capability checks.
					_.forEach(newlyDisabledCaps[requiredCap], function(actor) {
						if (_.get(menuItem.grant_access, actor) === true) {
							delete menuItem.grant_access[actor];
						}
					});
				}

				//Due to changed caps and cascading submenu overrides, changes to one item's permissions
				//can affect other items. Lets just update all items.
				updateActorAccessUi(containerNode);
			});

			//Refresh the UI.
			updateItemEditor(containerNode);
		}
	});

	menuEditorNode.on('click', '.ws_launch_access_editor', function() {
		var containerNode = $(this).parents('.ws_container').first();
		var menuItem = containerNode.data('menu_item');

		AmeItemAccessEditor.open({
			menuItem: menuItem,
			containerNode: containerNode,
			selectedActor: selectedActor,
			itemHasSubmenus: (!!(containerNode.data('submenu_id')) &&
				$('#' + containerNode.data('submenu_id')).find('.ws_item').length > 0)
		});
	});

	/***************************************************************************
		              General dialog handlers
	 ***************************************************************************/

	$(document).on('click', '.ws_close_dialog', function() {
		$(this).parents('.ui-dialog-content').dialog('close');
	});


	/***************************************************************************
	              Drop-down list for combo-box fields
	 ***************************************************************************/

	var capSelectorDropdown = $('#ws_cap_selector');
	var currentDropdownOwner = null; //The input element that the dropdown is currently associated with.
	var isDropdownBeingHidden = false;

	//Show/hide the capability drop-down list when the trigger button is clicked
	$('#ws_trigger_capability_dropdown').on('mousedown click', onDropdownTriggerClicked);
	menuEditorNode.on('mousedown click', '.ws_cap_selector_trigger', onDropdownTriggerClicked);

	function onDropdownTriggerClicked(event){
		/* jshint validthis:true */
		var inputBox = null;
		var button = $(this);

		//Find the input associated with the button that was clicked.
		if ( button.attr('id') === 'ws_trigger_capability_dropdown' ) {
			inputBox = $('#ws_extra_capability');
		} else {
			inputBox = button.closest('.ws_edit_field').find('.ws_field_value').first();
		}

		//If the user clicks the same button again while the dropdown is already visible,
		//ignore the click. The dropdown will be hidden by its "blur" handler.
		if (event.type === 'mousedown') {
			if ( capSelectorDropdown.is(':visible') && inputBox.is(currentDropdownOwner) ) {
				isDropdownBeingHidden = true;
			}
			return;
		} else if (isDropdownBeingHidden) {
			isDropdownBeingHidden = false; //Ignore the click event.
			return;
		}

		//A jQuery UI dialog widget will prevent focus from leaving the dialog. So if we want
		//the dropdown to be properly focused when displaying it in a dialog, we must make it
		//a child of the dialog's DOM node (and vice versa when it's not in a dialog).
		var parentContainer = $(this).closest('.ui-dialog, #ws_menu_editor');
		if ((parentContainer.length > 0) && (capSelectorDropdown.closest(parentContainer).length === 0)) {
			var oldHeight = capSelectorDropdown.height(); //Height seems to reset when moving to a new parent.
			capSelectorDropdown.detach().appendTo(parentContainer).height(oldHeight);
		}

		//Pre-select the current capability (will clear selection if there's no match).
		capSelectorDropdown.val(inputBox.val()).show();

		//Move the drop-down near the input box.
		var inputPos = inputBox.offset();
		capSelectorDropdown
			.css({
				position: 'absolute',
				zIndex: 1010 //Must be higher than the permissions dialog overlay.
			})
			.offset({
				left: inputPos.left,
				top : inputPos.top + inputBox.outerHeight()
			}).
			width(inputBox.outerWidth());

		currentDropdownOwner = inputBox;
		capSelectorDropdown.focus();
	}

	//Also show it when the user presses the down arrow in the input field (doesn't work in Opera).
	$('#ws_extra_capability').bind('keyup', function(event){
		if ( event.which === 40 ){
			$('#ws_trigger_capability_dropdown').click();
		}
	});

	//Event handlers for the drop-down lists themselves
	var dropdownNodes = $('.ws_dropdown');

	// Hide capability drop-down when it loses focus.
	dropdownNodes.blur(function(){
		capSelectorDropdown.hide();
	});

	dropdownNodes.keydown(function(event){

		//Hide it when the user presses Esc
		if ( event.which === 27 ){
			capSelectorDropdown.hide();
			if (currentDropdownOwner) {
				currentDropdownOwner.focus();
			}

		//Select an item & hide the list when the user presses Enter or Tab
		} else if ( (event.which === 13) || (event.which === 9) ){
			capSelectorDropdown.hide();

			if (currentDropdownOwner) {
				if ( capSelectorDropdown.val() ){
					currentDropdownOwner.val(capSelectorDropdown.val()).change();
				}
				currentDropdownOwner.focus();
			}

			event.preventDefault();
		}
	});

	//Eat Tab keys to prevent focus theft. Required to make the "select item on Tab" thing work.
	dropdownNodes.keyup(function(event){
		if ( event.which === 9 ){
			event.preventDefault();
		}
	});


	//Update the input & hide the list when an option is clicked
	dropdownNodes.click(function(){
		if (capSelectorDropdown.val()){
			capSelectorDropdown.hide();
			if (currentDropdownOwner) {
				currentDropdownOwner.val(capSelectorDropdown.val()).change().focus();
			}
		}
	});

	//Highlight an option when the user mouses over it (doesn't work in IE)
	dropdownNodes.mousemove(function(event){
		if ( !event.target ){
			return;
		}

		var option = event.target;
		if ( (typeof option.selected !== 'undefined') && !option.selected && option.value ){
			option.selected = true;
		}
	});

	/*************************************************************************
	                           Icon selector
	 *************************************************************************/
	var iconSelector = $('#ws_icon_selector');
	var currentIconButton = null; //Keep track of the last clicked icon button.

	//When the user clicks one of the available icons, update the menu item.
	iconSelector.on('click', '.ws_icon_option', function() {
		var selectedIcon = $(this).addClass('ws_selected_icon');
		iconSelector.hide();

		//Assign the selected icon to the menu.
		if (currentIconButton) {
			var container = currentIconButton.closest('.ws_container');
			var item = container.data('menu_item');

			//Remove the existing icon class, if any.
			var cssClass = getFieldValue(item, 'css_class', '');
			cssClass = jsTrim( cssClass.replace(/\b(ame-)?menu-icon-[^\s]+\b/, '') );

			if (selectedIcon.data('icon-class')) {
				//Add the new class.
				cssClass = selectedIcon.data('icon-class') + ' ' + cssClass;
				//Can't have both a class and an image or we'll get two overlapping icons.
				item.icon_url = '';
			} else if (selectedIcon.data('icon-url')) {
				item.icon_url = selectedIcon.data('icon-url');
			}
			item.css_class = cssClass;

			updateItemEditor(container);
		}

		currentIconButton = null;
	});

	//Show/hide the icon selector when the user clicks the icon button.
	menuEditorNode.on('click', '.ws_select_icon', function() {
		var button = $(this);
		//Clicking the same button a second time hides the icon list.
		if ( currentIconButton && button.is(currentIconButton) ) {
			iconSelector.hide();
			//noinspection JSUnusedAssignment
			currentIconButton = null;
			return;
		}

		currentIconButton = button;

		var containerNode = currentIconButton.closest('.ws_container');
		var menuItem = containerNode.data('menu_item');
		var cssClass = getFieldValue(menuItem, 'css_class', '');
		var iconUrl = getFieldValue(menuItem, 'icon_url', '', containerNode);

		var customImageOption = iconSelector.find('.ws_custom_image_icon').hide();

		//Highlight the currently selected icon.
		iconSelector.find('.ws_selected_icon').removeClass('ws_selected_icon');

		var expandSelector = false;
		var classMatches = cssClass.match(/\b(ame-)?menu-icon-([^\s]+)\b/);
		//Dashicons are set via the icon URL field, but they are actually CSS-based.
		var dashiconMatches = iconUrl && iconUrl.match('^\s*(dashicons-[a-z0-9\-]+)\s*$');

		if ( iconUrl && iconUrl !== 'none' && iconUrl !== 'div' && !dashiconMatches ) {
			var currentIcon = iconSelector.find('.ws_icon_option img[src="' + iconUrl + '"]').first().closest('.ws_icon_option');
			if ( currentIcon.length > 0 ) {
				currentIcon.addClass('ws_selected_icon').show();
			} else {
				//Display and highlight the custom image.
				customImageOption.find('img').prop('src', iconUrl);
				customImageOption.addClass('ws_selected_icon').show().data('icon-url', iconUrl);
			}
		} else if ( classMatches || dashiconMatches ) {
			//Highlight the icon that corresponds to the current CSS class or Dashicon name.
			var iconClass = dashiconMatches ? dashiconMatches[1] : ((classMatches[1] ? classMatches[1] : '') + 'icon-' + classMatches[2]);
			var selectedIcon = iconSelector.find('.' + iconClass).closest('.ws_icon_option').addClass('ws_selected_icon');
			//If the icon is one of those hidden by default, automatically expand the selector so it becomes visible.
			if (selectedIcon.hasClass('ws_icon_extra')) {
				expandSelector = true;
			}
		}

		expandSelector = expandSelector || (!!wsEditorData.showExtraIcons); //Second argument to toggleClass() must be a boolean, not just truthy/falsy.
		iconSelector.toggleClass('ws_with_more_icons', expandSelector);
		$('#ws_show_more_icons').val(expandSelector ? 'Less \u25B2' : 'More \u25BC');

		iconSelector.show();
		iconSelector.position({ //Requires jQuery UI.
			my: 'left top',
			at: 'left bottom',
			of: button
		});
	});

	//Alternatively, use the WordPress media uploader to select a custom icon.
	//This code is based on the header selection script in /wp-admin/js/custom-header.js.
	$('#ws_choose_icon_from_media').click(function(event) {
		event.preventDefault();
		var frame = null;

		//This option is not usable on the demo site since the filesystem is usually read-only.
		if (wsEditorData.isDemoMode) {
			alert('Sorry, image upload is disabled in demo mode!');
			return;
		}

        //If the media frame already exists, reopen it.
        if ( frame ) {
            frame.open();
            return;
        }

        //Create a custom media frame.
        frame = wp.media.frames.customAdminMenuIcon = wp.media({
            //Set the title of the modal.
            title: 'Choose a Custom Icon (20x20)',

            //Tell it to show only images.
            library: {
                type: 'image'
            },

            //Customize the submit button.
            button: {
                text: 'Set as icon', //Button text.
                close: true //Clicking the button closes the frame.
            }
        });

        //When an image is selected, set it as the menu icon.
        frame.on( 'select', function() {
            //Grab the selected attachment.
            var attachment = frame.state().get('selection').first();
            //TODO: Warn the user if the image exceeds 20x20 pixels.

	        //Set the menu icon to the attachment URL.
            if (currentIconButton) {
                var container = currentIconButton.closest('.ws_container');
                var item = container.data('menu_item');

                //Remove the existing icon class, if any.
                var cssClass = getFieldValue(item, 'css_class', '');
	            item.css_class = jsTrim( cssClass.replace(/\b(ame-)?menu-icon-[^\s]+\b/, '') );

	            //Set the new icon URL.
	            item.icon_url = attachment.attributes.url;

                updateItemEditor(container);
            }

            currentIconButton = null;
        });

		//If the user closes the frame by via Esc or the "X" button, clear up state.
		frame.on('escape', function(){
			currentIconButton = null;
		});

        frame.open();
		iconSelector.hide();
	});

	//Show/hide additional icons.
	$('#ws_show_more_icons').click(function() {
		iconSelector.toggleClass('ws_with_more_icons');
		wsEditorData.showExtraIcons = iconSelector.hasClass('ws_with_more_icons');
		$(this).val(wsEditorData.showExtraIcons ? 'Less \u25B2' : 'More \u25BC');

		//Remember the user's choice.
		$.cookie('ame-show-extra-icons', wsEditorData.showExtraIcons ? '1' : '0', {expires: 90});
	});

	//Hide the icon selector if the user clicks outside of it.
	//Exception: Clicks on "Select icon" buttons are handled above.
	$(document).on('mouseup', function(event) {
		if ( !iconSelector.is(':visible') ) {
			return;
		}

		if (
			!iconSelector.is(event.target)
			&& iconSelector.has(event.target).length === 0
			&& $(event.target).closest('.ws_select_icon').length === 0
		) {
			iconSelector.hide();
			currentIconButton = null;
		}
	});


	/*************************************************************************
	                        Embedded page selector
	 *************************************************************************/

	var pageSelector = $('#ws_embedded_page_selector'),
		pageListBox = pageSelector.find('#ws_current_site_pages'),
		currentPageSelectorButton = null, //The last page dropdown button that was clicked.
		isPageListPopulated = false,
		isPageRequestInProgress = false;

	pageSelector.tabs({
		heightStyle: 'auto',
		hide: false,
		show: false
	});
	//Hack. The selector needs to be hidden by default, but it can't start out as "display: none" because that makes
	//jQuery miscalculate tab heights. So we put it in a hidden container, then hide it on load and move it elsewhere.
	pageSelector.hide().appendTo(menuEditorNode);

	/**
	 * Update the page selector with the current menu item's settings.
	 */
	function updatePageSelector() {
		var menuItem, selectedPageId = 0, selectedBlogId = 1;
		if ( currentPageSelectorButton ) {
			menuItem = currentPageSelectorButton.closest('.ws_container').data('menu_item');
			selectedPageId = parseInt(getFieldValue(menuItem, 'embedded_page_id', 0), 10);
			selectedBlogId = parseInt(getFieldValue(menuItem, 'embedded_page_blog_id', 1), 10);
		}

		if (selectedPageId === 0) {
			pageListBox.val(null);
		} else {
			var optionValue = selectedBlogId + '_' + selectedPageId;
			pageListBox.val(optionValue);
			if ( pageListBox.val() !== optionValue ) {
				pageListBox.val('custom');
			}
		}

		pageSelector.find('#ws_embedded_page_id').val(selectedPageId);
		pageSelector.find('#ws_embedded_page_blog_id').val(selectedBlogId);
	}

	menuEditorNode.on('click', '.ws_embedded_page_selector_trigger', function(event) {
		var thisButton = $(this),
			thisInput = thisButton.closest('.ws_edit_field').find('input.ws_field_value:first');

		//Clicking the same button a second time hides the page selector.
		if (thisButton.is(currentPageSelectorButton) && pageSelector.is(':visible')) {
			pageSelector.hide();
			//noinspection JSUnusedAssignment
			currentPageSelectorButton = null;
			return;
		}

		currentPageSelectorButton = thisButton;
		pageSelector.show();
		pageSelector.position({
			my: 'left top',
			at: 'left bottom',
			of: thisInput
		});

		event.stopPropagation();

		if (!isPageListPopulated && !isPageRequestInProgress) {
			isPageRequestInProgress = true;

			var pageList = pageSelector.find('#ws_current_site_pages');
			pageList.prop('readonly', true);

			$.getJSON(
				wsEditorData.adminAjaxUrl,
				{
					'action' : 'ws_ame_get_pages',
					'_ajax_nonce' : wsEditorData.getPagesNonce
				},
				function(data){
					isPageRequestInProgress = false;
					pageList.prop('readonly', false);

					if (typeof data.error !== 'undefined'){
						alert(data.error);
						return;
					} else if ((typeof data !== 'object') || (typeof data.length === 'undefined')) {
						alert('Error: Could not retrieve a list of pages. Unexpected response from the server.');
						return;
					}

					//An alphabetised list is easier to scan visually.
					var pages = data.sort(function(a, b) {
						return a.post_title.localeCompare(b.post_title);
					});

					//Populate the select box.
					pageList.empty();
					$.each(pages, function(index, page) {
						pageList.append($('<option>', {
							val: page.blog_id + '_' + page.post_id,
							text: page.post_title
						}));
					});

					//Add a "custom" option. Select it when the current setting doesn't match any of the listed pages.
					pageList.prepend($('<option>', {
						val: 'custom',
						text: '< Custom >'
					}));

					updatePageSelector();
					isPageListPopulated = true;
				},
				'json'
			);

		}

		updatePageSelector();

		//Open the "Pages" tab by default, or the "Custom" tab if that's what's selected in the list box.
		//The updatePageSelector call above sets the pageListBox value.
		pageSelector.tabs('option', 'active', (pageListBox.val() === 'custom') ? 1 : 0);
	});

	//Hide the page selector if the user clicks outside of it and outside the current button.
	$(document).on('mouseup', function(event) {
		if ( !pageSelector.is(':visible') ) {
			return;
		}

		var target = $(event.target);
		var isOutsideSelector = target.closest(pageSelector).length === 0;
		var isOutsideButton = currentPageSelectorButton && (target.closest(currentPageSelectorButton).length === 0);

		if (isOutsideSelector && isOutsideButton) {
			pageSelector.hide();
			currentPageSelectorButton = null;
		}
	});

	function setEmbeddedPageForCurrentItem(newPageId, newBlogId, title) {
		if ( currentPageSelectorButton ) {
			var containerNode = currentPageSelectorButton.closest('.ws_container'),
				menuItem = containerNode.data('menu_item');

			menuItem.embedded_page_id = newPageId;
			menuItem.embedded_page_blog_id = newBlogId;

			if (typeof title === 'string') {
				//Store the page title for later. It will be displayed in the text box.
				AmePageTitles.add(newPageId, newBlogId, title);
			}

			updateItemEditor(containerNode);
		}
	}

	//When the user chooses a page from the list, update the menu item and hide the dropdown.
	pageListBox.on('change', function() {
		var selection = pageListBox.val();
		if (selection === 'custom') { // jshint ignore:line
			//Do nothing. Presumably, the user will now switch to the "Custom" tab and enter new settings.
			//If they don't do that and just close the dropdown, we keep the previous settings.
		} else if ( currentPageSelectorButton ) {
			//Set the new page and blog IDs. The expected value format is "blogid_postid".
			var parts = selection.split('_'),
				newBlogId = parseInt(parts[0], 10),
				newPageId = parseInt(parts[1], 10);

			pageSelector.hide();
			setEmbeddedPageForCurrentItem(newPageId, newBlogId, pageListBox.children(':selected').text());
		}
	});

	pageSelector.find('#ws_custom_embedded_page_tab form').on('submit', function(event) {
		event.preventDefault();

		var newPageId = parseInt(pageSelector.find('#ws_embedded_page_id').val(), 10),
			newBlogId = parseInt(pageSelector.find('#ws_embedded_page_blog_id').val(), 10);

		if (isNaN(newPageId) || (newPageId < 0)) {
			alert('Error: Invalid post ID');
		} else if (isNaN(newBlogId) || (newBlogId < 0)) {
			alert('Error: Invalid blog ID');
		} else if ( currentPageSelectorButton ) {
			pageSelector.hide();
			setEmbeddedPageForCurrentItem(newPageId, newBlogId);
		}
	});


	/*************************************************************************
	                             Color picker
	 *************************************************************************/

	var menuColorDialog = $('#ws-ame-menu-color-settings');
	if (menuColorDialog.length > 0) {
		menuColorDialog.dialog({
			autoOpen: false,
			closeText: ' ',
			draggable: false,
			modal: true,
			minHeight: 400,
			minWidth: 520
		});
	}

	var colorDialogState = {
		menuItem: null
	};

	var menuColorVariables = [
		'base-color',
		'text-color',
		'highlight-color',
		'icon-color',

		'menu-highlight-text',
		'menu-highlight-icon',
		'menu-highlight-background',

		'menu-current-text',
		'menu-current-icon',
		'menu-current-background',

		'menu-submenu-text',
		'menu-submenu-background',
		'menu-submenu-focus-text',
		'menu-submenu-current-text',

		'menu-bubble-text',
		'menu-bubble-background',
		'menu-bubble-current-text',
		'menu-bubble-current-background'
	];

	var colorPresetDropdown = $('#ame-menu-color-presets'),
		colorPresetDeleteButton = $("#ws-ame-delete-color-preset"),
		areColorChangesIgnored = false;

	//Show only the primary color settings by default.
	var showAdvancedColors = false;
	$('#ws-ame-show-advanced-colors').click(function() {
		showAdvancedColors = !showAdvancedColors;
		$('#ws-ame-menu-color-settings').find('.ame-advanced-menu-color').toggle(showAdvancedColors);
		$(this).text(showAdvancedColors ? 'Hide advanced options' : 'Show advanced options');
	});

	//"Edit.." color schemes.
	var colorPickersInitialized = false;
	menuEditorNode.on('click', '.ws_open_color_editor, .ws_color_scheme_display', function() {
		//Initializing the color pickers takes a while, so we only do it when needed instead of on document ready.
		if ( !colorPickersInitialized ) {
			menuColorDialog.find('.ame-color-picker').wpColorPicker({
				//Deselect the current preset when the user changes any of the color options.
				change: deselectPresetOnColorChange,
				clear: deselectPresetOnColorChange
			});
			colorPickersInitialized = true;
		}

		var containerNode = $(this).parents('.ws_container').first();
		var menuItem = containerNode.data('menu_item');

		colorDialogState.containerNode = containerNode;
		colorDialogState.menuItem = menuItem;

		var colors = getFieldValue(menuItem, 'colors', {}) || {};
		var customColorCount = displayColorSettingsInDialog(colors);
		if ( customColorCount > 0 ) {
			menuItem.colors = colors;
		} else {
			menuItem.colors = null;
		}

		//Populate presets and deselect the previously selected option.
		colorPresetDropdown.val('');
		if (!wasPresetDropdownPopulated) {
			populatePresetDropdown();
			wasPresetDropdownPopulated = true;
		}

		//Add menu title to the dialog caption.
		var title = getFieldValue(menuItem, 'menu_title', null);
		menuColorDialog.dialog(
			'option',
			'title',
			title ? ('Colors: ' + title.substring(0, 30)) : 'Colors'
		);
		menuColorDialog.dialog('open');
	});

	function getColorSettingsFromDialog() {
		var colors = {}, colorCount = 0;

		for (var i = 0; i < menuColorVariables.length; i++) {
			var name = menuColorVariables[i];
			var value = $('#ame-color-' + name).val();
			if (value) {
				colors[name] = value;
				colorCount++;
			}
		}

		if (colorCount > 0) {
			return colors;
		} else {
			return null;
		}
	}

	function displayColorSettingsInDialog(colors) {
		//noinspection JSUnusedAssignment
		areColorChangesIgnored = true;
		var customColorCount = 0;

		for (var i = 0; i < menuColorVariables.length; i++) {
			var name = menuColorVariables[i];
			var value = colors.hasOwnProperty(name) ? colors[name] : false;

			if ( value ) {
				$('#ame-color-' + name).wpColorPicker('color', value);
				customColorCount++;
			} else {
				$('#ame-color-' + name).closest('.wp-picker-container').find('.wp-picker-clear').click();
			}
		}

		areColorChangesIgnored = false;
		return customColorCount;
	}

	//The "Save Changes" button in the color dialog.
	$('#ws-ame-save-menu-colors').click(function() {
		menuColorDialog.dialog('close');
		if ( !colorDialogState.menuItem ) {
			return;
		}
		var menuItem = colorDialogState.menuItem;
		menuItem.colors = getColorSettingsFromDialog();
		updateItemEditor(colorDialogState.containerNode);

		colorDialogState.containerNode = null;
		colorDialogState.menuItem = null;
	});

	//The "Apply to All" button in the same dialog.
	$('#ws-ame-apply-colors-to-all').click(function() {
		if (!confirm('Apply these color settings to ALL top level menus?')) {
			return;
		}

		var newColors = getColorSettingsFromDialog();
		$('#ws_menu_box').find('.ws_menu').each(function() {
			var containerNode = $(this),
				menuItem = containerNode.data('menu_item');
			if (!menuItem.separator) {
				menuItem.colors = newColors;
				updateItemEditor(containerNode);
			}
		});

		menuColorDialog.dialog('close');
		colorDialogState.containerNode = null;
		colorDialogState.menuItem = null;
	});

	function addColorPreset(name, colors) {
		colorPresets[name] = colors;
		populatePresetDropdown();
		colorPresetDropdown.val(name);
		colorPresetDeleteButton.removeClass('hidden');
	}

	function deleteColorPreset(name) {
		delete colorPresets[name];
		populatePresetDropdown();
		colorPresetDropdown.val('');
		colorPresetDeleteButton.addClass('hidden');
	}

	function populatePresetDropdown() {
		var separator = colorPresetDropdown.find('#ame-color-preset-separator');

		//Delete the old options, but keep the "save preset" option and so on.
		colorPresetDropdown.find('option').not('.ame-meta-option').remove();

		//Sort presets alphabetically.
		var presetNames = $.map(colorPresets, function(unused, name) {
			return name;
		}).sort(function(a, b) {
			return a.localeCompare(b);
		});

		//Add them all to the dropdown.
		var newOptions = jQuery([]);
		$.each(presetNames, function(unused, name) {
			newOptions = newOptions.add($('<option>', {
				val: name,
				text: name
			}));
		});
		newOptions.insertBefore(separator);
	}

	function deselectPresetOnColorChange() {
		//Most jQuery widgets don't trigger change events when you update them via JavaScript,
		//but apparently wpColorPicker does. We want to ignore those superfluous events.
		if (!areColorChangesIgnored && (colorPresetDropdown.val() !== '')) {
			colorPresetDropdown.val('');
		}
	}

	colorPresetDropdown.change(function() {
		var dropdown = $(this),
			presetName = dropdown.val();

		colorPresetDeleteButton.toggleClass('hidden', (presetName === '') || (presetName === '[save_preset]'));

		if ((presetName === '[save_preset]') && menuColorDialog.dialog('isOpen')) {
			//Create a new preset.
			var colors = getColorSettingsFromDialog();
			if (colors === null) {
				dropdown.val('');
				alert('Error: No colors selected');
				return;
			}

			var newPresetName = window.prompt('New preset name:', '');
			if ((newPresetName === null) || (jsTrim(newPresetName) === '')) {
				dropdown.val('');
				return;
			}

			addColorPreset(newPresetName, colors);
		} else if (presetName !== '') {
			//Apply the selected preset.
			var preset = colorPresets[presetName];
			displayColorSettingsInDialog(preset);
		}
	});

	colorPresetDeleteButton.click(function() {
		var presetName = $('#ame-menu-color-presets').val();
		if ((presetName === '[save_preset]') || (presetName === '') || (presetName === null)) {
			return false;
		}
		if (!confirm('Are you sure you want to delete the preset "' + presetName + '"?')) {
			return false;
		}

		deleteColorPreset(presetName);
		return false;
	});

    /*************************************************************************
	                           Menu toolbar buttons
	 *************************************************************************/
    function getSelectedMenu() {
	    return $('#ws_menu_box').find('.ws_active');
    }

	//Show/Hide menu
	$('#ws_hide_menu').click(function (event) {
		event.preventDefault();

		//Get the selected menu
		var selection = getSelectedMenu();
		if (!selection.length) {
			return;
		}

		toggleItemHiddenFlag(selection);
	});

	//Hide a menu and deny access.
	menuEditorNode.find('.ws_toolbar').on('click', '.ws_hide_and_deny_button', function() {
		var $box = $(this).closest('.ws_main_container').find('.ws_box'),
			selection = $box.is('#ws_menu_box') ? getSelectedMenu() : getSelectedSubmenuItem();
		if (selection.length < 1) {
			return;
		}

		function objectFillKeys(keys, value) {
			var result = {};
			_.forEach(keys, function(key) {
				result[key] = value;
			});
			return result;
		}

		if (selectedActor === null) {
			//Hide from everyone except Super Admin and the current user.
			var menuItem = selection.data('menu_item'),
				validActors = _.keys(wsEditorData.actors),
				alwaysAllowedActors = _.intersection(
					['special:super_admin', 'user:' + wsEditorData.currentUserLogin],
					validActors
				),
				victims = _.difference(validActors, alwaysAllowedActors),
				shouldHide;

			//First, lets check who has access. Maybe this item is already hidden from the victims.
			shouldHide = _.some(victims, _.curry(actorCanAccessMenu, 2)(menuItem));

			var keepEnabled = objectFillKeys(alwaysAllowedActors, true),
				hideAllExceptAllowed = _.assign(objectFillKeys(victims, false), keepEnabled);

			walkMenuTree(selection, function(container, item) {
				var newAccess;
				if (shouldHide) {
					//Yay, hide it now!
					newAccess = hideAllExceptAllowed;
					//Only update had_access_before_hiding if this item isn't hidden yet or the field is missing.
					//We don't want to double-hide an item.
					var actorsWithAccess = _.filter(victims, function(actor) {
						return actorCanAccessMenu(item, actor);
					});
					if ((actorsWithAccess.length) > 0 || _.isEmpty(_.get(item, 'had_access_before_hiding', null))) {
						item.had_access_before_hiding = actorsWithAccess;
					}
				} else {
					//Give back access to the roles and users who previously had access.
					//Careful, don't give access to roles that no longer exist.
					var actorsWhoHadAccess = _.get(item, 'had_access_before_hiding', []) || [];
					actorsWhoHadAccess = _.intersection(actorsWhoHadAccess, validActors);

					newAccess = _.assign(objectFillKeys(actorsWhoHadAccess, true), keepEnabled);
					delete item.had_access_before_hiding;
				}

				setActorAccess(container, newAccess);
				updateItemEditor(container);
			});

		} else {
			//Just toggle the checkbox.
			selection.find('input.ws_actor_access_checkbox').click();
		}
	});

	//Delete error dialog. It shows up when the user tries to delete one of the default menus.
	var menuDeletionDialog = $('#ws-ame-menu-deletion-error').dialog({
		autoOpen: false,
		modal: true,
		closeText: ' ',
		title: 'Error',
		draggable: false
	});
	var menuDeletionCallback = function(hide) {
		menuDeletionDialog.dialog('close');
		var selection = menuDeletionDialog.data('selected_menu');

		function applyCallbackRecursively(containerNode, callback) {
			callback(containerNode.data('menu_item'));

			var subMenuId = containerNode.data('submenu_id');
			if (subMenuId && containerNode.hasClass('ws_menu')) {
				$('.ws_item', '#' + subMenuId).each(function() {
					var node = $(this);
					callback(node.data('menu_item'));
					updateItemEditor(node);
				});
			}

			updateItemEditor(containerNode);
		}

		function hideRecursively(containerNode, exceptActor) {
			applyCallbackRecursively(containerNode, function(menuItem) {
				denyAccessForAllExcept(menuItem, exceptActor);
			});
			updateParentAccessUi(containerNode);
		}

		if (hide === 'all') {
			if (wsEditorData.wsMenuEditorPro) {
				hideRecursively(selection, null);
			} else {
				//The free version doesn't have role permissions, so use the global "hidden" flag.
				applyCallbackRecursively(selection, function(menuItem) {
					menuItem.hidden = true;
				});
			}
		} else if (hide === 'except_current_user') {
			hideRecursively(selection, 'user:' + wsEditorData.currentUserLogin);
		} else if (hide === 'except_administrator' && !wsEditorData.wsMenuEditorPro) {
			//Set "required capability" to something only the Administrator role would have.
			var adminOnlyCap = 'manage_options';
			applyCallbackRecursively(selection, function(menuItem) {
				menuItem.extra_capability = adminOnlyCap;
			});
			alert('The "required capability" field was set to "' + adminOnlyCap + '".');
		}
	};

	//Callbacks for each of the dialog buttons.
	$('#ws_cancel_menu_deletion').click(function() {
		menuDeletionCallback(false);
	});
	$('#ws_hide_menu_from_everyone').click(function() {
		menuDeletionCallback('all');
	});
	$('#ws_hide_menu_except_current_user').click(function() {
		menuDeletionCallback('except_current_user');
	});
	$('#ws_hide_menu_except_administrator').click(function() {
		menuDeletionCallback('except_administrator');
	});

	/**
	 * Check if it's possible to delete a menu item.
	 *
	 * @param {jQuery} containerNode
	 * @returns {boolean}
	 */
	function canDeleteItem(containerNode) {
		if (!containerNode || (containerNode.length < 1)) {
			return false;
		}

		var menuItem = containerNode.data('menu_item');
		var isDefaultItem =
			( menuItem.template_id !== '')
			&& ( menuItem.template_id !== wsEditorData.unclickableTemplateId)
			&& ( menuItem.template_id !== wsEditorData.embeddedPageTemplateId)
			&& (!menuItem.separator);

		var otherCopiesExist = false;
		if (isDefaultItem) {
			//Check if there are any other menus with the same template ID.
			$('#ws_menu_editor').find('.ws_container').each(function() {
				var otherItem = $(this).data('menu_item');
				if ((menuItem !== otherItem) && (menuItem.template_id === otherItem.template_id)) {
					otherCopiesExist = true;
					return false;
				}
				return true;
			});
		}

		return (!isDefaultItem || otherCopiesExist);
	}

	/**
	 * Attempt to delete a menu item. Will check if the item can actually be deleted and ask the user for confirmation.
	 * UI callback.
	 *
	 * @param {jQuery} selection The selected menu item (DOM node).
	 */
	function tryDeleteItem(selection) {
		var menuItem = selection.data('menu_item');
		var shouldDelete = false;

		if (canDeleteItem(selection)) {
			//Custom and duplicate items can be deleted normally.
			shouldDelete = confirm('Delete this menu?');
		} else {
			//Non-custom items can not be deleted, but they can be hidden. Ask the user if they want to do that.
			menuDeletionDialog.find('#ws-ame-menu-type-desc').text(
				_.get(menuItem.defaults, 'is_plugin_page') ? 'an item added by another plugin' : 'a built-in menu item'
			);
			menuDeletionDialog.data('selected_menu', selection);

			//Different versions get slightly different options because only the Pro version has
			//role-specific permissions.
			$('#ws_hide_menu_except_current_user').toggleClass('hidden', !wsEditorData.wsMenuEditorPro);
			$('#ws_hide_menu_except_administrator').toggleClass('hidden', wsEditorData.wsMenuEditorPro);

			menuDeletionDialog.dialog('open');

			//Select "Cancel" as the default button.
			menuDeletionDialog.find('#ws_cancel_menu_deletion').focus();
		}

		if (shouldDelete) {
			//Delete this menu's submenu first, if any.
			var submenuId = selection.data('submenu_id');
			if (submenuId) {
				$('#' + submenuId).remove();
			}
			var parentSubmenu = selection.closest('.ws_submenu');

			//Delete the menu.
			selection.remove();

			if (parentSubmenu) {
				//Refresh permissions UI for this menu's parent (if any).
				updateParentAccessUi(parentSubmenu);
			}
		}
	}

	//Delete menu
	$('#ws_delete_menu').click(function (event) {
		event.preventDefault();

		//Get the selected menu
		var selection = getSelectedMenu();
		if (!selection.length) {
			return;
		}

		tryDeleteItem(selection);
	});

	//Copy menu
	$('#ws_copy_menu').click(function (event) {
		event.preventDefault();

		//Get the selected menu
		var selection = $('#ws_menu_box').find('.ws_active');
		if (!selection.length) {
			return;
		}

		//Store a copy of the current menu state in clipboard
		menu_in_clipboard = readItemState(selection);
	});

	//Cut menu
	$('#ws_cut_menu').click(function (event) {
		event.preventDefault();

		//Get the selected menu
		var selection = $('#ws_menu_box').find('.ws_active');
		if (!selection.length) {
			return;
		}

		//Store a copy of the current menu state in clipboard
		menu_in_clipboard = readItemState(selection);

		//Remove the original menu and submenu
		$('#'+selection.data('submenu_id')).remove();
		selection.remove();
	});

	//Paste menu
	function pasteMenu(menu, afterMenu) {
		//The user shouldn't need to worry about giving separators a unique filename.
		if (menu.separator) {
			menu.defaults.file = randomMenuId('separator_');
		}

		//If we're pasting from a sub-menu, we may need to fix some properties
		//that are blank for sub-menu items but required for top-level menus.
		if (getFieldValue(menu, 'css_class', '') == '') {
			menu.css_class = 'menu-top';
		}
		if (getFieldValue(menu, 'icon_url', '') == '') {
			menu.icon_url = 'dashicons-admin-generic';
		}
		if (getFieldValue(menu, 'hookname', '') == '') {
			menu.hookname = randomMenuId();
		}

		//Paste the menu after the specified one, or at the end of the list.
		if (afterMenu) {
			outputTopMenu(menu, afterMenu);
		} else {
			outputTopMenu(menu);
		}
	}

	$('#ws_paste_menu').click(function (event) {
		event.preventDefault();

		//Check if anything has been copied/cut
		if (!menu_in_clipboard) {
			return;
		}

		var menu = $.extend(true, {}, menu_in_clipboard);

		//Get the selected menu
		var selection = $('#ws_menu_box').find('.ws_active');
		//Paste the menu after the selection.
		pasteMenu(menu, (selection.length > 0) ? selection : null);
	});

	//New menu
	$('#ws_new_menu').click(function (event) {
		event.preventDefault();

		ws_paste_count++;

		//The new menu starts out rather bare
		var randomId = randomMenuId();
		var menu = $.extend({}, wsEditorData.blankMenuItem, {
			custom: true, //Important : flag the new menu as custom, or it won't show up after saving.
			template_id : '',
			menu_title : 'Custom Menu ' + ws_paste_count,
			file : randomId,
			items: [],
			defaults: $.extend({}, itemTemplates.getDefaults(''))
		});

		//Make it accessible only to the current actor if one is selected.
		if (selectedActor !== null) {
			denyAccessForAllExcept(menu, selectedActor);
		}

		//Insert the new menu
		var selection = $('#ws_menu_box').find('.ws_active');
		var result = outputTopMenu(menu, (selection.length > 0) ? selection : null);

		//The menus's editbox is always open
		result.menu.find('.ws_edit_link').click();
	});

	//New separator
	$('#ws_new_separator, #ws_new_submenu_separator').click(function (event) {
		event.preventDefault();

		ws_paste_count++;

		//The new menu starts out rather bare
		var randomId = randomMenuId('separator_');
		var menu = $.extend(true, {}, wsEditorData.blankMenuItem, {
			separator: true, //Flag as a separator
			custom: false,   //Separators don't need to flagged as custom to be retained.
			items: [],
			defaults: {
				separator: true,
				css_class : 'wp-menu-separator',
				access_level : 'read',
				file : randomId,
				hookname : randomId
			}
		});

		if ( $(this).attr('id').indexOf('submenu') === -1 ) {
			//Insert in the top-level menu.
			var selection = $('#ws_menu_box').find('.ws_active');
			outputTopMenu(menu, (selection.length > 0) ? selection : null);
		} else {
			//Insert in the currently visible submenu.
			pasteItem(menu);
		}
	});

	//Toggle all menus for the currently selected actor
	$('#ws_toggle_all_menus').click(function(event) {
		event.preventDefault();

		if ( selectedActor === null ) {
			alert("This button enables/disables all menus for the selected role. To use it, click a role and then click this button again.");
			return;
		}

		var topMenuNodes = $('.ws_menu', '#ws_menu_box');
		//Look at the first menu's permissions and set everything to the opposite.
		var allow = ! actorCanAccessMenu(topMenuNodes.eq(0).data('menu_item'), selectedActor);

		topMenuNodes.each(function() {
			var containerNode = $(this);
			setActorAccessForTreeAndUpdateUi(containerNode, selectedActor, allow);
		});
	});

	//Copy all menu permissions from one role to another.
	var copyPermissionsDialog = $('#ws-ame-copy-permissions-dialog').dialog({
		autoOpen: false,
		modal: true,
		closeText: ' ',
		draggable: false
	});

	var sourceActorList = $('#ame-copy-source-actor'), destinationActorList = $('#ame-copy-destination-actor');

	//The "Copy permissions" toolbar button.
	$('#ws_copy_role_permissions').click(function(event) {
		event.preventDefault();

		var previousSource = sourceActorList.val();

		//Populate source/destination lists.
		sourceActorList.find('option').not('[disabled]').remove();
		destinationActorList.find('option').not('[disabled]').remove();
		$.each(wsEditorData.actors, function(actor, name) {
			var option = $('<option>', {val: actor, text: name});
			sourceActorList.append(option);
			destinationActorList.append(option.clone());
		});

		//Pre-select the current actor as the destination.
		if (selectedActor !== null) {
			destinationActorList.val(selectedActor);
		}

		//Restore the previous source selection.
		if (previousSource) {
			sourceActorList.val(previousSource);
		}
		if (!sourceActorList.val()) {
			sourceActorList.find('option').first().prop('selected', true); //Fallback.
		}

		copyPermissionsDialog.dialog('open');
	});

	//Actually copy the permissions when the user click the confirmation button.
	var copyConfirmationButton = $('#ws-ame-confirm-copy-permissions');
	copyConfirmationButton.click(function() {
		var sourceActor = sourceActorList.val();
		var destinationActor = destinationActorList.val();

		if (sourceActor === null || destinationActor === null) {
			alert('Select a source and a destination first.');
			return;
		}

		//Iterate over all menu items and copy the permissions from one actor to the other.
		var allMenuNodes = $('.ws_menu', '#ws_menu_box').add('.ws_item', submenuBox);
		allMenuNodes.each(function() {
			var node = $(this);
			var menuItem = node.data('menu_item');

			//Only change permissions when they don't match. This ensures we won't unnecessarily overwrite default
			//permissions and bloat the configuration with extra grant_access entries.
			var sourceAccess      = actorCanAccessMenu(menuItem, sourceActor);
			var destinationAccess = actorCanAccessMenu(menuItem, destinationActor);
			if (sourceAccess !== destinationAccess) {
				setActorAccess(node, destinationActor, sourceAccess);
				//Note: In theory, we could also look at the default permissions for destinationActor and
				//revert to default instead of overwriting if that would make the two actors' permissions match.
			}
		});

		//If the user is currently looking at the destination actor, force the UI to refresh
		//so that they can see the new permissions.
		if (selectedActor === destinationActor) {
			//This is a bit of a hack, but right now there's no better way to refresh all items at once.
			setSelectedActor(null);
			setSelectedActor(destinationActor);
		}

		//All done.
		copyPermissionsDialog.dialog('close');
	});

	//Only enable the copy button when the user selects a valid source and destination.
	copyConfirmationButton.prop('disabled', true);
	sourceActorList.add(destinationActorList).click(function() {
		var sourceActor = sourceActorList.val();
		var destinationActor = destinationActorList.val();

		var validInputs = (sourceActor !== null) && (destinationActor !== null) && (sourceActor !== destinationActor);
		copyConfirmationButton.prop('disabled', !validInputs);
	});

	//Sort menus in ascending or descending order.
	menuEditorNode.find('.ws_toolbar').on('click', '.ws_sort_menus_button', function(event) {
		event.preventDefault();

		var button = $(this),
			menuBox = $(this).closest('.ws_main_container').find('.ws_box').first();

		if (menuBox.is('#ws_submenu_box')) {
			menuBox = menuBox.find('.ws_submenu:visible').first();
		}

		if (menuBox.length > 0) {
			sortMenuItems(menuBox, button.data('sort-direction') || 'asc');
		}
	});

	/**
	 * Sort menu items by title.
	 *
	 * @param $menuBox A DOM node that contains multiple menu items.
	 * @param {string} direction 'asc' or 'desc'
	 */
	function sortMenuItems($menuBox, direction) {
		var multiplier = (direction === 'desc') ? -1 : 1,
			items = $menuBox.find('.ws_container');

		//Separators don't have a title, but we don't want them to end up at the top of the list.
		//Instead, lets keep their position the same relative to the previous item.
		var prevItemTitle = '';
		items.each((function(){
			var item = $(this), sortValue;
			if (item.is('.ws_menu_separator')) {
				sortValue = prevItemTitle;
			} else {
				sortValue = jsTrim(item.find('.ws_item_title').text());
				prevItemTitle = sortValue;
			}
			item.data('ame-sort-value', sortValue);
		}));

		function compareMenus(a, b){
			var aTitle = jsTrim($(a).find('.ws_item_title').text()),
				bTitle = jsTrim($(b).find('.ws_item_title').text());

			aTitle = aTitle.toLowerCase();
			bTitle = bTitle.toLowerCase();

			if (aTitle > bTitle) {
				return multiplier;
			} else if (aTitle < bTitle) {
				return -multiplier;
			}
			return 0;
		}

		items.sort(compareMenus);
	}

	//Toggle the second row of toolbar buttons.
	$('#ws_toggle_toolbar').click(function() {
		var visible = menuEditorNode.find('.ws_second_toolbar_row').toggle().is(':visible');
		$.cookie('ame-show-second-toolbar', visible ? '1' : '0', {expires: 90});
	});


	/*************************************************************************
	                          Item toolbar buttons
	 *************************************************************************/
	function getSelectedSubmenuItem() {
		return $('#ws_submenu_box').find('.ws_submenu:visible .ws_active');
	}

	//Show/Hide item
	$('#ws_hide_item').click(function (event) {
		event.preventDefault();

		//Get the selected item
		var selection = getSelectedSubmenuItem();
		if (!selection.length) {
			return;
		}

		//Mark the item as hidden/visible
		toggleItemHiddenFlag(selection);
	});

	//Delete item
	$('#ws_delete_item').click(function (event) {
		event.preventDefault();

		var selection = getSelectedSubmenuItem();
		if (!selection.length) {
			return;
		}

		tryDeleteItem(selection);
	});

	//Copy item
	$('#ws_copy_item').click(function (event) {
		event.preventDefault();

		//Get the selected item
		var selection = getSelectedSubmenuItem();
		if (!selection.length) {
			return;
		}

		//Store a copy of item state in the clipboard
		menu_in_clipboard = readItemState(selection);
	});

	//Cut item
	$('#ws_cut_item').click(function (event) {
		event.preventDefault();

		//Get the selected item
		var selection = getSelectedSubmenuItem();
		if (!selection.length) {
			return;
		}

		//Store a copy of item state in the clipboard
		menu_in_clipboard = readItemState(selection);

		var submenu = selection.parent();
		//Remove the original item
		selection.remove();
		updateParentAccessUi(submenu);
	});

	//Paste item
	function pasteItem(item) {
		//We're pasting this item into a sub-menu, so it can't have a sub-menu of its own.
		//Instead, any sub-menu items belonging to this item will be pasted after the item.
		var newItems = [];
		for (var file in item.items) {
			if (item.items.hasOwnProperty(file)) {
				newItems.push(buildMenuItem(item.items[file], false));
			}
		}
		item.items = [];

		newItems.unshift(buildMenuItem(item, false));

		//Get the selected menu
		var visibleSubmenu = $('#ws_submenu_box').find('.ws_submenu:visible');
		var selection = visibleSubmenu.find('.ws_active');
		for(var i = 0; i < newItems.length; i++) {
			if (selection.length > 0) {
				//If an item is selected add the pasted items after it
				selection.after(newItems[i]);
			} else {
				//Otherwise add the pasted items at the end
				visibleSubmenu.append(newItems[i]);
			}

			updateItemEditor(newItems[i]);
			newItems[i].show();
		}

		updateParentAccessUi(visibleSubmenu);
	}

	$('#ws_paste_item').click(function (event) {
		event.preventDefault();

		//Check if anything has been copied/cut
		if (!menu_in_clipboard) {
			return;
		}

		//You can only add separators to submenus in the Pro version.
		if ( menu_in_clipboard.separator && !wsEditorData.wsMenuEditorPro ) {
			return;
		}

		//Paste it.
		var item = $.extend(true, {}, menu_in_clipboard);
		pasteItem(item);
	});

	//New item
	$('#ws_new_item').click(function (event) {
		event.preventDefault();

		if ($('.ws_submenu:visible').length < 1) {
			return; //Abort if no submenu visible
		}

		ws_paste_count++;

		var entry = $.extend({}, wsEditorData.blankMenuItem, {
			custom: true,
			template_id : '',
			menu_title : 'Custom Item ' + ws_paste_count,
			file : randomMenuId(),
			items: [],
			defaults: $.extend({}, itemTemplates.getDefaults(''))
		});

		//Make it accessible to only the currently selected actor.
		if (selectedActor !== null) {
			denyAccessForAllExcept(entry, selectedActor);
		}

		var menu = buildMenuItem(entry);

		//Insert the item into the currently open submenu.
		var visibleSubmenu = $('#ws_submenu_box').find('.ws_submenu:visible');
		var selection = visibleSubmenu.find('.ws_active');
		if (selection.length > 0) {
			selection.after(menu);
		} else {
			visibleSubmenu.append(menu);
		}
		updateItemEditor(menu);

		//The items's editbox is always open
		menu.find('.ws_edit_link').click();

		updateParentAccessUi(menu);
	});

	//==============================================
	//				Main buttons
	//==============================================

	//Save Changes - encode the current menu as JSON and save
	$('#ws_save_menu').click(function () {
		try {
			var tree = readMenuTreeState();
		} catch (error) {
			//Right now the only known error condition is duplicate top level URLs.
			if (error.hasOwnProperty('code') && (error.code === 'duplicate_top_level_url')) {
				var message = 'Error: Duplicate menu URLs. The following top level menus have the same URL:\n\n' ;
				for (var i = 0; i < error.duplicates.length; i++) {
					var containerNode = $(error.duplicates[i]);
					message += (i + 1) + '. ' + containerNode.find('.ws_item_title').first().text() + '\n';
				}
				message += '\nPlease change the URLs to be unique or delete the duplicates.';
				alert(message);
			} else {
				alert(error.message);
			}
			return;
		}

		function findItemByTemplateId(items, templateId) {
			var foundItem = null;

			$.each(items, function(index, item) {
				if (item.template_id === templateId) {
					foundItem = item;
					return false;
				}
				if (item.hasOwnProperty('items') && (item.items.length > 0)) {
					foundItem = findItemByTemplateId(item.items, templateId);
					if (foundItem !== null) {
						return false;
					}
				}
				return true;
			});

			return foundItem;
		}

		//Abort the save if it would make the editor inaccessible.
        if (wsEditorData.wsMenuEditorPro) {
            var myMenuItem = findItemByTemplateId(tree.tree, 'options-general.php>menu_editor');
            if (myMenuItem === null) { // jshint ignore:line
                //This is OK - the missing menu item will be re-inserted automatically.
            } else if (!actorCanAccessMenu(myMenuItem, 'user:' + wsEditorData.currentUserLogin)) {
                alert(
	                "Error: This configuration would make you unable to access the menu editor!\n\n" +
	                "Please click either your role name or \"Current user (" + wsEditorData.currentUserLogin + ")\" "+
	                "and enable the \"Menu Editor Pro\" menu item."
                );
                return;
            }
        }

		var data = encodeMenuAsJSON(tree);
		$('#ws_data').val(data);
		$('#ws_data_length').val(data.length);
		$('#ws_selected_actor').val(selectedActor === null ? '' : selectedActor);
		$('#ws_visible_users_json').val($.toJSON(wsEditorData.visibleUsers || []));
		$('#ws_main_form').submit();
	});

	//Load default menu - load the default WordPress menu
	$('#ws_load_menu').click(function () {
		if (confirm('Are you sure you want to load the default WordPress menu?')){
			loadMenuConfiguration(defaultMenu);
		}
	});

	//Reset menu - re-load the custom menu. Discards any changes made by user.
	$('#ws_reset_menu').click(function () {
		if (confirm('Undo all changes made in the current editing session?')){
			loadMenuConfiguration(customMenu);
		}
	});

	$('#ws_toggle_editor_layout').click(function () {
		var isCompactLayoutEnabled = menuEditorNode.toggleClass('ws_compact_layout').hasClass('ws_compact_layout');
		$.cookie('ame-compact-layout', isCompactLayoutEnabled ? '1' : '0', {expires: 90});

		var button = $(this);
		if (button.is('input')) {
			var checkMark = '\u2713';
			button.val(button.val().replace(checkMark, ''));
			if (isCompactLayoutEnabled) {
				button.val(checkMark + ' ' + button.val());
			}
		}
	});

	//Export menu - download the current menu as a file
	$('#export_dialog').dialog({
		autoOpen: false,
		closeText: ' ',
		modal: true,
		minHeight: 100
	});

	$('#ws_export_menu').click(function(){
		var button = $(this);
		button.prop('disabled', true);
		button.val('Exporting...');

		$('#export_complete_notice, #download_menu_button').hide();
		$('#export_progress_notice').show();
		var exportDialog = $('#export_dialog');
		exportDialog.dialog('open');

		//Encode the menu.
		try {
			var exportData = encodeMenuAsJSON();
		} catch (error) {
			exportDialog.dialog('close');
			alert(error.message);

			button.val('Export');
			button.prop('disabled', false);
			return;
		}

		//Store the menu for download.
		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'data' : exportData,
				'action' : 'export_custom_menu',
				'_ajax_nonce' : wsEditorData.exportMenuNonce
			},
			/**
			 * @param {Object} data
			 */
			function(data){
				button.val('Export');
				button.prop('disabled', false);

				if ( typeof data.error !== 'undefined' ){
					exportDialog.dialog('close');
					alert(data.error);
				}

				if ( _.has(data, 'download_url') ){
					//window.location = data.download_url;
					$('#download_menu_button').attr('href', _.get(data, 'download_url')).data('filesize', _.get(data, 'filesize'));
					$('#export_progress_notice').hide();
					$('#export_complete_notice, #download_menu_button').show();
				}
			},
			'json'
		);
	});

	$('#ws_cancel_export').click(function(){
		$('#export_dialog').dialog('close');
	});

	$('#download_menu_button').click(function(){
		$('#export_dialog').dialog('close');
	});

	//Import menu - upload an exported menu and show it in the editor
	$('#import_dialog').dialog({
		autoOpen: false,
		closeText: ' ',
		modal: true
	});

	$('#ws_cancel_import').click(function(){
		$('#import_dialog').dialog('close');
	});

	$('#ws_import_menu').click(function(){
		$('#import_progress_notice, #import_progress_notice2, #import_complete_notice, #ws_import_error').hide();
		$('#ws_import_panel').show();
		$('#import_menu_form').resetForm();
		//The "Upload" button is disabled until the user selects a file
		$('#ws_start_import').attr('disabled', 'disabled');

		var importDialog = $('#import_dialog');
		importDialog.find('.hide-when-uploading').show();
		importDialog.dialog('open');
	});

	$('#import_file_selector').change(function(){
		$('#ws_start_import').prop('disabled', ! $(this).val() );
	});

	//This function displays unhandled server side errors. In theory, our upload handler always returns a well-formed
	//response even if there's an error. In practice, stuff can go wrong in unexpected ways (e.g. plugin conflicts).
	function handleUnexpectedImportError(xhr, errorMessage) {
		//The server-side code didn't catch this error, so it's probably something serious
		//and retrying won't work.
		$('#import_menu_form').resetForm();
		$('#ws_import_panel').hide();

		//Display error information.
		$('#ws_import_error_message').text(errorMessage);
		$('#ws_import_error_http_code').text(xhr.status);
		$('#ws_import_error_response').text((xhr.responseText !== '') ? xhr.responseText : '[Empty response]');
		$('#ws_import_error').show();
	}

	//AJAXify the upload form
	$('#import_menu_form').ajaxForm({
		dataType : 'json',
		beforeSubmit: function(formData) {

			//Check if the user has selected a file
			for(var i = 0; i < formData.length; i++){
				if ( formData[i].name === 'menu' ){
					if ( (typeof formData[i].value === 'undefined') || !formData[i].value){
						alert('Select a file first!');
						return false;
					}
				}
			}

			$('#import_dialog').find('.hide-when-uploading').hide();
			$('#import_progress_notice').show();

			$('#ws_start_import').attr('disabled', 'disabled');
			return true;
		},
		success: function(data, status, xhr) {
			$('#import_progress_notice').hide();

			var importDialog = $('#import_dialog');
			if ( !importDialog.dialog('isOpen') ){
				//Whoops, the user closed the dialog while the upload was in progress.
				//Discard the response silently.
				return;
			}

			if ( data === null ) {
				handleUnexpectedImportError(xhr, 'Invalid response from server. Please check your PHP error log.');
				return;
			}

			if ( typeof data.error !== 'undefined' ){
				alert(data.error);
				//Let the user try again
				$('#import_menu_form').resetForm();
				importDialog.find('.hide-when-uploading').show();
			}

			if ( (typeof data.tree !== 'undefined') && data.tree ){
				//Whee, we got back a (seemingly) valid menu. A veritable miracle!
				//Lets load it into the editor.
				var progressNotice = $('#import_progress_notice2').show();
				loadMenuConfiguration(data);
				progressNotice.hide();
				//Display a success notice, then automatically close the window after a few moments
				$('#import_complete_notice').show();
				setTimeout((function(){
					//Close the import dialog
					$('#import_dialog').dialog('close');
				}), 500);
			}

		},
		error: function(xhr, status, errorMessage) {
			handleUnexpectedImportError(xhr, errorMessage);
		}
	});

	/*************************************************************************
	                 Drag & drop items between menu levels
	 *************************************************************************/

	if (wsEditorData.wsMenuEditorPro) {
		//Allow the user to drag sub-menu items to the top level.
		$('#ws_top_menu_dropzone').droppable({
			'hoverClass' : 'ws_dropzone_hover',
			'activeClass' : 'ws_dropzone_active',

			'accept' : (function(thing){
				return thing.hasClass('ws_item');
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				pasteMenu(droppedItemData);
				if ( !event.ctrlKey ) {
					ui.draggable.remove();
				}
			})
		});

		//...and to drag top level menus to a sub-menu.
		submenuBox.closest('.ws_main_container').droppable({
			'hoverClass' : 'ws_top_to_submenu_drop_hover',

			'accept' : (function(thing){
				var visibleSubmenu = $('#ws_submenu_box').find('.ws_submenu:visible');
				return (
					//Accept top-level menus
					thing.hasClass('ws_menu') &&

					//Prevent users from dropping a menu on its own sub-menu.
					(visibleSubmenu.attr('id') !== thing.data('submenu_id'))
				);
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				pasteItem(droppedItemData);
				if ( !event.ctrlKey ) {
					ui.draggable.remove();
				}
			})
		});
	}


	//Set up tooltips
	$('.ws_tooltip_trigger').qtip({
		style: {
			classes: 'qtip qtip-rounded ws_tooltip_node'
		},
		hide: {
			fixed: true,
			delay: 300
		}
	});

	//Set up the "additional permissions are available" tooltips.
	menuEditorNode.on('mouseenter click', '.ws_ext_permissions_indicator', function() {
		var $indicator = $(this);
		$indicator.qtip({
			overwrite: false,
			content: {
				text: function() {
					var indicator = $(this),
						extPermissions = indicator.data('ext_permissions'),
						text = 'Additional permission settings are available. Click "Edit..." to change them.',
						heading = '';

					if (extPermissions && extPermissions.hasOwnProperty('title')) {
						heading = extPermissions.title;
						if (extPermissions.hasOwnProperty('type')) {
							heading = _.capitalize(_.startCase(extPermissions.type).toLowerCase()) + ': ' + heading;
						}
						text = '<strong>' + heading + '</strong><br>' + text;
					}

					return text;
				}
			},
			show: {
				ready: true //Show immediately.
			},
			style: {
				classes: 'qtip qtip-rounded ws_tooltip_node'
			},
			hide: {
				fixed: true,
				delay: 300
			},
			position: {
				my: 'bottom center',
				at: 'top center'
			}
		});
	});

	//Flag closed hints as hidden by sending the appropriate AJAX request to the backend.
	$('.ws_hint_close').click(function() {
		var hint = $(this).parents('.ws_hint').first();
		hint.hide();
		wsEditorData.showHints[hint.attr('id')] = false;
		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'action' : 'ws_ame_hide_hint',
				'hint' : hint.attr('id')
			}
		);
	});


	/******************************************************************
	                           Actor views
	 ******************************************************************/

	var actorSelector = $('#ws_actor_selector');

	function rebuildActorIndex() {
		var actors = {};
		//Include all roles.
		_.forEach(wsEditorData.roles, function(role, id) {
			actors['role:' + id] = role.name;
		});
		//Include the Super Admin (multisite only).
		if (wsEditorData.users[wsEditorData.currentUserLogin].is_super_admin) {
			actors['special:super_admin'] = 'Super Admin';
		}
		//Include the current user.
		actors['user:' + wsEditorData.currentUserLogin] = 'Current user (' + wsEditorData.currentUserLogin + ')';

		//Include other visible users.
		_(_.get(wsEditorData, 'visibleUsers', []))
			.without(wsEditorData.currentUserLogin)
			.sortBy()
			.forEach(function(login) {
				var user = AmeCapabilityManager.getUser(login);
				actors['user:' + login] = user.display_name + ' (' + login + ')';
			})
			.value();

		//Keep the same object, but replace all keys/values.
		_.forEach(_.keys(wsEditorData.actors), function(oldActor) {
			delete wsEditorData.actors[oldActor];
		});
		_.assign(wsEditorData.actors, actors);
	}


	function populateActorSelector() {
		if (!wsEditorData.wsMenuEditorPro) {
			return;
		}

		rebuildActorIndex();

		//Build the list of available actors.
		actorSelector.empty();
		actorSelector.append('<li><a href="#" class="current ws_actor_option ws_no_actor" data-text="All">All</a></li>');

		for(var actor in wsEditorData.actors) {
			if (!wsEditorData.actors.hasOwnProperty(actor)) {
				continue;
			}
			actorSelector.append(
				$('<li></li>').append(
					$('<a></a>')
						.attr('href', '#' + actor)
						.attr('data-text', wsEditorData.actors[actor])
						.text(wsEditorData.actors[actor])
						.addClass('ws_actor_option')
				)
			);
		}

		var moreUsersText = 'Choose users\u2026';
		actorSelector.append(
			$('<li>').append(
				$('<a></a>')
					.attr('id', 'ws_show_more_users')
					.attr('href', '#more-users')
					.attr('data-text', moreUsersText)
					.text(moreUsersText)
			)
		);

		actorSelector.show();

		if (selectedActor && !wsEditorData.actors.hasOwnProperty(selectedActor)) {
			selectedActor = null;
		}
		setSelectedActor(selectedActor);
	}

	AmeEditorApi.populateActorSelector = populateActorSelector;

	if (wsEditorData.wsMenuEditorPro) {
		populateActorSelector();

		if (wsEditorData.hasOwnProperty('selectedActor') && wsEditorData.selectedActor) {
			setSelectedActor(wsEditorData.selectedActor);
		} else {
			setSelectedActor(null);
		}
	}

	actorSelector.on('click', 'li a.ws_actor_option', function(event) {
		var actor = $(this).attr('href').substring(1);
		if (actor === '') {
			actor = null;
		}

		setSelectedActor(actor);
		event.preventDefault();
	});

	actorSelector.on('click', '#ws_show_more_users', function(event) {
		event.preventDefault();
		AmeVisibleUserDialog.open({
			currentUserLogin : wsEditorData.currentUserLogin,
			users            : AmeCapabilityManager.getUsers(),
			visibleUsers     : _.get(wsEditorData, 'visibleUsers', []),

			save: function(userDetails, selectedUsers) {
				AmeCapabilityManager.addUsers(userDetails);
				wsEditorData.visibleUsers = selectedUsers;
				populateActorSelector();
			}
		});
	});

	//Finally, show the menu
	loadMenuConfiguration(customMenu);
  });

})(jQuery, wsAmeLodash);

//==============================================
//				Screen options
//==============================================

jQuery(function($){
	'use strict';

	var screenOptions = $('#ws-ame-screen-meta-contents');
	var hideSettingsCheckbox = screenOptions.find('#ws-hide-advanced-settings');
	var extraIconsCheckbox = screenOptions.find('#ws-show-extra-icons');

	hideSettingsCheckbox.prop('checked', wsEditorData.hideAdvancedSettings);
	extraIconsCheckbox.prop('checked', wsEditorData.showExtraIcons);

	//Update editor state when settings change
	$('#ws-hide-advanced-settings, #ws-show-extra-icons').click(function(){
		wsEditorData.hideAdvancedSettings = hideSettingsCheckbox.prop('checked');
		wsEditorData.showExtraIcons = extraIconsCheckbox.prop('checked');

		//Show/hide advanced settings dynamically as the user changes the setting.
		if ($(this).is(hideSettingsCheckbox)) {
			var menuEditorNode = $('#ws_menu_editor');
			if ( wsEditorData.hideAdvancedSettings ){
				menuEditorNode.find('div.ws_advanced').hide();
				menuEditorNode.find('a.ws_toggle_advanced_fields').text(wsEditorData.captionShowAdvanced).show();
			} else {
				menuEditorNode.find('div.ws_advanced').show();
				menuEditorNode.find('a.ws_toggle_advanced_fields').text(wsEditorData.captionHideAdvanced).hide();
			}
		}

		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'action' : 'ws_ame_save_screen_options',
				'hide_advanced_settings' : wsEditorData.hideAdvancedSettings ? 1 : 0,
				'show_extra_icons' : wsEditorData.showExtraIcons ? 1 : 0,
				'_ajax_nonce' : wsEditorData.hideAdvancedSettingsNonce
			}
		);

		//We also have a cookie for the current user.
		$.cookie('ame-show-extra-icons', wsEditorData.showExtraIcons ? '1' : '0', {expires: 90});
	});

	//Move our options into the screen meta panel
	$('#adv-settings').empty().append(screenOptions.show());
});
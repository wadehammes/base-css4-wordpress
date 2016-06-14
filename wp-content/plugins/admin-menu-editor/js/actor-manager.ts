/// <reference path="lodash-3.10.d.ts" />
/// <reference path="common.d.ts" />

declare var wsAmeActorData: any;
declare var wsAmeLodash: _.LoDashStatic;
declare var AmeActors: AmeActorManager;

interface CapabilityMap {
	[capabilityName: string] : boolean;
}

abstract class AmeBaseActor {
	public id: string;
	public displayName: string = '[Error: No displayName set]';
	public capabilities: CapabilityMap;

	groupActors: string[] = [];

	protected actorTypeSpecificity: Number = 0;

	constructor(id: string, displayName: string, capabilities: CapabilityMap) {
		this.id = id;
		this.displayName = displayName;
		this.capabilities = capabilities;
	}

	/**
	 * Get the capability setting directly from this actor, ignoring capabilities
	 * granted by roles, the Super Admin flag, or the grantedCapabilities feature.
	 *
	 * Returns NULL for capabilities that are neither explicitly granted nor denied.
	 *
	 * @param {string} capability
	 * @returns {boolean|null}
	 */
	hasOwnCap(capability: string): boolean {
		if (this.capabilities.hasOwnProperty(capability)) {
			return this.capabilities[capability];
		}
		return null;
	}

	static getActorSpecificity(actorId: string) {
		var actorType = actorId.substring(0, actorId.indexOf(':')),
			specificity = 0;
		switch (actorType) {
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
	}
}

class AmeRole extends AmeBaseActor {
	name: string;
	protected actorTypeSpecificity = 1;

	constructor(roleId: string, displayName: string, capabilities: CapabilityMap) {
		super('role:' + roleId, displayName, capabilities);
		this.name = roleId;
	}


	hasOwnCap(capability: string): boolean {
		//In WordPress, a role name is also a capability name. Users that have the role "foo" always
		//have the "foo" capability. It's debatable whether the role itself actually has that capability
		//(WP_Role says no), but it's convenient to treat it that way.
		if (capability === this.name) {
			return true;
		}
		return super.hasOwnCap(capability);
	}
}

class AmeUser extends AmeBaseActor {
	userLogin: string;
	roles: string[];
	isSuperAdmin: boolean = false;
	groupActors: string[];

	protected actorTypeSpecificity = 10;

	constructor(
		userLogin: string,
		displayName: string,
		capabilities: CapabilityMap,
		roles: string[],
		isSuperAdmin: boolean = false
	) {
		super('user:' + userLogin, displayName, capabilities);

		this.userLogin = userLogin;
		this.roles = roles;
		this.isSuperAdmin = isSuperAdmin;

		if (this.isSuperAdmin) {
			this.groupActors.push(AmeSuperAdmin.permanentActorId);
		}
		for (var i = 0; i < this.roles.length; i++) {
			this.groupActors.push('role:' + this.roles[i]);
		}
	}
}

class AmeSuperAdmin extends AmeBaseActor {
	static permanentActorId = 'special:super_admin';
	protected actorTypeSpecificity = 2;

	constructor() {
		super(AmeSuperAdmin.permanentActorId, 'Super Admin', {});
	}

	hasOwnCap(capability: string): boolean {
		//The Super Admin has all possible capabilities except the special "do_not_allow" flag.
		return (capability !== 'do_not_allow');
	}
}

interface AmeGrantedCapabilityMap {
	[actorId: string]: {
		[capability: string] : any
	}
}

class AmeActorManager {
	private static _ = wsAmeLodash;

	private roles: {[roleId: string] : AmeRole} = {};
	private users: {[userLogin: string] : AmeUser} = {};
	private grantedCapabilities: AmeGrantedCapabilityMap = {};

	private isMultisite: boolean = false;
	private superAdmin: AmeSuperAdmin;

	constructor(roles, users, isMultisite: boolean = false) {
		this.isMultisite = !!isMultisite;

		AmeActorManager._.forEach(roles, (roleDetails, id) => {
			var role = new AmeRole(id, roleDetails.name, roleDetails.capabilities);
			this.roles[role.name] = role;
		});

		AmeActorManager._.forEach(users, (userDetails) => {
			var user = new AmeUser(
				userDetails.user_login,
				userDetails.display_name,
				userDetails.capabilities,
				userDetails.roles,
				userDetails.is_super_admin
			);
			this.users[user.userLogin] = user;
		});

		if (this.isMultisite) {
			this.superAdmin = new AmeSuperAdmin();
		}
	}

	actorCanAccess(
		actorId: string,
		grantAccess: {[actorId: string] : boolean},
		defaultCapability: string = null
	): boolean {
		if (grantAccess.hasOwnProperty(actorId)) {
			return grantAccess[actorId];
		}
		if (defaultCapability !== null) {
			return this.hasCap(actorId, defaultCapability, grantAccess);
		}
		return true;
	}

	getActor(actorId): AmeBaseActor {
		if (actorId === AmeSuperAdmin.permanentActorId) {
			return this.superAdmin;
		}

		var separator = actorId.indexOf(':'),
			actorType = actorId.substring(0, separator),
			actorKey = actorId.substring(separator + 1);

		if (actorType === 'role') {
			return this.roles.hasOwnProperty(actorKey) ? this.roles[actorKey] : null;
		} else if (actorType === 'user') {
			return this.users.hasOwnProperty(actorKey) ? this.users[actorKey] : null;
		}

		throw {
			name: 'InvalidActorException',
			message: "There is no actor with that ID, or the ID is invalid.",
			value: actorId
		};
	}

	actorExists(actorId: string): boolean {
		try {
			return (this.getActor(actorId) !== null);
		} catch (exception) {
			if (exception.hasOwnProperty('name') && (exception.name === 'InvalidActorException')) {
				return false;
			} else {
				throw exception;
			}
		}
	}

	hasCap(actorId, capability, context?: {[actor: string] : any}): boolean {
		context = context || {};
		return this.actorHasCap(actorId, capability, [context, this.grantedCapabilities]);
	}

	hasCapByDefault(actorId, capability) {
		return this.actorHasCap(actorId, capability);
	}

	private actorHasCap(actorId: string, capability: string, contextList?: Array<Object>): boolean {
		//It's like the chain-of-responsibility pattern.

		//Everybody has the "exist" cap and it can't be removed or overridden by plugins.
		if (capability === 'exist') {
			return true;
		}

		capability = AmeActorManager.mapMetaCap(capability);

		//Step #1: Check temporary context - unsaved caps, etc. Optional.
		//Step #2: Check granted capabilities. Default on, but can be skipped.
		if (contextList) {
			//Check for explicit settings first.
			var result = null, actorValue, len = contextList.length;
			for (var i = 0; i < len; i++) {
				if (contextList[i].hasOwnProperty(actorId)) {
					actorValue = contextList[i][actorId];
					if (typeof actorValue === 'boolean') {
						//Context: grant_access[actorId] = boolean. Necessary because enabling a menu item for a role
						//should also enable it for all users who have that role (unless explicitly disabled for a user).
						return actorValue;
					} else if (actorValue.hasOwnProperty(capability)) {
						//Context: grantedCapabilities[actor][capability] = boolean|[boolean, ...]
						result = actorValue[capability];
						return (typeof result === 'boolean') ? result : result[0];
					}
				}
			}
		}

		//Step #3: Check owned/default capabilities. Always checked.
		var actor = this.getActor(actorId),
			hasOwnCap = actor.hasOwnCap(capability);
		if (hasOwnCap !== null) {
			return hasOwnCap;
		}

		//Step #4: Users can get a capability through their roles or the "super admin" flag.
		//Only users can have inherited capabilities, so if this actor is not a user, we're done.
		if (actor instanceof AmeUser) {
			//Note that Super Admin has priority. If the user is a super admin, their roles are ignored.
			if (actor.isSuperAdmin) {
				return this.actorHasCap('special:super_admin', capability, contextList);
			}

			//Check if any of the user's roles have the capability.
			result = false;
			for (var index = 0; index < actor.roles.length; index++) {
				result = result || this.actorHasCap('role:' + actor.roles[index], capability, contextList);
			}
			return result;
		}

		return false;
	}

	private static mapMetaCap(capability: string): string {
		if (capability === 'customize') {
			return 'edit_theme_options';
		} else if (capability === 'delete_site') {
			return 'manage_options';
		}
		return capability;
	}

	/* -------------------------------
	 * Roles
	 * ------------------------------- */

	getRoles() {
		return this.roles;
	}

	roleExists(roleId: string): boolean {
		return this.roles.hasOwnProperty(roleId);
	};

	getSuperAdmin() : AmeSuperAdmin {
		return this.superAdmin;
	}

	/* -------------------------------
	 * Users
	 * ------------------------------- */

	getUsers() {
		return this.users;
	}

	getUser(login: string) {
		return this.users.hasOwnProperty(login) ? this.users[login] : null;
	}

	addUsers(newUsers: AmeUser[]) {
		AmeActorManager._.forEach(newUsers, (user) => {
			this.users[user.userLogin] = user;
		});
	}

	getGroupActorsFor(userLogin: string) {
		return this.users[userLogin].groupActors;
	}

	/* -------------------------------
	 * Granted capability manipulation
	 * ------------------------------- */

	setGrantedCapabilities(newGrants) {
		this.grantedCapabilities = AmeActorManager._.cloneDeep(newGrants);
	}

	getGrantedCapabilities(): AmeGrantedCapabilityMap {
		return this.grantedCapabilities;
	}

	/**
	 * Grant or deny a capability to an actor.
	 */
	setCap(actor: string, capability: string, hasCap: boolean, sourceType?, sourceName?) {
		AmeActorManager.setCapInContext(this.grantedCapabilities, actor, capability, hasCap, sourceType, sourceName);
	}

	static setCapInContext(
		context: AmeGrantedCapabilityMap,
		actor: string,
		capability: string,
		hasCap: boolean,
		sourceType?: string,
		sourceName?: string
	) {
		capability = AmeActorManager.mapMetaCap(capability);

		var grant = sourceType ? [hasCap, sourceType, sourceName || null] : hasCap;
		AmeActorManager._.set(context, [actor, capability], grant);
	}

	resetCap(actor: string, capability: string) {
		AmeActorManager.resetCapInContext(this.grantedCapabilities, actor, capability);
	}

	static resetCapInContext(context: AmeGrantedCapabilityMap, actor: string, capability: string) {
		capability = AmeActorManager.mapMetaCap(capability);

		if (AmeActorManager._.has(context, [actor, capability])) {
			delete context[actor][capability];
		}
	}

	/**
	 * Remove redundant granted capabilities.
	 *
	 * For example, if user "jane" has been granted the "edit_posts" capability both directly and via the Editor role,
	 * the direct grant is redundant. We can remove it. Jane will still have "edit_posts" because she's an editor.
	 */
	pruneGrantedUserCapabilities(): AmeGrantedCapabilityMap {
		var _ = AmeActorManager._,
			pruned = _.cloneDeep(this.grantedCapabilities),
			context = [pruned];

		var actorKeys = _(pruned).keys().filter((actorId) => {
			//Skip users that are not loaded.
			var actor = this.getActor(actorId);
			if (actor === null) {
				return false;
			}
			return (actor instanceof AmeUser);
		}).value();

		_.forEach(actorKeys, (actor) => {
			_.forEach(_.keys(pruned[actor]), (capability) => {
				var grant = pruned[actor][capability];
				delete pruned[actor][capability];

				var hasCap = _.isArray(grant) ? grant[0] : grant,
					hasCapWhenPruned = this.actorHasCap(actor, capability, context);

				if (hasCap !== hasCapWhenPruned) {
					pruned[actor][capability] = grant; //Restore.
				}
			});
		});

		this.setGrantedCapabilities(pruned);
		return pruned;
	};


	/**
	 * Compare the specificity of two actors.
	 *
	 * Returns 1 if the first actor is more specific than the second, 0 if they're both
	 * equally specific, and -1 if the second actor is more specific.
	 *
	 * @return {Number}
	 */
	static compareActorSpecificity(actor1: string, actor2: string): Number {
		var delta = AmeBaseActor.getActorSpecificity(actor1) - AmeBaseActor.getActorSpecificity(actor2);
		if (delta !== 0) {
			delta = (delta > 0) ? 1 : -1;
		}
		return delta;
	};
}

if (typeof wsAmeActorData !== 'undefined') {
	AmeActors = new AmeActorManager(
		wsAmeActorData.roles,
		wsAmeActorData.users,
		wsAmeActorData.isMultisite
	);
}

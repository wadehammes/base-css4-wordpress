/// <reference path="lodash-3.10.d.ts" />
/// <reference path="common.d.ts" />
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var AmeBaseActor = (function () {
    function AmeBaseActor(id, displayName, capabilities) {
        this.displayName = '[Error: No displayName set]';
        this.groupActors = [];
        this.actorTypeSpecificity = 0;
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
    AmeBaseActor.prototype.hasOwnCap = function (capability) {
        if (this.capabilities.hasOwnProperty(capability)) {
            return this.capabilities[capability];
        }
        return null;
    };
    AmeBaseActor.getActorSpecificity = function (actorId) {
        var actorType = actorId.substring(0, actorId.indexOf(':')), specificity = 0;
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
    };
    return AmeBaseActor;
}());
var AmeRole = (function (_super) {
    __extends(AmeRole, _super);
    function AmeRole(roleId, displayName, capabilities) {
        _super.call(this, 'role:' + roleId, displayName, capabilities);
        this.actorTypeSpecificity = 1;
        this.name = roleId;
    }
    AmeRole.prototype.hasOwnCap = function (capability) {
        //In WordPress, a role name is also a capability name. Users that have the role "foo" always
        //have the "foo" capability. It's debatable whether the role itself actually has that capability
        //(WP_Role says no), but it's convenient to treat it that way.
        if (capability === this.name) {
            return true;
        }
        return _super.prototype.hasOwnCap.call(this, capability);
    };
    return AmeRole;
}(AmeBaseActor));
var AmeUser = (function (_super) {
    __extends(AmeUser, _super);
    function AmeUser(userLogin, displayName, capabilities, roles, isSuperAdmin) {
        if (isSuperAdmin === void 0) { isSuperAdmin = false; }
        _super.call(this, 'user:' + userLogin, displayName, capabilities);
        this.isSuperAdmin = false;
        this.actorTypeSpecificity = 10;
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
    return AmeUser;
}(AmeBaseActor));
var AmeSuperAdmin = (function (_super) {
    __extends(AmeSuperAdmin, _super);
    function AmeSuperAdmin() {
        _super.call(this, AmeSuperAdmin.permanentActorId, 'Super Admin', {});
        this.actorTypeSpecificity = 2;
    }
    AmeSuperAdmin.prototype.hasOwnCap = function (capability) {
        //The Super Admin has all possible capabilities except the special "do_not_allow" flag.
        return (capability !== 'do_not_allow');
    };
    AmeSuperAdmin.permanentActorId = 'special:super_admin';
    return AmeSuperAdmin;
}(AmeBaseActor));
var AmeActorManager = (function () {
    function AmeActorManager(roles, users, isMultisite) {
        var _this = this;
        if (isMultisite === void 0) { isMultisite = false; }
        this.roles = {};
        this.users = {};
        this.grantedCapabilities = {};
        this.isMultisite = false;
        this.isMultisite = !!isMultisite;
        AmeActorManager._.forEach(roles, function (roleDetails, id) {
            var role = new AmeRole(id, roleDetails.name, roleDetails.capabilities);
            _this.roles[role.name] = role;
        });
        AmeActorManager._.forEach(users, function (userDetails) {
            var user = new AmeUser(userDetails.user_login, userDetails.display_name, userDetails.capabilities, userDetails.roles, userDetails.is_super_admin);
            _this.users[user.userLogin] = user;
        });
        if (this.isMultisite) {
            this.superAdmin = new AmeSuperAdmin();
        }
    }
    AmeActorManager.prototype.actorCanAccess = function (actorId, grantAccess, defaultCapability) {
        if (defaultCapability === void 0) { defaultCapability = null; }
        if (grantAccess.hasOwnProperty(actorId)) {
            return grantAccess[actorId];
        }
        if (defaultCapability !== null) {
            return this.hasCap(actorId, defaultCapability, grantAccess);
        }
        return true;
    };
    AmeActorManager.prototype.getActor = function (actorId) {
        if (actorId === AmeSuperAdmin.permanentActorId) {
            return this.superAdmin;
        }
        var separator = actorId.indexOf(':'), actorType = actorId.substring(0, separator), actorKey = actorId.substring(separator + 1);
        if (actorType === 'role') {
            return this.roles.hasOwnProperty(actorKey) ? this.roles[actorKey] : null;
        }
        else if (actorType === 'user') {
            return this.users.hasOwnProperty(actorKey) ? this.users[actorKey] : null;
        }
        throw {
            name: 'InvalidActorException',
            message: "There is no actor with that ID, or the ID is invalid.",
            value: actorId
        };
    };
    AmeActorManager.prototype.actorExists = function (actorId) {
        try {
            return (this.getActor(actorId) !== null);
        }
        catch (exception) {
            if (exception.hasOwnProperty('name') && (exception.name === 'InvalidActorException')) {
                return false;
            }
            else {
                throw exception;
            }
        }
    };
    AmeActorManager.prototype.hasCap = function (actorId, capability, context) {
        context = context || {};
        return this.actorHasCap(actorId, capability, [context, this.grantedCapabilities]);
    };
    AmeActorManager.prototype.hasCapByDefault = function (actorId, capability) {
        return this.actorHasCap(actorId, capability);
    };
    AmeActorManager.prototype.actorHasCap = function (actorId, capability, contextList) {
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
                    }
                    else if (actorValue.hasOwnProperty(capability)) {
                        //Context: grantedCapabilities[actor][capability] = boolean|[boolean, ...]
                        result = actorValue[capability];
                        return (typeof result === 'boolean') ? result : result[0];
                    }
                }
            }
        }
        //Step #3: Check owned/default capabilities. Always checked.
        var actor = this.getActor(actorId), hasOwnCap = actor.hasOwnCap(capability);
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
    };
    AmeActorManager.mapMetaCap = function (capability) {
        if (capability === 'customize') {
            return 'edit_theme_options';
        }
        else if (capability === 'delete_site') {
            return 'manage_options';
        }
        return capability;
    };
    /* -------------------------------
     * Roles
     * ------------------------------- */
    AmeActorManager.prototype.getRoles = function () {
        return this.roles;
    };
    AmeActorManager.prototype.roleExists = function (roleId) {
        return this.roles.hasOwnProperty(roleId);
    };
    ;
    AmeActorManager.prototype.getSuperAdmin = function () {
        return this.superAdmin;
    };
    /* -------------------------------
     * Users
     * ------------------------------- */
    AmeActorManager.prototype.getUsers = function () {
        return this.users;
    };
    AmeActorManager.prototype.getUser = function (login) {
        return this.users.hasOwnProperty(login) ? this.users[login] : null;
    };
    AmeActorManager.prototype.addUsers = function (newUsers) {
        var _this = this;
        AmeActorManager._.forEach(newUsers, function (user) {
            _this.users[user.userLogin] = user;
        });
    };
    AmeActorManager.prototype.getGroupActorsFor = function (userLogin) {
        return this.users[userLogin].groupActors;
    };
    /* -------------------------------
     * Granted capability manipulation
     * ------------------------------- */
    AmeActorManager.prototype.setGrantedCapabilities = function (newGrants) {
        this.grantedCapabilities = AmeActorManager._.cloneDeep(newGrants);
    };
    AmeActorManager.prototype.getGrantedCapabilities = function () {
        return this.grantedCapabilities;
    };
    /**
     * Grant or deny a capability to an actor.
     */
    AmeActorManager.prototype.setCap = function (actor, capability, hasCap, sourceType, sourceName) {
        AmeActorManager.setCapInContext(this.grantedCapabilities, actor, capability, hasCap, sourceType, sourceName);
    };
    AmeActorManager.setCapInContext = function (context, actor, capability, hasCap, sourceType, sourceName) {
        capability = AmeActorManager.mapMetaCap(capability);
        var grant = sourceType ? [hasCap, sourceType, sourceName || null] : hasCap;
        AmeActorManager._.set(context, [actor, capability], grant);
    };
    AmeActorManager.prototype.resetCap = function (actor, capability) {
        AmeActorManager.resetCapInContext(this.grantedCapabilities, actor, capability);
    };
    AmeActorManager.resetCapInContext = function (context, actor, capability) {
        capability = AmeActorManager.mapMetaCap(capability);
        if (AmeActorManager._.has(context, [actor, capability])) {
            delete context[actor][capability];
        }
    };
    /**
     * Remove redundant granted capabilities.
     *
     * For example, if user "jane" has been granted the "edit_posts" capability both directly and via the Editor role,
     * the direct grant is redundant. We can remove it. Jane will still have "edit_posts" because she's an editor.
     */
    AmeActorManager.prototype.pruneGrantedUserCapabilities = function () {
        var _this = this;
        var _ = AmeActorManager._, pruned = _.cloneDeep(this.grantedCapabilities), context = [pruned];
        var actorKeys = _(pruned).keys().filter(function (actorId) {
            //Skip users that are not loaded.
            var actor = _this.getActor(actorId);
            if (actor === null) {
                return false;
            }
            return (actor instanceof AmeUser);
        }).value();
        _.forEach(actorKeys, function (actor) {
            _.forEach(_.keys(pruned[actor]), function (capability) {
                var grant = pruned[actor][capability];
                delete pruned[actor][capability];
                var hasCap = _.isArray(grant) ? grant[0] : grant, hasCapWhenPruned = _this.actorHasCap(actor, capability, context);
                if (hasCap !== hasCapWhenPruned) {
                    pruned[actor][capability] = grant; //Restore.
                }
            });
        });
        this.setGrantedCapabilities(pruned);
        return pruned;
    };
    ;
    /**
     * Compare the specificity of two actors.
     *
     * Returns 1 if the first actor is more specific than the second, 0 if they're both
     * equally specific, and -1 if the second actor is more specific.
     *
     * @return {Number}
     */
    AmeActorManager.compareActorSpecificity = function (actor1, actor2) {
        var delta = AmeBaseActor.getActorSpecificity(actor1) - AmeBaseActor.getActorSpecificity(actor2);
        if (delta !== 0) {
            delta = (delta > 0) ? 1 : -1;
        }
        return delta;
    };
    ;
    AmeActorManager._ = wsAmeLodash;
    return AmeActorManager;
}());
if (typeof wsAmeActorData !== 'undefined') {
    AmeActors = new AmeActorManager(wsAmeActorData.roles, wsAmeActorData.users, wsAmeActorData.isMultisite);
}
//# sourceMappingURL=actor-manager.js.map
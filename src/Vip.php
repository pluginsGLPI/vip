<?php

/**
 * -------------------------------------------------------------------------
 * vip plugin for GLPI
 * Copyright (C) 2022-2026 by the vip Development Team.
 *
 * https://github.com/pluginsGLPI/vip
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of vip.
 *
 * vip is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * vip is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vip. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Vip;

use Auth;
use AuthLDAP;
use CommonDBTM;
use Group_User;
use User;

/**
 * Class Vip
 */
class Vip extends CommonDBTM
{
    public static $rightname = 'plugin_vip';

    /**
     * @param int $nb
     *
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return __('VIP', 'vip');
    }

    public static function getIcon()
    {
        return "ti ti-vip";
    }

    public static function afterAdd(User $user)
    {
        self::applyRules($user);
    }

    public static function afterUpdate(User $user)
    {
        // The hook fires on every update of a user (last login, preferences, tokens...): only
        // the fields that change the directory entry the rules read are worth an LDAP bind.
        if (!array_intersect(['user_dn', 'authtype', 'auths_id', 'entities_id'], $user->updates)) {
            return;
        }

        self::applyRules($user);
    }

    /**
     * Run the VIP rules against the LDAP entry of a user, and add the user to the VIP group
     * the matching rule names.
     */
    private static function applyRules(User $user): void
    {
        global $DB;

        if (!isset($user->fields["authtype"])
            || !(($user->fields["authtype"] == Auth::LDAP)
                || Auth::isAlternateAuth($user->fields['authtype']))) {
            return;
        }

        // No active VIP rule: skip the LDAP bind altogether. The COUNT request already returns
        // the number of rules: count() on that integer threw a TypeError (ldap:synchronize_users).
        if ((int) ($DB->request([
            'COUNT' => 'cpt',
            'FROM'  => 'glpi_rules',
            'WHERE' => ['sub_type' => RuleVip::class, 'is_active' => 1],
        ])->current()['cpt'] ?? 0) === 0) {
            return;
        }

        $config_ldap = new AuthLDAP();
        if (!$config_ldap->getFromDB($user->fields['auths_id'])) {
            return;
        }
        $ds = $config_ldap->connect();
        if (!$ds) {
            return;
        }
        $info = AuthLdap::getUserByDn($ds, $user->fields['user_dn'], []);
        if (!is_array($info)) {
            return;
        }

        $input = [];
        foreach ((new RuleVip())->getCriterias() as $criteria) {
            if (isset($criteria['field'], $info[$criteria['field']][0])) {
                $input[$criteria['field']] = $info[$criteria['field']][0];
            }
        }
        if (isset($info["dn"])) {
            $input["dn"] = $info["dn"];
        }

        $ruleCollection = new RuleVipCollection($user->fields['entities_id']);
        $fields         = $ruleCollection->processAllRules($input, [], []);

        $groups_id = (int) ($fields['groups_id'] ?? 0);
        if ($groups_id <= 0) {
            return;
        }

        // The group comes from a rule action, a value stored by whoever manages the VIP rules:
        // the add below runs without any right check, so it must only ever grant a VIP group.
        // A rule naming any other group would otherwise hand out membership of any group of
        // any entity to the users it matches.
        if (!Group::isVipGroup($groups_id)) {
            return;
        }

        $groupuser = new Group_User();
        if (!$groupuser->find(['users_id' => $user->getID(), 'groups_id' => $groups_id])) {
            $groupuser->add(['users_id' => $user->getID(), 'groups_id' => $groups_id]);
        }
    }
}

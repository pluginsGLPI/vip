<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 vip plugin for GLPI
 Copyright (C) 2016-2022 by the vip Development Team.

 https://github.com/InfotelGLPI/vip
 -------------------------------------------------------------------------

 LICENSE

 This file is part of vip.

 vip is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 vip is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with vip. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Vip;

use Auth;
use AuthLDAP;
use CommonDBTM;
use Group_User;
use User;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

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
        $rulevip   = new RuleVip();
        $criterias = $rulevip->getCriterias();

        if (isset($user->fields["authtype"])
            && (($user->fields["authtype"] == Auth::LDAP)
                || Auth::isAlternateAuth($user->fields['authtype']))) {
            $config_ldap = new AuthLDAP();
            $ds          = false;

            if ($config_ldap->getFromDB($user->fields['auths_id'])) {
                $ds = $config_ldap->connect();
            }

            if ($ds) {
                $info = AuthLdap::getUserByDn($ds, $user->fields['user_dn'], []);
            }

            $input = [];
            foreach ($criterias as $criteria) {
                if (isset($criteria['field']) && isset($info[$criteria['field']]) && isset($info[$criteria['field']][0])) {
                    $input[$criteria['field']] = $info[$criteria['field']][0];
                }
                if (isset($info["dn"])) {
                    $input["dn"] = $info["dn"];
                }
            }

            $ruleCollection = new RuleVipCollection($user->fields['entities_id']);
            $fields         = [];

            $fields = $ruleCollection->processAllRules($input, $fields, []);

            //Store rule that matched
            if (isset($fields['groups_id'])) {
                $groupuser = new Group_User();

                $result = $groupuser->find([
                    'users_id' => $user->getID(),
                    'groups_id' => $fields['groups_id'],
                ]);
                if (!$result) {
                    $groupuser->add(['users_id'  => $user->getID(),
                        'groups_id' => $fields['groups_id']]);
                }
            }
        }
    }

    public static function afterUpdate(User $user)
    {
        $rulevip   = new RuleVip();
        $criterias = $rulevip->getCriterias();

        if (isset($user->fields["authtype"])
            && (($user->fields["authtype"] == Auth::LDAP)
                || Auth::isAlternateAuth($user->fields['authtype']))) {
            $config_ldap = new AuthLDAP();
            $ds          = false;

            if ($config_ldap->getFromDB($user->fields['auths_id'])) {
                $ds = $config_ldap->connect();
            }

            if ($ds) {
                $info = AuthLdap::getUserByDn($ds, $user->fields['user_dn'], []);
            }

            $input = [];
            foreach ($criterias as $criteria) {
                if (isset($criteria['field']) && isset($info[$criteria['field']]) && isset($info[$criteria['field']][0])) {
                    $input[$criteria['field']] = $info[$criteria['field']][0];
                }
                if (isset($info["dn"])) {
                    $input["dn"] = $info["dn"];
                }
            }

            $ruleCollection = new RuleVipCollection($user->fields['entities_id']);
            $fields         = [];

            $fields = $ruleCollection->processAllRules($input, $fields, []);

            //Store rule that matched
            if (isset($fields['groups_id'])) {
                $groupuser = new Group_User();

                $result = $groupuser->find([
                    'users_id' => $user->getID(),
                    'groups_id' => $fields['groups_id'],
                ]);
                if (!$result) {
                    $groupuser->add(['users_id'  => $user->getID(),
                        'groups_id' => $fields['groups_id']]);
                }
            }
        }
    }
}

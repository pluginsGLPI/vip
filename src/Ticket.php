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

use CommonDBTM;
use CommonITILActor;
use Computer;
use Printer;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}


class Ticket extends CommonDBTM
{
    public static $types = ['Ticket', 'Printer', 'Computer'];

    /**
     * @param $uid
     *
     * @return bool
     */
    public static function isUserVip($uid)
    {
        global $DB;

        if ($uid) {
            $result = $DB->request([
                'SELECT' => ['glpi_plugin_vip_groups.id'],
                'FROM' => 'glpi_groups_users',
                'LEFT JOIN' => [
                    'glpi_plugin_vip_groups' => [
                        'ON' => [
                            'glpi_plugin_vip_groups' => 'id',
                            'glpi_groups_users' => 'groups_id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'glpi_plugin_vip_groups.isvip' => 1,
                    'glpi_groups_users.users_id' => $uid
                ]
            ]);
            if (count($result) > 0) {
                return $result->current()['id'];
            }
        }

        return false;
    }

    /**
     * @param $entities
     *
     * @return array
     */
    public static function getUserVipList($entities)
    {
        global $DB;

        $vip = [];

        $result = $DB->request([
            'SELECT' => ['glpi_groups_users.users_id'],
            'FROM' => 'glpi_groups_users',
            'LEFT JOIN' => [
                'glpi_plugin_vip_groups' => [
                    'ON' => [
                        'glpi_plugin_vip_groups' => 'id',
                        'glpi_groups_users' => 'groups_id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_vip_groups.isvip' => 1
            ]
        ]);
        if (count($result) > 0) {
            foreach ($result as $uids) {
                $vip[] = $uids['users_id'];
            }
        }
        return $vip;
    }

    /**
     * @param $ticketid
     *
     * @return bool
     */
    public static function isTicketVip($ticketid)
    {
        global $DB;

        if ($ticketid > 0) {
            $userresult = $DB->request([
                'SELECT' => ['users_id'],
                'FROM' => 'glpi_tickets_users',
                'WHERE' => [
                    'type' => CommonITILActor::REQUESTER,
                    'tickets_id' => $ticketid
                ]
            ]);
            if (count($userresult) > 0) {
                foreach ($userresult as $uids) {
                    $isuservip = self::isUserVip($uids['users_id']);
                    if ($isuservip > 0) {
                        return $isuservip;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $printers_id
     *
     * @return bool
     */
    public static function isPrinterVip($printers_id)
    {
        $printer = new Printer();
        $printer->getFromDB($printers_id);
        return self::isUserVip($printer->getField('users_id'));
    }

    /**
     * @param $computers_id
     *
     * @return bool
     */
    public static function isComputerVip($computers_id)
    {
        $computer = new Computer();
        $computer->getFromDB($computers_id);
        return self::isUserVip($computer->getField('users_id'));
    }

    /**
     * @param $params
     *
     * @return void
     */
    public static function showVIPInfos($params)
    {
        $item = $params['item'];

        if ($item != null && in_array($item->getType(), self::$types)) {
            if ($item->getType() == 'Ticket') {
                if ($id = self::isTicketVip($item->getID())) {
                    $name = Group::getVipName($id);
                    $icon = Group::getVipIcon($id);
                    $color = Group::getVipColor($id);
                    echo "<div class='alert alert-danger center'>";
                    echo "<i class='ti $icon' title=\"$name\" style='font-size:2em;color: $color'></i>&nbsp;";
                    echo sprintf(__('%1$s %2$s'), __('This ticket concerns at least one', 'vip'), $name);
                    echo "</div>";
                }
            } else {
                if ($id = self::isUserVip($item->getField('users_id'))) {
                    $color = Group::getVipColor($id);
                    echo "<div class='alert alert-danger center'>";
                    if ($item->getType() == 'Computer') {
                        $name = Group::getVipName($id);
                        $icon = Group::getVipIcon($id);
                        echo "<i class='ti $icon' title=\"$name\" style='font-size:2em;color: $color'></i>&nbsp;";
                        echo sprintf(__('%1$s %2$s'), __('This computer is used by a', 'vip'), $name);
                    } elseif ($item->getType() == 'Printer') {
                        $name = Group::getVipName($id);
                        $icon = Group::getVipIcon($id);
                        echo "<i class='ti $icon' title=\"$name\" style='font-size:2em;color: $color'></i>&nbsp;";
                        echo sprintf(__('%1$s %2$s'), __('This printer is used by a', 'vip'), $name);
                    }
                    echo "</div>";
                }
            }
        }
    }
}

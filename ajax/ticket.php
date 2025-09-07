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

Session::checkLoginUser();
//Html::header_nocache();

switch ($_POST['action']) {
    case 'getTicket':
        header('Content-Type: application/json; charset=UTF-8"');

        $params = [
            'entities_id' => (is_array($_SESSION['glpiactiveentities']) ? json_encode(
                array_values($_SESSION['glpiactiveentities'])
            ) : $_SESSION['glpiactiveentities']),
            'used' => []
        ];

        if (isset($_POST['items_id'])) {
            $ticket = new Ticket();
            $actor = new Ticket_User();
            if ($ticket->getFromDB($_POST['items_id'])) {
                $actors = $actor->getActors($_POST['items_id']);

                $used = [];
                if (isset($actors[CommonITILActor::REQUESTER])) {
                    foreach ($actors[CommonITILActor::REQUESTER] as $requesters) {
                        $used[] = $requesters['users_id'];
                    }
                }

                $params = [
                    'used' => $used,
                    'entities_id' => $ticket->fields['entities_id']
                ];
            }
        }

        echo json_encode($params);
        break;
    case 'getVIP':
        header('Content-Type: application/json; charset=UTF-8"');

        $params = [
            'entities_id' => (is_array($_SESSION['glpiactiveentities']) ? json_encode(
                array_values($_SESSION['glpiactiveentities'])
            ) : $_SESSION['glpiactiveentities']),
            'used' => []
        ];

        $used = PluginVipTicket::getUserVipList($params['entities_id']);
        $used = array_unique($used);
        if (count($used) > 0) {
            $params = ['used' => $used];
        }

        echo json_encode($params);
        break;
    case 'getPrinter':
        header('Content-Type: application/json; charset=UTF-8"');

        $params = [
            'entities_id' => (is_array($_SESSION['glpiactiveentities']) ? json_encode(
                array_values($_SESSION['glpiactiveentities'])
            ) : $_SESSION['glpiactiveentities']),
            'used' => []
        ];

        if (isset($_POST['items_id'])) {
            $printer = new Printer();
            $printer->getFromDB($_POST['items_id']);

            $used = [];
            $used[] = $printer->fields['users_id'];


            $params = [
                'used' => $used,
                'entities_id' => $printer->fields['entities_id']
            ];
        }

        echo json_encode($params);
        break;
    case 'getComputer':
        header('Content-Type: application/json; charset=UTF-8"');

        $params = [
            'entities_id' => (is_array($_SESSION['glpiactiveentities']) ? json_encode(
                array_values($_SESSION['glpiactiveentities'])
            ) : $_SESSION['glpiactiveentities']),
            'used' => []
        ];

        if (isset($_POST['items_id'])) {
            $computer = new Computer();
            $computer->getFromDB($_POST['items_id']);

            $used = [];
            $used[] = $computer->fields['users_id'];


            $params = [
                'used' => $used,
                'entities_id' => $computer->fields['entities_id']
            ];
        }
        echo json_encode($params);
        break;
}

<?php

/*
 -------------------------------------------------------------------------
 vip plugin for GLPI
 Copyright (C) 2022-2026 by the vip Development Team.

 https://github.com/pluginsGLPI/vip
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

use GlpiPlugin\Vip\Ticket;

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
            $ticket = new \Ticket();
            $actor = new Ticket_User();
            $items_id = (int)$_POST['items_id'];
            if ($ticket->getFromDB($items_id) && $ticket->can($items_id, READ)) {
                $actors = $actor->getActors($items_id);

                $used = [];
                if (isset($actors[CommonITILActor::REQUESTER])) {
                    foreach ($actors[CommonITILActor::REQUESTER] as $requesters) {
                        $used[] = $requesters['users_id'];
                    }
                }

                $params = [
                    'used'        => $used,
                    'entities_id' => $ticket->fields['entities_id'],
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

        $used = Ticket::getUserVipList($params['entities_id']);
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
            $items_id = (int)$_POST['items_id'];
            if ($printer->getFromDB($items_id) && $printer->can($items_id, READ)) {
                $used = [];
                if (isset($printer->fields['users_id'])) {
                    $used[] = $printer->fields['users_id'];
                }

                $params = [
                    'used'        => $used,
                    'entities_id' => $printer->fields['entities_id'],
                ];
            }
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
            $items_id = (int)$_POST['items_id'];
            if ($computer->getFromDB($items_id) && $computer->can($items_id, READ)) {
                $used = [];
                $used[] = $computer->fields['users_id'];

                $params = [
                    'used'        => $used,
                    'entities_id' => $computer->fields['entities_id'],
                ];
            }
        }
        echo json_encode($params);
        break;
}

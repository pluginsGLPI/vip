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

use GlpiPlugin\Vip\Group;

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: application/json; charset=UTF-8");

global $CFG_GLPI;
switch ($_POST['action']) {
    case "load":
        $vip_group = new Group();
        $vip       = $vip_group->getVipUsers();

        $params                            = [];
        $params['page_limit']              = $CFG_GLPI['dropdown_max'];
        $params['root_doc']                = $CFG_GLPI['root_doc'];
        $params['minimumResultsForSearch'] = $CFG_GLPI['ajax_limit_count'];
        $params['emptyValue']              = Dropdown::EMPTY_VALUE;

        echo json_encode(['vip' => $vip, 'params' => $params], JSON_HEX_TAG);
        break;
}

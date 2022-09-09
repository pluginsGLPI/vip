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

include('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

switch ($_POST['action']) {
   case "load" :
      $vip_group = new PluginVipGroup();
      $vip       = $vip_group->getVipUsers();

      $params                            = [];
      $params['page_limit']              = $CFG_GLPI['dropdown_max'];
      $params['root_doc']                = $CFG_GLPI['root_doc'];
      $params['minimumResultsForSearch'] = $CFG_GLPI['ajax_limit_count'];
      $params['emptyValue']              = Dropdown::EMPTY_VALUE;

      echo "<script type='text/javascript'>";
      echo "var viptest = $(document).initVipPlugin(" . json_encode($params) . ");";
      echo "viptest.changeRequesterColor(" . json_encode($vip) . ");";
      echo "</script>";
      break;
}

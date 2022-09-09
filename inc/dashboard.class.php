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

class PluginVipDashboard extends CommonGLPI {

   public  $widgets = [];
   private $options;
   private $datas, $form;

   public function __construct($_options = []) {
      $this->options = $_options;
   }

   function getWidgetsForItem() {

      $widgets = [
         __('Tables', "mydashboard") => [
            $this->getType() . "1" => ["title"   => __("Tickets VIP", "mydashboard"),
                                        "icon"    => "ti ti-table",
                                        "comment" => ""],
         ],
      ];
      return $widgets;

   }

   public function getWidgetContentForItem($widgetId) {
      global $DB;

      $dbu = new DbUtils();
      switch ($widgetId) {

         case $this->getType() . "1":

            $widget = new PluginMydashboardHtml();

            $link_ticket = Toolbox::getItemTypeFormURL("Ticket");

            $mygroups = Group_User::getUserGroups(Session::getLoginUserID(), ["is_assign" => 1]);
            $groups   = [];
            foreach ($mygroups as $mygroup) {
               $groups[] = $mygroup["id"];
            }

            $query = "SELECT  `glpi_tickets`.`id` AS tickets_id, 
                                 `glpi_tickets`.`status` AS status, 
                                 `glpi_tickets`.`time_to_resolve` AS time_to_resolve
                        FROM `glpi_tickets`
                        LEFT JOIN `glpi_entities` ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`)
                        LEFT JOIN `glpi_groups_tickets` ON (`glpi_tickets`.`id` = `glpi_groups_tickets`.`tickets_id` 
                                                            AND `glpi_groups_tickets`.`type` = " . CommonITILActor::ASSIGN . ")
                        WHERE `glpi_tickets`.`is_deleted` = '0' 
                              AND `glpi_tickets`.`status` NOT IN (" . CommonITILObject::INCOMING . "," . CommonITILObject::SOLVED . "," . CommonITILObject::CLOSED . ") ";
            if (count($groups) > 0) {
               $query .= "AND `glpi_groups_tickets`.`groups_id` IN (" . implode(",", $groups) . ")";
            }
            $query .= "ORDER BY `glpi_tickets`.`time_to_resolve` DESC";//

            $widget  = PluginMydashboardHelper::getWidgetsFromDBQuery('table', $query);
            $headers = [__('ID'),
                        _n('Requester', 'Requesters', 2),
                        __('Status'),
                        __('Time to resolve'),
                        __('Assigned to technicians')];
            $widget->setTabNames($headers);

            $result = $DB->query($query);
            $nb     = $DB->numrows($result);

            $datas   = [];
            $tickets = [];

            if ($nb) {
               while ($data = $DB->fetchAssoc($result)) {

                  $ticket = new Ticket();
                  $ticket->getFromDB($data['tickets_id']);
                  if ($ticket->countUsers(CommonITILActor::REQUESTER)) {
                     $users = [];
                     foreach ($ticket->getUsers(CommonITILActor::REQUESTER) as $u) {
                        $users[] = $u['users_id'];
                     }
                     foreach ($users as $key => $val) {
                        if (PluginVipTicket::isUserVip($val) !== false) {
                           $tickets[] = $data;
                        }
                     }
                  }
               }
               $i = 0;

               foreach ($tickets as $key => $val) {

                  $ticket = new Ticket();
                  $ticket->getFromDB($val['tickets_id']);

                  $bgcolor = $_SESSION["glpipriority_" . $ticket->fields["priority"]];

                  $name_ticket = "<div class='center' style='background-color:$bgcolor; padding: 10px;'>";
                  $name_ticket .= "<a href='" . $link_ticket . "?id=" . $val['tickets_id'] . "' target='_blank'>";
                  $name_ticket .= sprintf(__('%1$s: %2$s'), __('ID'), $val['tickets_id']);
                  $name_ticket .= "</a>";
                  $name_ticket .= "</div>";


                  $datas[$i]["tickets_id"] = $name_ticket;


                  $userdata = '';
                  if ($ticket->countUsers(CommonITILActor::REQUESTER)) {

                     foreach ($ticket->getUsers(CommonITILActor::REQUESTER) as $u) {
                        $k = $u['users_id'];
                        if ($k) {
                           $userdata .= $dbu->getUserName($k);
                        }


                        if ($ticket->countUsers(CommonITILActor::REQUESTER) > 1) {
                           $userdata .= "<br>";
                        }
                     }
                  }
                  $datas[$i]["users_id"] = $userdata;

                  $datas[$i]["status"] = Ticket::getStatus($val['status']);

                  $time_to_resolve = '';
                  $due             = strtotime(date('Y-m-d H:i:s')) - strtotime($val['time_to_resolve']);
                  if ($due > 0) {
                     $time_to_resolve .= "<div class='center red'>";
                  }
                  $time_to_resolve .= Html::convDateTime($val['time_to_resolve']);
                  if ($due > 0) {
                     $time_to_resolve .= "</div>";
                  }
                  $datas[$i]["time_to_resolve"] = $time_to_resolve;

                  $techdata = '';
                  if ($ticket->countUsers(CommonITILActor::ASSIGN)) {

                     foreach ($ticket->getUsers(CommonITILActor::ASSIGN) as $u) {
                        $k = $u['users_id'];
                        if ($k) {
                           $techdata .= $dbu->getUserName($k);
                        }


                        if ($ticket->countUsers(CommonITILActor::ASSIGN) > 1) {
                           $techdata .= "<br>";
                        }
                     }
                  }
                  $datas[$i]["techs_id"] = $techdata;
                  $i++;
               }
            }

            $widget->setTabDatas($datas);
//            $widget->setOption("bSort", false);
            $widget->toggleWidgetRefresh();

            $widget->setWidgetTitle(__("Tickets VIP", "mydashboard"));

            return $widget;
            break;
      }
   }
}

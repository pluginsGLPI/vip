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

namespace GlpiPlugin\Vip;

use CommonGLPI;
use CommonITILActor;
use CommonITILObject;
use DBmysqlIterator;
use DbUtils;
use Glpi\Application\View\TemplateRenderer;
use Group_User;
use Html;
use GlpiPlugin\Mydashboard\Datatable;
use GlpiPlugin\Mydashboard\Helper;
use GlpiPlugin\Mydashboard\Html as MydashboardHtml;
use GlpiPlugin\Mydashboard\Menu;
use GlpiPlugin\Mydashboard\Widget;
use Session;
use Toolbox;

class Dashboard extends CommonGLPI
{
    public $widgets = [];
    private $options;
    private $datas;
    private $form;

    public function __construct($_options = [])
    {
        $this->options = $_options;
    }

     /**
      * @return \array[][]
      */
    public function getWidgetsForItem()
    {
        $widgets = [
            Menu::$HELPDESK => [
                $this->getType() . "1" => ["title"   => __("Tickets VIP", "mydashboard"),
                                           "type"    => Widget::$TABLE,
                                           "comment" => ""],
            ],
        ];

        return $widgets;
    }

    public function getWidgetContentForItem($widgetId)
    {
        global $DB;

        if (!Session::haveRight('plugin_vip', READ)) {
            return new MydashboardHtml();
        }

        $dbu = new DbUtils();
        switch ($widgetId) {
            case $this->getType() . "1":
                $widget = new MydashboardHtml();

                $link_ticket = Toolbox::getItemTypeFormURL("Ticket");

                $mygroups = Group_User::getUserGroups(Session::getLoginUserID(), ["is_assign" => 1]);
                $groups   = [];
                foreach ($mygroups as $mygroup) {
                    $groups[] = $mygroup["id"];
                }

                $criteria = [
                    'SELECT' => [
                        'glpi_tickets.id AS tickets_id', 'glpi_tickets.status AS status', 'glpi_tickets.time_to_resolve AS time_to_resolve'
                    ],
                    'FROM' => 'glpi_tickets',
                    'LEFT JOIN' => [
                        'glpi_entities' => [
                            'ON' => [
                                'glpi_tickets' => 'entities_id',
                                'glpi_entities' => 'id'
                            ]
                        ],
                        'glpi_groups_tickets' => [
                            'ON' => [
                                'glpi_tickets' => 'id',
                                'glpi_groups_tickets' => 'tickets_id', [
                                    'AND' => [
                                        'glpi_groups_tickets.type' => CommonITILActor::ASSIGN
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'WHERE' => [
                        'glpi_tickets.is_deleted' => '0',
                        'NOT' => ['glpi_tickets.status' => [CommonITILObject::INCOMING, CommonITILObject::SOLVED, CommonITILObject::CLOSED]],
                        getEntitiesRestrictCriteria('glpi_tickets'),
                    ],
                    'ORDER' => ['glpi_tickets.time_to_resolve' => 'DESC']
                ];
                if (count($groups) > 0) {
                    $criteria['WHERE']['glpi_groups_tickets.groups_id'] = $groups;
                }
                $it = new DBmysqlIterator($DB);
                $it->buildQuery($criteria);
                $widget  = Helper::getWidgetsFromDBQuery('table', $it->getSql());
                $headers = [__('ID'),
                            _n('Requester', 'Requesters', 2),
                            __('Status'),
                            __('Time to resolve'),
                            __('Assigned to technicians')];
                $widget->setTabNames($headers);

                $result = $DB->request($criteria);
                $nb     = count($result);

                $datas   = [];
                $tickets = [];

                if ($nb) {
                    foreach ($result as $data) {
                        $ticket = new \Ticket();
                        $ticket->getFromDB($data['tickets_id']);
                        if ($ticket->countUsers(CommonITILActor::REQUESTER)) {
                            $users = [];
                            foreach ($ticket->getUsers(CommonITILActor::REQUESTER) as $u) {
                                $users[] = $u['users_id'];
                            }
                            foreach ($users as $key => $val) {
                                if (Ticket::isUserVip($val) !== false) {
                                    $tickets[] = $data;
                                }
                            }
                        }
                    }
                    $i = 0;

                    foreach ($tickets as $key => $val) {
                        $ticket = new \Ticket();
                        $ticket->getFromDB($val['tickets_id']);

                        $bgcolor = $_SESSION["glpipriority_" . $ticket->fields["priority"]];

                        $datas[$i]["tickets_id"] = TemplateRenderer::getInstance()->render('@vip/dashboard_ticket_cell.html.twig', [
                            'bgcolor'    => $bgcolor,
                            'link'       => $link_ticket,
                            'tickets_id' => $val['tickets_id'],
                            'id_label'   => __('ID'),
                        ]);

                        $user_names = [];
                        if ($ticket->countUsers(CommonITILActor::REQUESTER)) {
                            foreach ($ticket->getUsers(CommonITILActor::REQUESTER) as $u) {
                                $k = $u['users_id'];
                                if ($k) {
                                    $user_names[] = $dbu->getUserName($k);
                                }
                            }
                        }
                        $datas[$i]["users_id"] = TemplateRenderer::getInstance()->render('@vip/dashboard_users_cell.html.twig', [
                            'names' => $user_names,
                        ]);

                        $datas[$i]["status"] = \Ticket::getStatus($val['status']);

                        $due = strtotime(date('Y-m-d H:i:s')) - strtotime($val['time_to_resolve']);
                        $datas[$i]["time_to_resolve"] = TemplateRenderer::getInstance()->render('@vip/dashboard_datetime_cell.html.twig', [
                            'overdue'  => $due > 0,
                            'datetime' => Html::convDateTime($val['time_to_resolve']),
                        ]);

                        $tech_names = [];
                        if ($ticket->countUsers(CommonITILActor::ASSIGN)) {
                            foreach ($ticket->getUsers(CommonITILActor::ASSIGN) as $u) {
                                $k = $u['users_id'];
                                if ($k) {
                                    $tech_names[] = getUserName($k);
                                }
                            }
                        }
                        $datas[$i]["techs_id"] = TemplateRenderer::getInstance()->render('@vip/dashboard_users_cell.html.twig', [
                            'names' => $tech_names,
                        ]);
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

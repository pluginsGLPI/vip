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

use Glpi\Plugin\Hooks;
use GlpiPlugin\Vip\Dashboard;
use GlpiPlugin\Vip\Profile;
use GlpiPlugin\Vip\RuleVipCollection;
use GlpiPlugin\Vip\Ticket;
use GlpiPlugin\Vip\Vip;
use GlpiPlugin\Vip\Group;

global $CFG_GLPI;

define('PLUGIN_VIP_VERSION', '1.9.1');

if (!defined("PLUGIN_VIP_DIR")) {
    define("PLUGIN_VIP_DIR", Plugin::getPhpDir("vip"));
    $root = $CFG_GLPI['root_doc'] . '/plugins/vip';
    define("PLUGIN_VIP_WEBDIR", $root);
}

// Init the hooks of the plugins -Needed
function plugin_init_vip()
{

    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['vip'] = true;

    Plugin::registerClass(Profile::class, ['addtabon' => ['Profile']]);
    $PLUGIN_HOOKS['change_profile']['vip'] = [Profile::class, 'changeProfile'];

    if (Session::haveRight('plugin_vip', UPDATE)) {
        Plugin::registerClass(Group::class, ['addtabon' => ['Group']]);
        $PLUGIN_HOOKS['use_massive_action']['vip'] = 1;
        Plugin::registerClass(Ticket::class);
    }

    if (class_exists('PluginMydashboardMenu')) {
        $PLUGIN_HOOKS['mydashboard']['vip'] = [Dashboard::class];
    }

    if (Session::haveRight('plugin_vip', READ)
    && isset($_SESSION["glpiactiveprofile"]["interface"])
    && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['vip'][] = 'js/vip.js.php';
//        $PLUGIN_HOOKS["javascript"]['vip']     = [PLUGIN_VIP_NOTFULL_DIR."/js/vip.js.php"];

        if (class_exists(Ticket::class)) {
            foreach (Ticket::$types as $item) {
                if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], strtolower($item) . ".form.php") !== false) {
                    $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['vip'][] = 'js/vip_load_scripts.js.php';
  //               $PLUGIN_HOOKS['javascript']['vip']       = [
  //                  PLUGIN_VIP_NOTFULL_DIR. "js//vip_load_scripts.js.php",
  //               ];
                }
            }
        }
    }
    if (isset($_SESSION["glpiactiveprofile"]["interface"])
    && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {
        $PLUGIN_HOOKS['pre_show_item']['vip'] = [Ticket::class, 'showVIPInfos'];
    }
    $PLUGIN_HOOKS['item_add']['vip']    = ['User' => [Vip::class, 'afterAdd']];
    $PLUGIN_HOOKS['item_update']['vip'] = ['User' => [Vip::class, 'afterUpdate']];

    Plugin::registerClass(RuleVipCollection::class, [
       'rulecollections_types' => true
    ]);

    // Cannot be placed inside any permission check as the plugin is initialized before the API router authenticates the user
    //TODO activate in GLPI 11 version
//    $PLUGIN_HOOKS[Hooks::REDEFINE_API_SCHEMAS]['vip'] = 'plugin_vip_redefine_api_schemas';
}

function plugin_version_vip()
{

    return ['name'           => "VIP",
           'version'        => PLUGIN_VIP_VERSION,
           'author'         => '<a href="http://www.probesys.com">Probesys</a> & <a href="https//blogglpi.infotel.com">Infotel</a>, Xavier CAILLAUD',
           'license'        => 'AGPLv3+',
           'homepage'       => 'https://github.com/pluginsGLPI/vip',
           'requirements'   => [
              'glpi' => [
                 'min' => '11.0',
                 'max' => '12.0',
                 'dev' => false
              ]
           ]
    ];
}

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

// Plugin web root, mirroring PLUGIN_VIP_WEBDIR from setup.php. GLPI exposes
// both variables in the page <head> (config_js) before any plugin script is
// loaded, so no server-side interpolation is needed here.
var root_vip_doc = ((window.CFG_GLPI && CFG_GLPI.root_doc) || '')
   + ((window.GLPI_PLUGINS_PATH && GLPI_PLUGINS_PATH.vip) || '/plugins/vip');

(function ($) {

    $.fn.vip_load_scripts = function () {

        init();
        // Start the plugin
        function init() {
            // Send data
            $.ajax({
                url: root_vip_doc +'/ajax/loadscripts.php',
                type: "POST",
                dataType: "json",
                data: 'action=load',
                success: function (data) {
                    var viptest = $(document).initVipPlugin(data.params);
                    viptest.changeRequesterColor(data.vip);
                }
            });
        }

        return this;
    };
}(jQuery));

$(document).vip_load_scripts();

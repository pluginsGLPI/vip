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

header('Content-Type: text/javascript');
?>

var root_vip_doc = "<?php echo PLUGIN_VIP_WEBDIR; ?>";
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

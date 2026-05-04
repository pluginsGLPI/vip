<?php
use Glpi\Event;
include('../../../../inc/includes.php');
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

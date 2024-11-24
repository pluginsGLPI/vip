<?php
use Glpi\Event;
include('../../inc/includes.php');
header('Content-Type: text/javascript');

?>

var root_vip_doc = "<?php echo PLUGIN_VIP_WEBDIR; ?>";

(function ($) {
    $.fn.initVipPlugin = function (options) {

        var object = this;
        init();

        // Start the plugin
        function init() {
            object.params = new Array();
            object.params['entities_id'] = 0;
            object.params['page_limit'] = 0;
            object.params['minimumResultsForSearch'] = 0;
            object.params['root_doc'] = null;
            object.params['emptyValue'] = null;

            if (options != undefined) {
                $.each(options, function (index, val) {
                    if (val != undefined && val != null) {
                        object.params[index] = val;
                    }
                });
            }
        }

        this.changeRequesterColor = function (vip) {
            $(document).ready(function () {

                // only in ticket form
                if (location.pathname.indexOf('ticket.form.php') > 0) {
                    $.urlParam = function (url, name) {
                        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
                        if (results != null) {
                            return results[1] || 0;
                        }
                    };

                    // get item id
                    var items_id = $.urlParam(window.location.href, 'id');

                    // Launched on each complete Ajax load
                    $(document).ajaxComplete(function (event, xhr, option) {

                        if (option.url !== undefined
                            && (option.url.indexOf('vip/ajax/loadscripts.php') > 0 || option.url.indexOf('common.tabs.php') > 0)) {

                            setTimeout(function () {

                                if (items_id > 0) {
                                    $.ajax({
                                        url: root_vip_doc + '/ajax/ticket.php',
                                        type: "POST",
                                        dataType: "json",
                                        data: {
                                            'items_id': items_id,
                                            'action': 'getTicket'
                                        },
                                        success: function (response, opts) {
                                            var ticketVip = false;
                                            $.each(vip, function (index, val) {
                                                $.each(response.used, function (index2, val2) {
                                                    var userid = val.id;

                                                    if (val.id == val2
                                                    ) {
                                                        var userid = val.id;
                                                        $("span[data-items-id='" + userid + "']").css("color", val.color);
                                                        $("span[data-items-id='" + userid + "']").after("&nbsp;<i class='fas " + val.icon + "' title=\"" + val.name + "\" style='font-family:\"Font Awesome 5 Free\", \"Font Awesome 5 Brands\";color:" + val.color + "'></i>&nbsp;");
                                                    }
                                                });
                                            });
                                        },
                                    });
                                } else {
                                    $.ajax({
                                        url: root_vip_doc + '/ajax/ticket.php',
                                        type: "POST",
                                        dataType: "json",
                                        data: {
                                            'action': 'getVIP'
                                        },
                                        success: function (response, opts) {
                                            var ticketVip = false;
                                            $.each(vip, function (index, val) {
                                                $.each(response.used, function (index2, val2) {
                                                    var userid = val.id;

                                                    if (val.id == val2
                                                    ) {
                                                        var userid = val.id;
                                                        $("span[data-items-id='" + userid + "']").css("color", val.color);
                                                        $("span[data-items-id='" + userid + "']").after("&nbsp;<i class='fas " + val.icon + "' title=\"" + val.name + "\" style='font-family:\"Font Awesome 5 Free\", \"Font Awesome 5 Brands\";color:" + val.color + "'></i>&nbsp;");
                                                    }
                                                });
                                            });
                                        },
                                    });
                                }
                            }, 500);
                        }
                        // }, 500);
                    }, this);
                }
                inputName = 'users_id';
                if (location.pathname.indexOf('printer.form.php') > 0) {
                    $.urlParam = function (url, name) {
                        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
                        if (results != null) {
                            return results[1] || 0;
                        }
                    };
                    // get item id
                    var items_id = $.urlParam(window.location.href, 'id');

                    setTimeout(function () {
                        $.ajax({
                            url: root_vip_doc + '/ajax/ticket.php',
                            type: "POST",
                            dataType: "json",
                            data: {
                                'items_id': items_id,
                                'action': 'getPrinter'
                            },
                            success: function (response, opts) {
                                $.each(vip, function (index, val) {
                                    $.each(response.used, function (index2, val2) {
                                        var userid = val.id;

                                        if (val.id == val2
                                        ) {
                                            var userid = val.id;
                                            $("span[id^='select2-dropdown_users_id']").each(function () {
                                                //not select2-dropdown_users_id_tech
                                                selectname = $(this).attr('id');
                                                if (selectname.indexOf('select2-dropdown_users_id_tech') == -1) {
                                                    $(this).css("color", val.color);
                                                }

                                            });
                                            // $("span[id^='select2-dropdown_users_id']").css("color", val.color);
                                            $("select[name='" + inputName + "']").before("&nbsp;<i class='fas " + val.icon + " fa-2x' title=\"" + val.name + "\" style='font-family:\"Font Awesome 5 Free\", \"Font Awesome 5 Brands\";color:" + val.color + "'></i>&nbsp;");
                                        }
                                    });
                                });
                            }
                        });
                    }, 500);
                }
                inputName = 'users_id';
                if (location.pathname.indexOf('computer.form.php') > 0) {
                    $.urlParam = function (url, name) {
                        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(url);
                        if (results != null) {
                            return results[1] || 0;
                        }
                    };
                    // get item id
                    var items_id = $.urlParam(window.location.href, 'id');

                    setTimeout(function () {
                        $.ajax({
                            url: root_vip_doc + '/ajax/ticket.php',
                            type: "POST",
                            dataType: "json",
                            data: {
                                'items_id': items_id,
                                'action': 'getComputer'
                            },
                            success: function (response, opts) {
                                $.each(vip, function (index, val) {
                                    $.each(response.used, function (index2, val2) {
                                        var userid = val.id;

                                        if (val.id == val2
                                        ) {
                                            var userid = val.id;
                                            $("span[id^='select2-dropdown_users_id']").each(function () {
                                                //not select2-dropdown_users_id_tech
                                                selectname = $(this).attr('id');
                                                if (selectname.indexOf('select2-dropdown_users_id_tech') == -1) {
                                                    $(this).css("color", val.color);
                                                }

                                            });
                                            // $("span[id^='select2-dropdown_users_id']").css("color", val.color);
                                            $("select[name='" + inputName + "']").before("&nbsp;<i class='fas " + val.icon + " fa-2x' title=\"" + val.name + "\" style='font-family:\"Font Awesome 5 Free\", \"Font Awesome 5 Brands\";color:" + val.color + "'></i>&nbsp;");
                                        }
                                    });
                                });
                            }
                        });
                    }, 500);
                }
            });
        };

        return this;
    };
}(jQuery));

"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const moment_1 = __importDefault(require("moment"));
require("moment/min/locales");
(function ($) {
    // @ts-ignore
    var vars = is_media_lib_backend_vars;
    moment_1.default.locale(vars.lang);
    var formatter_functions = {
        "mime_type": function (data, type, row) {
            return '<i class="iservice-mime-type" data-type="' + data + '" title="' + data + '"></i>';
        },
        "img": function (data, type, row) {
            return data != null ? '<img src="' + data + '" />' : "";
        },
        "file": function (data, type, row) {
            return '<a href="' + vars.media_edit_url + row["attachment_id"] + '">' + row["file"] + '</a>';
        },
        "date": function (data, type, row) {
            return (0, moment_1.default)(data).format("D. MMMM YYYY HH:mm");
        },
        "post_title": function (data, type, row) {
            return '<a href="' + vars.post_edit_url + row["post_id"] + '">' + row["post_title"] + '</a>';
        },
        "post_type": function (data, type, row) {
            return vars.pts[data] != undefined ? vars.pts[data] : data;
        },
        "usage_type": function (data, type, row) {
            return '<i class="usage-type ' + data + '" title="' + data + '"></i>';
        }
    };
    var $show_hide_buttons = $(".is-media-lib-tracking-list .show-hide");
    $show_hide_buttons.empty();
    var $table_header = $(".is-media-lib-tracking-list .table-view-list thead tr");
    $table_header.empty();
    var columns = [];
    for (var i = 0, tmp_count = vars.cols.length; i < tmp_count; ++i) {
        $show_hide_buttons.append($('<li />').attr({
            "class": (vars.cols[i]["visable"] == true ? "active" : ""),
            "data-column": vars.cols[i]["id"]
        }).text(vars.cols[i]["name"]));
        $table_header.append($('<th />').text(vars.cols[i]["hide-headline-name"] == undefined ? vars.cols[i]["name"] : ""));
        var col = {
            "data": vars.cols[i]["id"],
            "visible": vars.cols[i]["visable"],
        };
        if (vars.cols[i]["width"] != undefined) {
            col["width"] = vars.cols[i]["width"];
        }
        if (vars.cols[i]["formatter"] != undefined && formatter_functions[vars.cols[i]["formatter"]] != undefined) {
            col["render"] = formatter_functions[vars.cols[i]["formatter"]];
        }
        columns.push(col);
    }
    var is_media_table = $("#is_media_lib_tracking_list").DataTable({
        columns: columns,
        order: [[1, "asc"]],
        processing: true,
        language: {
            info: vars.l18n["info"],
            infoEmpty: vars.l18n["info_empty"],
            infoFiltered: vars.l18n["info_filtered"],
            lengthMenu: vars.l18n["length_menu"],
            zeroRecords: vars.l18n["zero_records"],
            loadingRecords: '&nbsp;',
            processing: '<div class="iservice-loader loading"><div class="loader"></div></div>',
            search: vars.l18n["search"],
            paginate: {
                first: vars.l18n["first"],
                previous: vars.l18n["previous"],
                next: vars.l18n["next"],
                last: vars.l18n["last"],
            },
            entries: {
                _: vars.l18n["_"],
                1: vars.l18n["1"],
            }
        },
        serverSide: true,
        ajax: {
            url: vars.search_url,
            type: "POST",
            data: function (d) {
                return JSON.stringify(d);
            }
        },
        initComplete: function () {
            var $filters = $(".special-filter");
            $("div.dataTables_filter").prepend($filters);
            $filters.removeClass("hidden");
        }
    });
    $(".is-media-lib-tracking-list .show-hide li").on("click touch", function (e) {
        e.preventDefault();
        var col_name = e.target.dataset.column;
        var columns = is_media_table.columns().data();
        for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
            // colname table col -> index found
            if (col_name == is_media_table.column(i).dataSrc()) {
                var col = is_media_table.column(i);
                col.visible(!col.visible());
                $(e.target).toggleClass("active");
                return;
            }
        }
    });
    $(".special-filter select").on("change", function (e) {
        var columns = is_media_table.columns().data();
        for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
            // colname is classname in select -> index found
            if ($(e.target).hasClass(is_media_table.column(i).dataSrc())) {
                is_media_table.column(i).search(e.target.value).draw();
                return;
            }
        }
    });
    // @ts-ignore
})(window.jQuery);

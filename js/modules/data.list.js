"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const moment_1 = __importDefault(require("moment"));
require("moment/min/locales");
var media_edit_url = "";
var post_edit_url = "";
var media_pts = {};
class DataList {
    constructor($dom, settings = {}) {
        this.settings = {};
        this.formatters = {};
        this.columns = [];
        this.is_media_table = {};
        this.$dom = $dom;
        this.$hide_buttons = $dom.find(".show-hide");
        this.$table_header = $dom.find(".table-view-list thead tr");
        this.$special_filters = $dom.find(".special-filter select");
        this.settings = settings;
        moment_1.default.locale(this.settings.lang != undefined ? this.settings.lang : "en");
        this.add_formmatters();
        this.load_base_structure();
        this.init_events();
        this.init_table();
    } // constructor
    add_formmatters() {
        media_edit_url = this.settings.media_edit_url;
        post_edit_url = this.settings.post_edit_url;
        media_pts = this.settings.pts;
        this.formatters = {
            "mime_type": function (data, type, row) {
                return '<i class="iservice-mime-type" data-type="' + data + '" title="' + data + '"></i>';
            },
            "img": function (data, type, row) {
                return data != null ? '<img src="' + data + '" />' : "";
            },
            "file": function (data, type, row) {
                return '<a href="' + media_edit_url + row["attachment_id"] + '">' + row["file"] + '</a>';
            },
            "date": function (data, type, row) {
                return (0, moment_1.default)(data).format("D. MMMM YYYY HH:mm");
            },
            "post_title": function (data, type, row) {
                return '<a href="' + post_edit_url + row["post_id"] + '">' + row["post_title"] + '</a>';
            },
            "post_type": function (data, type, row) {
                if (media_pts == undefined) {
                    return "";
                }
                return media_pts[data] != undefined ? media_pts[data] : data;
            },
            "usage_type": function (data, type, row) {
                return '<i class="usage-type ' + data + '" title="' + data + '"></i>';
            }
        };
    } // add_formmatters()
    load_base_structure() {
        this.$hide_buttons.empty();
        this.$table_header.empty();
        if (this.settings.cols == undefined) {
            return;
        }
        for (var i = 0, tmp_count = this.settings.cols.length; i < tmp_count; ++i) {
            var li = document.createElement("li");
            li.textContent = this.settings.cols[i]["name"];
            li.dataset.column = this.settings.cols[i]["id"];
            if (this.settings.cols[i]["visable"] == true) {
                li.classList.add("active");
            }
            this.$hide_buttons[0].append(li);
            var th = document.createElement("th");
            th.textContent = this.settings.cols[i]["hide-headline-name"] == undefined ? this.settings.cols[i]["name"] : "";
            this.$table_header[0].append(th);
            var col = {
                "data": this.settings.cols[i]["id"],
                "visible": this.settings.cols[i]["visable"],
            };
            if (this.settings.cols[i]["width"] != undefined) {
                col["width"] = this.settings.cols[i]["width"];
            }
            if (this.settings.cols[i]["formatter"] != undefined && this.formatters[this.settings.cols[i]["formatter"]] != undefined) {
                col["render"] = this.formatters[this.settings.cols[i]["formatter"]];
            }
            this.columns.push(col);
        }
    } // load_base_structure()
    init_events() {
        this.$hide_buttons.find("li").on("click touch", (e) => {
            e.preventDefault();
            var col_name = e.target.dataset.column;
            var columns = this.is_media_table.columns().data();
            for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
                // colname table col -> index found
                if (col_name == this.is_media_table.column(i).dataSrc()) {
                    var col = this.is_media_table.column(i);
                    col.visible(!col.visible());
                    e.target.classList.toggle("active");
                    return;
                }
            }
        });
        this.$special_filters.on("click touch", (e) => {
            e.preventDefault();
            var columns = this.is_media_table.columns().data();
            for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
                // colname is classname in select -> index found
                var target = e.target;
                if (target.nodeName == "OPTION") {
                    target = target.parentNode;
                }
                if (target.classList.contains(this.is_media_table.column(i).dataSrc())) {
                    this.is_media_table.column(i).search(target.value).draw();
                    return;
                }
            }
        });
    } // init_events()
    init_table() {
        // @ts-ignore
        this.is_media_table = this.$dom.find("#is_media_lib_tracking_list").DataTable({
            columns: this.columns,
            order: [[1, "asc"]],
            processing: true,
            language: {
                info: this.settings.l18n["info"],
                infoEmpty: this.settings.l18n["info_empty"],
                infoFiltered: this.settings.l18n["info_filtered"],
                lengthMenu: this.settings.l18n["length_menu"],
                zeroRecords: this.settings.l18n["zero_records"],
                loadingRecords: '&nbsp;',
                processing: '<div class="iservice-loader loading"><div class="loader"></div></div>',
                search: this.settings.l18n["search"],
                paginate: {
                    first: this.settings.l18n["first"],
                    previous: this.settings.l18n["previous"],
                    next: this.settings.l18n["next"],
                    last: this.settings.l18n["last"],
                },
                entries: {
                    _: this.settings.l18n["_"],
                    1: this.settings.l18n["1"],
                }
            },
            serverSide: true,
            ajax: {
                url: this.settings.search_url,
                type: "POST",
                data: function (d) {
                    return JSON.stringify(d);
                }
            },
            initComplete: function () {
                var filters = document.querySelector(".special-filter");
                var data_filters = document.querySelector("div.dataTables_filter");
                if (data_filters != null && filters != null) {
                    data_filters.prepend(filters);
                    filters.classList.remove("hidden");
                }
            }
        });
    } // init_table()
} // class DataList{}
exports.default = DataList;

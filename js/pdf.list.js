"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const data_list_1 = __importDefault(require("./modules/data.list"));
(function ($) {
    class MediaList {
        constructor($dom, settings = {}) {
            this.data_list = new data_list_1.default($dom, settings);
        } // constructor
    }
    var $table = $(".is-media-lib-pdf-list");
    // @ts-ignore
    var vars = is_media_lib_pdf_list_vars;
    if ($table.length > 0) {
        new MediaList($table, vars);
    }
    // @ts-ignore
})(window.jQuery);

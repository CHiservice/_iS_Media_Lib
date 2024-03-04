import { Column } from '../types/data.list.column';
import moment from 'moment';
import 'moment/min/locales';

var media_url = "";
var post_url  = "";
var media_pts = {};

export function get_columns(columns: [], lang: string = "en", media_edit_url: string = "", post_edit_url: string = "", pts: {} = {}): Column[] {
	moment.locale(lang);
	media_url = media_edit_url;
	post_url  = post_edit_url;
	media_pts = pts;

	let Columns : Column[] = [];
	for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
		var col : Column = {
			"data"    : columns[i]["id"],
			"visible" : columns[i]["visible"],
		};
		if(columns[i]["width"] != undefined) {
			col["width"] = columns[i]["width"];
		}
		if(columns[i]["formatter"] != undefined && formatters[columns[i]["formatter"]] != undefined) {
			col["render"] = formatters[columns[i]["formatter"]];
		}
		Columns.push(col);
	}

	return Columns;
}

const formatters = {
	"mime_type" : function(data, type, row) {
		return '<i class="iservice-mime-type" data-type="' + data + '" title="' + data + '"></i>';
	},
	"img" : function(data, type, row) {
		return data != null ? '<img src="' + data + '" />' : "";
	},
	"file" : function(data, type, row) {
		return '<a href="' + media_url + row["attachment_id"] + '">' + row["file"] + '</a>';
	},
	"date" : function(data, type, row) {
		return moment(data).format("D. MMMM YYYY HH:mm");
	},
	"post_title" : function(data, type, row) {
		return '<a href="' + post_url + row["post_id"] + '">' + row["post_title"] + '</a>';
	},
	"post_type" : function(data, type, row) {
		if(media_pts == undefined) {
			return "";
		}
		return media_pts[data] != undefined ? media_pts[data] : data;
	},
	"usage_type" : function(data, type, row) {
		return '<i class="usage-type ' + data + '" title="' + data + '"></i>';
	}
}

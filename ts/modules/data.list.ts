import {formated_l18n} from './data.list.l18n';
import {get_columns} from './data.list.columns';

export default class DataList {
	private $dom             : JQuery<HTMLElement>;
	private $hide_buttons    : JQuery<HTMLElement>;
	private $table_header    : JQuery<HTMLElement>;
	private $special_filters : JQuery<HTMLElement>;
	private settings         : any = {};
	private formatters       : any = {};
	private columns          : any = [];
	private is_media_table   : any = {};

	constructor($dom: JQuery<HTMLElement>, settings : any = {}) {
		this.$dom             = $dom;
		this.$hide_buttons    = $dom.find(".show-hide");
		this.$table_header    = $dom.find(".table-view-list thead tr");
		this.$special_filters = $dom.find(".special-filter select");
		this.settings         = settings;

		this.load_base_structure();
		this.init_events();
		this.init_table();
	} // constructor

	load_base_structure ():void {
		this.$hide_buttons.empty();
		this.$table_header.empty();
	
		if(this.settings.cols == undefined) {
			return;
		}
		for (var i = 0, tmp_count = this.settings.cols.length; i < tmp_count; ++i) {
			var li            = document.createElement("li");
			li.textContent    = this.settings.cols[i]["name"];
			li.dataset.column = this.settings.cols[i]["id"];
			if(this.settings.cols[i]["visible"] == true ) {
				li.classList.add("active");
			}
			this.$hide_buttons[0].append(li);

			var th         = document.createElement("th");
			th.textContent = this.settings.cols[i]["hide-headline-name"] == undefined ? this.settings.cols[i]["name"] : "";
			this.$table_header[0].append(th);
		}

		this.columns = get_columns(this.settings.cols, this.settings.lang, this.settings.media_edit_url, this.settings.post_edit_url, this.settings.pts);
	} // load_base_structure()

	init_events ():void {
		this.$hide_buttons.find("li").on("click touch", (e) => {
			e.preventDefault();
			var col_name = e.target.dataset.column;
			var columns = this.is_media_table.columns().data();
	
			for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
				// colname table col -> index found
				if(col_name == this.is_media_table.column(i).dataSrc()) {
					var col = this.is_media_table.column(i);
	
					col.visible(!col.visible());
					e.target.classList.toggle("active");
					return
				}
			}
		});
	
		this.$special_filters.on("click touch", (e) => {
			e.preventDefault();
			var columns = this.is_media_table.columns().data();
	
			for (var i = 0, tmp_count = columns.length; i < tmp_count; ++i) {
				// colname is classname in select -> index found
				var target : any = e.target;
				if(target.nodeName == "OPTION") {
					target = target.parentNode;
				}
				if(target.classList.contains(this.is_media_table.column(i).dataSrc())) {
					this.is_media_table.column(i).search(target.value).draw();
					return;
				}
			}
		});
	} // init_events()

	init_table():void {
		// @ts-ignore
		this.is_media_table = this.$dom.find("#is_media_lib_tracking_list").DataTable({
			columns    : this.columns,
			order      : [[1, "asc"]],
			processing : true,
			language   : formated_l18n(this.settings.l18n || {}),
			serverSide : true,
			ajax       : {
				url  : this.settings.search_url,
				type : "POST",
				data : function (d) {
					return JSON.stringify(d);
				}
			},
			initComplete: function() {
				var filters      = document.querySelector(".special-filter");
				var data_filters = document.querySelector("div.dataTables_filter");
				if(data_filters != null && filters != null) {
					data_filters.prepend(filters);
					filters.classList.remove("hidden");
				}
			}
		});
	} // init_table()
} // class DataList{}

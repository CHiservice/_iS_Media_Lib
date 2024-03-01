import DataList from "./modules/data.list";

(function ($) {
	class MediaList {
		private data_list : DataList;

		constructor($dom: JQuery<HTMLElement>, settings: any = {}) {
			this.data_list = new DataList($dom, settings);
		} // constructor
	}

	var $table = $(".is-media-lib-pdf-list");
	// @ts-ignore
	var vars : any = is_media_lib_pdf_list_vars;

	if($table.length > 0) {
		new MediaList($table, vars);
	}
	// @ts-ignore
})(window.jQuery);

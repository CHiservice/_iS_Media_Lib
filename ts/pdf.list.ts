import DataList from "./modules/data.list";

(function ($) {
	var $table = $(".is-media-lib-pdf-list");
	// @ts-ignore
	var vars : any = is_media_lib_pdf_list_vars;

	if($table.length > 0) {
		new DataList($table, vars)
	}
	// @ts-ignore
})(window.jQuery);

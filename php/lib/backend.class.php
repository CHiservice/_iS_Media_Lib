<?php
class iS_Media_Lib_Backend {
	public $config = null;

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		add_filter("manage_media_columns", array($this, "add_tracking_column"));
		add_action("manage_media_custom_column", array($this, "tracking_column"), 10, 2);

		if((int) iS_Media_Lib_Settings::is_media_track_overview_enabled() == 1) {
			new iS_Media_Lib_Media_List();
		}
		if((int) iS_Media_Lib_Settings::is_pdf_content_cache_enabled() == 1) {
			new iS_Media_Lib_PDF_List();
		}
	} // __construct()

	function add_tracking_column($columns) {
		foreach ($columns as $key => $value) {
			$new_columns[$key] = $value;
			if ($key == "title") {
				$new_columns["tracking"] = esc_html__("Usage", $this->config->get("modulName"));
			}
		}

		return $new_columns;
	} // add_tracking_column()

	function tracking_column($column_name, $id) {
		if ($column_name == "tracking") {
			echo iS_Media_Lib_Tracking::get_attachment_tracking_count($id);
		}
	} // tracking_column()
} // iS_Media_Lib_Backend{}

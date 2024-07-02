<?php
class iS_Media_Lib_Backend {
	public $config = null;

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		if(count(iS_Media_Lib_Settings::get_enabled_media_tracking_pts()) > 0) {
			add_filter("manage_media_columns", array($this, "add_tracking_column"));
			add_action("manage_media_custom_column", array($this, "tracking_column"), 10, 2);

			add_action("admin_enqueue_scripts", array($this, "enqueue_scripts"));
		}

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
			$count = (int) iS_Media_Lib_Tracking::get_attachment_tracking_count($id);
			$title = $count == 1 ? esc_html__("Media link", $this->config->get("modulName")) : esc_html__("Media links", $this->config->get("modulName"));
			echo '<div class="tracking_detail" data-id="'.$id.'" data-count="'.$count.'">'.$count.' '.$title.'</div>';
		}
	} // tracking_column()

	function enqueue_scripts() {
		$pts  = array();
		$cpts = iS_General_CPT::get_all_cpts();
		foreach ($cpts as $cpt) {
			$pts[$cpt] = iS_General_Settings::get_translated_pt_name($cpt);
		}

		wp_enqueue_style("is_media_lib_backend_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/backend.min.css", array(), $this->config->get("version"));
		wp_enqueue_script("is_media_lib_fusilli_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/lib/fusilli.min.js", array("jquery"), $this->config->get("version"), true);
		wp_enqueue_script("is_media_lib_backend_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/backend.min.js", array("jquery", "is_media_lib_fusilli_js"), $this->config->get("version"), true);
		wp_localize_script(
			"is_media_lib_backend_js",
			"is_media_lib_backend_vars",
			array(
				"ajax_url" => esc_url(get_option("home"))."/wp-json/is_media_lib/tracking/", // avoid wpml lang in url
				"l18n"     => array(
					"post_title" => esc_html__("Post title", $this->config->get("modulName")),
					"post_type" => esc_html__("Post type", $this->config->get("modulName")),
				),
			),
		);
	} // enqueue_scripts()
} // iS_Media_Lib_Backend{}

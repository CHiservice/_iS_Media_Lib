<?php
class iS_Media_Lib_PDF_List {
	public $config = null;

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		if((int) iS_Media_Lib_Settings::is_media_track_overview_enabled() == 1) {
			add_action("admin_enqueue_scripts", array($this, "enqueue_scripts"));
			add_action("admin_menu", array($this, "menu_init"));
		}
	} // __construct()

	function enqueue_scripts() {
		$handles = iS_General_Enqueue_Lib::js("jquery.dataTables");
		
		wp_enqueue_style("is_media_lib_media_list_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/media.list.min.css", array(), $this->config->get("version"));
		wp_enqueue_script("is_media_lib_pdf_list_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/pdf.list.min.js", $handles, $this->config->get("version"), true);
		wp_localize_script(
			"is_media_lib_pdf_list_js",
			"is_media_lib_pdf_list_vars",
			array(
				"search_url"     => esc_url(get_option("home"))."/wp-json/is_media_lib/pdf", // avoid wpml lang in url
				"media_edit_url" => esc_url(get_option("home"))."/wp-admin/post.php?action=edit&post=", // avoid wpml lang in url
				"lang"           => get_locale(),
				"l18n"           => array(
					"info"            => esc_html__("Showing page _PAGE_ of _PAGES_", $this->config->get("modulName")),
					"info_empty"      => esc_html__("No PDF caches available", $this->config->get("modulName")),
					"info_filtered"   => esc_html__("(filtered from _MAX_ total PDFs)", $this->config->get("modulName")),
					"length_menu"     => esc_html__("Show _MENU_ PDFs per page", $this->config->get("modulName")),
					"zero_records"    => esc_html__("Nothing found.", $this->config->get("modulName")),
					"first"           => esc_html__("First", $this->config->get("modulName")),
					"previous"        => esc_html__("Previous", $this->config->get("modulName")),
					"next"            => esc_html__("Next", $this->config->get("modulName")),
					"last"            => esc_html__("Last", $this->config->get("modulName")),
					"search"          => esc_html__("Search", $this->config->get("modulName")),
				),
				"cols"           => array(
					array("id" => "file", "name" => esc_html__("PDF", $this->config->get("modulName")), "visible" => true, "formatter" => "file"),
					array("id" => "file_date", "name" => esc_html__("File date", $this->config->get("modulName")), "visible" => true, "width" => "150px", "formatter" => "date"),
				),
			),
		);
	} // enqueue_scripts()

	function menu_init() {
		add_menu_page(
			esc_html__("PDF content", $this->config->get("modulName")),
			esc_html__("PDF content", $this->config->get("modulName")),
			"manage_options",
			"pdf-list",
			array($this, "pdf_list"),
			"",
			998
		);
	} // menu_init()

	function pdf_list() {
?>
		<div class="is-media-lib-pdf-list">
			<h1><?php echo esc_html__("Media links", $this->config->get("modulName")); ?></h1>
			<?php echo esc_html__("Visable columns", $this->config->get("modulName")); ?>
			<ul class="show-hide"></ul>
			<table id="is_media_lib_tracking_list" class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr></tr>
				</thead>
			</table>
		</div>
<?php
	} // pdf_list()
} // iS_Media_Lib_PDF_List{}

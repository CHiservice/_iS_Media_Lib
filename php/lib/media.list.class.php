<?php
class iS_Media_Lib_Media_List {
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
		wp_enqueue_script("is_media_lib_media_list_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/media.list.min.js", $handles, $this->config->get("version"), true);
		wp_localize_script(
			"is_media_lib_media_list_js",
			"is_media_lib_media_list_vars",
			array(
				"search_url"     => esc_url(get_option("home"))."/wp-json/is_media_lib/tracking", // avoid wpml lang in url
				"media_edit_url" => esc_url(get_option("home"))."/wp-admin/post.php?action=edit&post=",
				"post_edit_url"  => esc_url(get_option("home"))."/wp-admin/post.php?action=edit&post=",
				"lang"           => get_locale(),
				"l18n"           => array(
					"info"            => esc_html__("Showing page _PAGE_ of _PAGES_", $this->config->get("modulName")),
					"info_empty"      => esc_html__("No media links available", $this->config->get("modulName")),
					"info_filtered"   => esc_html__("(filtered from _MAX_ total media links)", $this->config->get("modulName")),
					"length_menu"     => esc_html__("Show _MENU_ media links per page", $this->config->get("modulName")),
					"zero_records"    => esc_html__("Nothing found.", $this->config->get("modulName")),
					"first"           => esc_html__("First", $this->config->get("modulName")),
					"previous"        => esc_html__("Previous", $this->config->get("modulName")),
					"next"            => esc_html__("Next", $this->config->get("modulName")),
					"last"            => esc_html__("Last", $this->config->get("modulName")),
					"search"          => esc_html__("Search", $this->config->get("modulName")),
				),
				"pts"            => $this->_get_pt_names(),
				"cols"           => array(
					array("id" => "mime_type", "name" => esc_html__("Mime type", $this->config->get("modulName")), "hide-headline-name" => true, "visible" => true, "width" => "30px", "formatter" => "mime_type"),
					array("id" => "thumbnail", "name" => esc_html__("Thumbnail", $this->config->get("modulName")), "hide-headline-name" => true, "visible" => true, "width" => "30px", "formatter" => "img"),
					array("id" => "file", "name" => esc_html__("File", $this->config->get("modulName")), "visible" => true, "formatter" => "file"),
					array("id" => "file_date", "name" => esc_html__("File date", $this->config->get("modulName")), "visible" => true, "width" => "150px", "formatter" => "date"),
					array("id" => "post_title", "name" => esc_html__("Post name", $this->config->get("modulName")), "visible" => true, "formatter" => "post_title"),
					array("id" => "post_type", "name" => esc_html__("Post type", $this->config->get("modulName")), "visible" => true, "formatter" => "post_type"),
					array("id" => "post_date", "name" => esc_html__("Post date", $this->config->get("modulName")), "visible" => true, "width" => "150px", "formatter" => "date"),
					array("id" => "usage_type", "name" => esc_html__("Usage", $this->config->get("modulName")), "visible" => true, "width" => "85px", "formatter" => "usage_type"),
					array("id" => "field_type", "name" => esc_html__("Field type", $this->config->get("modulName")), "visible" => false),
					array("id" => "field_id", "name" => esc_html__("Field ID", $this->config->get("modulName")), "visible" => false),
				),
			),
		);
	} // enqueue_scripts()

	function menu_init() {
		add_menu_page(
			esc_html__("Media links", $this->config->get("modulName")),
			esc_html__("Media links", $this->config->get("modulName")),
			"manage_options",
			"media-tracking",
			array($this, "tracking_list"),
			"",
			998
		);
	} // menu_init()

	function tracking_list() {
?>
		<div class="is-media-lib-tracking-list">
			<h1><?php echo esc_html__("Media links", $this->config->get("modulName")); ?></h1>
			<?php echo esc_html__("Visable columns", $this->config->get("modulName")); ?>
			<ul class="show-hide"></ul>
			<div class="special-filter hidden">
				<div>
					<label><?php echo esc_html__("Post type", $this->config->get("modulName")); ?></label>
					<select class="post_type">
						<option value=""><?php echo esc_html__("All", $this->config->get("modulName")); ?></option>
<?php
						foreach($this->_get_pt_names() AS $pt => $name) {
							echo "<option value='".$pt."'>".$name."</option>";
						
						}
?>
					</select>

				</div>
				<div>
					<label><?php echo esc_html__("Mime type", $this->config->get("modulName")); ?></label>
					<select class="mime_type">
						<option value=""><?php echo esc_html__("All", $this->config->get("modulName")); ?></option>
<?php
						foreach(iS_Media_Lib_Tracking::get_mime_types() AS $type) {
							echo "<option value='".$type."'>".$type."</option>";
						}
?>
					</select>
				</div>
			</div>
			<table id="is_media_lib_tracking_list" class="wp-list-table widefat fixed striped table-view-list">
				<thead>
					<tr></tr>
				</thead>
			</table>
		</div>
<?php
	} // tracking_list()

	private function _get_pt_names() {
		$pts           = array();
		$available_pts = iS_General_CPT::get_all_cpts();
		foreach ($available_pts as $cpt) {
			$pts[$cpt] = iS_General_Settings::get_translated_pt_name($cpt);
		}

		return $pts;
	} // _get_pt_names()
} // iS_Media_Lib_Media_List{}

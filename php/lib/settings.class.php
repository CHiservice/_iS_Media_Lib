<?php

class iS_Media_Lib_Settings {
	private $config              = null;
	private $section_pdf_id      = "media_lib_section_pdf";
	private $section_tracking_id = "media_lib_section_tracking";
	private $section_cpt_id      = "media_lib_section_cpt";
	public static $pageId        = "iservice-settings-media_lib";
	public static $groupId       = "iservice-settings-media_lib-group";

	public function __construct() {
		$this->config = iS_Media_Lib_Config::get_instance();

		add_action("admin_enqueue_scripts", array($this, "enqueue_scripts"));

		add_action("admin_menu", array($this, "add_settings_page"));
		add_action("admin_menu", array($this, "add_sections"));

		$checkbox = new Setting_Checkbox("is_media_pdf_content_cache", esc_html__("Store PDF text for search", $this->config->get("modulName")), self::$pageId, $this->section_pdf_id, self::$groupId);
		$checkbox = new Setting_Checkbox("is_media_track_overview", esc_html__("Show list of media links", $this->config->get("modulName")), self::$pageId, $this->section_tracking_id, self::$groupId);

		$cpts = iS_General_CPT::get_all_cpts();
		foreach ($cpts as $cpt) {
			$pt_name  = iS_General_Settings::get_translated_pt_name($cpt);
			$checkbox = new Setting_Checkbox("is_media_track_attachment_".$cpt, esc_html__("Post type", $this->config->get("modulName")).": ".$pt_name, self::$pageId, $this->section_cpt_id, self::$groupId);
		}

		add_action("rest_api_init", function () {
			// "/register/(?P<id>\d+)/(?P<number>[a-zA-Z0-9-]+)"
			register_rest_route("is_media_lib", "init_tracking", array(
				"methods"  => "GET",
				"callback" => array($this, "init_tracking_for_existing"),
			));
			register_rest_route("is_media_lib", "init_pdf_cache", array(
				"methods"  => "GET",
				"callback" => array($this, "init_pdf_cache_for_existing"),
			));
		});

	} // __construct

	function enqueue_scripts() {
		$pts  = array();
		$cpts = iS_General_CPT::get_all_cpts();
		foreach ($cpts as $cpt) {
			$pts[$cpt] = iS_General_Settings::get_translated_pt_name($cpt);
		}

		wp_enqueue_style("is_media_lib_settings_css", STYLESHEETURL."/".$this->config->get("modulName")."/css/settings.min.css", array(), $this->config->get("version"));
		wp_enqueue_script("is_media_lib_settings_js", STYLESHEETURL."/".$this->config->get("modulName")."/js/settings.min.js", array("jquery"), $this->config->get("version"), true);
		wp_localize_script(
			"is_media_lib_settings_js",
			"is_media_lib_settings_vars",
			array(
				"ajax_url" => esc_url(get_option("home"))."/wp-json/is_media_lib/", // avoid wpml lang in url
				"pts"      => $pts,
			),
		);
	} // enqueue_scripts()

	public function add_settings_page() {
		add_options_page(
			esc_html__("iService Media links", $this->config->get("modulName")),
			iS_General_Settings::add_sub_settings_page_title(esc_html__("Media links", $this->config->get("modulName")), "media-lib"),
			"manage_options",
			self::$pageId,
			array($this, "render_settings_page")
		);
	} // add_settings_page()

	public function add_sections() {
		add_settings_section($this->section_pdf_id, esc_html__("Activate PDF content cache", $this->config->get("modulName")), array($this, "section_pdf_callback"), self::$pageId);
		add_settings_section($this->section_tracking_id, esc_html__("Media links page", $this->config->get("modulName")), array($this, "section_callback"), self::$pageId);
		add_settings_section($this->section_cpt_id, esc_html__("Track all media links in posts", $this->config->get("modulName")), array($this, "section_media_tracking_callback"), self::$pageId);
	} // add_sections()

	public function section_pdf_callback() {
		echo '<div class="media-lib-pdf-cache-status hidden">';
			echo esc_html__("Status", $this->config->get("modulName")).': <div class="count">0</div>'.esc_html__("PDFs in Cache.", $this->config->get("modulName"));
		echo '</div>';
	} // section_pdf_callback()

	public function section_media_tracking_callback() {
		echo '<div class="media-lib-media-tracking-status hidden">';
			echo esc_html__("Status", $this->config->get("modulName")).': <div class="counts"></div>';
		echo '</div>';
	} // section_media_tracking_callback()

	public function section_callback() {
		echo "";
	} // section_callback()

	public function render_settings_page() {
		?>
		<div class="iService-backend-settings">
			<h1><img src="<?= STYLESHEETURL."/iS_General/css/img/iService_Logo_round_black.png" ?>"><?php echo esc_html__("iService Media links", $this->config->get("modulName")); ?></h1>
			<div id="iservice-media_lib">
				<form method="post" action="options.php">
					<?php
					settings_fields(self::$groupId);
					do_settings_sections(self::$pageId);
					submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	} // render_settings_page()

	public static function get_enabled_media_tracking_pts() {
		$enabled_cpt = array();
		$all_cpts    = iS_General_CPT::get_all_cpts();
		foreach ($all_cpts as $cpt) {
			$val = get_option("is_media_track_attachment_".$cpt);
			if($val) {
				array_push($enabled_cpt, $cpt);
			}
		}

		return $enabled_cpt;
	} // get_enabled_media_tracking_pts()

	public static function is_pdf_content_cache_enabled() {
		return (int) get_option("is_media_pdf_content_cache");
	} // is_media_tracking_enabled()

	public static function is_media_tracking_pt_enabled($cpt) {
		return (int) get_option("is_media_track_attachment_".$cpt);
	} // is_media_tracking_pt_enabled()

	public static function is_media_track_overview_enabled() {
		return (int) get_option("is_media_track_overview");
	} // is_media_track_overview_enabled()

	public function init_tracking_for_existing($data) {
		global $wpdb;

		$lib        = new iS_Media_Lib_Tracking();
		$active_pts = iS_Media_Lib_Settings::get_enabled_media_tracking_pts();
		$cpts       = iS_General_CPT::get_all_cpts();
		$status     = get_option("is_media_init_track_attachment"); // last done id
		$statistic  = get_option("is_media_init_track_attachment_statistic"); // how much done

		if(!is_array($status)) {
			$status = array();
		}

		if(!is_array($statistic)) {
			$statistic = array();
		}

		foreach ($cpts as $cpt) {
			if(!array_key_exists($cpt, $statistic)) {
				$statistic[$cpt] = null;
			}

			if(!in_array($cpt, $active_pts)) {
				// init for posttype done? -> clear all
				if(array_key_exists($cpt, $statistic) && is_array($statistic[$cpt]) && array_key_exists(0, $statistic[$cpt]) && $statistic[$cpt][0] > 0) {
					$lib->clear_post_type($cpt);
					$status[$cpt] = 0;
					update_option("is_media_init_track_attachment", $status);
					$statistic[$cpt][0] = 0;
					update_option("is_media_init_track_attachment_statistic", $statistic);
				}
				$statistic[$cpt] = null;
				update_option("is_media_init_track_attachment_statistic", $statistic);
			} else {
				if(is_array($statistic[$cpt]) == false || count($statistic[$cpt]) < 2) {
					$statistic[$cpt] = array(0, 0);
				}

				// could be more than before
				$sql = "SELECT
						COUNT(1)
					FROM `".$wpdb->prefix."posts` AS `p` 
					WHERE `p`.`post_type` = '".$cpt."'";
				$statistic[$cpt][1] = (int) $GLOBALS["wpdb"]->get_var($sql);

				if($statistic[$cpt][0] < $statistic[$cpt][1]) {
					// init next package
					$sql = "SELECT
							*
						FROM `".$wpdb->prefix."posts` AS `p` 
						WHERE ID > ".(int) $status[$cpt]."
						AND `p`.`post_type` = '".$cpt."'
						LIMIT 10";
					$data = $GLOBALS["wpdb"]->get_results($sql);

					if(is_array($data) && count($data) > 0) {
						foreach ($data as $post) {
							$lib->save_data($post->ID, $post);
							$status[$cpt] = $post->ID;
							update_option("is_media_init_track_attachment", $status);
							$statistic[$cpt][0] = min($statistic[$cpt][0]+1, $statistic[$cpt][1]);
							update_option("is_media_init_track_attachment_statistic", $statistic);
						}
					}
				}
			}
		}

		return $statistic;
	} // init_tracking_for_existing()

	public function init_pdf_cache_for_existing() {
		global $wpdb;

		$lib       = new iS_Media_Lib_PDF_Content_Cache();
		$active    = iS_Media_Lib_Settings::is_pdf_content_cache_enabled();
		$status    = get_option("is_media_init_pdf_cache");
		$statistic = get_option("is_media_init_pdf_cache_statistic");

		if(!is_array($statistic)) {
			$statistic = array(0, 0);
		}

		if($active != 1 && $statistic[0] > 0) {
			$statistic = array(0, 0);
			$status = "done";
			update_option("is_media_init_pdf_cache", $status);
			update_option("is_media_init_pdf_cache_statistic", $statistic);
		} else {
			// could be more than before
			$sql = "SELECT
					COUNT(1)
				FROM `".$wpdb->prefix."posts` AS `p` 
				WHERE `p`.`post_type` = 'attachment'
				AND `p`.`post_mime_type` = 'application/pdf'";
			$statistic[1] = (int) $GLOBALS["wpdb"]->get_var($sql);

			// init next package
			$sql = "SELECT
					*
				FROM `".$wpdb->prefix."posts` AS `p` 
				WHERE ID > ".max(0, (int) $status)."
				AND `p`.`post_type` = 'attachment'
				AND `p`.`post_mime_type` = 'application/pdf'
				LIMIT 10";
			$data = $GLOBALS["wpdb"]->get_results($sql);
			if(is_array($data) && count($data) > 0) {
				foreach ($data as $post) {
					$lib->insert_pdf_chache($post->ID);
					$status = $post->ID;
					update_option("is_media_init_pdf_cache", $status);
					$statistic[0]++;
					$statistic[0] = min($statistic[0]+1, $statistic[1]);
					update_option("is_media_init_pdf_cache_statistic", $statistic);
				}
			} else {
				$status = "done";
				update_option("is_media_init_pdf_cache", $status);
			}
		}

		return array("status" => $status, "statistic" => $statistic);
	} // init_pdf_cache_for_existing()
} // iS_Media_Lib_Settings{}
